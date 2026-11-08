<?php
/**
 * Authentification, session PHP et lecture du fichier users.txt.
 */

declare(strict_types=1);

/**
 * Démarre la session avec des paramètres raisonnablement sûrs.
 */
function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_IDLE_SECONDS);

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Normalise un rôle provenant de users.txt.
 */
function normalize_role(string $role): string
{
    $role = strtolower(trim($role));
    return match ($role) {
        'po', 'product_owner', 'product-owner' => 'PO',
        'sm', 'scrum_master', 'scrum-master' => 'SCRUM_MASTER',
        'dev', 'developer', 'développeur', 'developpeur' => 'DEVELOPER',
        'formateur', 'teacher' => 'FORMATEUR',
        default => throw new RuntimeException('Rôle inconnu dans users.txt : ' . $role),
    };
}

/**
 * Lit et valide le fichier users.txt.
 * Retourne un tableau indexé par login, sans jamais exposer les dates
 * de naissance en dehors de ce module.
 */
function load_users(): array
{
    if (!is_file(USERS_FILE)) {
        throw new RuntimeException('Le fichier datas/users.txt est introuvable.');
    }
    $lines = file(USERS_FILE, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Impossible de lire datas/users.txt.');
    }

    $users = [];
    foreach ($lines as $lineNumber => $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode(';', $line);
        if (count($parts) !== 4 && count($parts) !== 5) {
            throw new RuntimeException('Ligne invalide dans users.txt (ligne ' . ($lineNumber + 1) . ').');
        }
        $parts = array_map('trim', $parts);
        [$login, $dob, $groupRaw, $roleRaw] = array_slice($parts, 0, 4);
        $passwordHash = $parts[4] ?? '';

        if (!preg_match('/^[a-z]{1,30}$/', $login)) {
            throw new RuntimeException('Login invalide dans users.txt (ligne ' . ($lineNumber + 1) . ').');
        }
        if (!preg_match('/^\d{8}$/', $dob)) {
            throw new RuntimeException('Date de naissance invalide pour ' . $login . '.');
        }
        if (!preg_match('/^[0-3]$/', $groupRaw)) {
            throw new RuntimeException('Groupe invalide pour ' . $login . '.');
        }
        $group = (int) $groupRaw;
        $role = normalize_role($roleRaw);

        // Le formateur utilise ses 8 chiffres comme mot de passe : il peut
        // choisir une suite qui n'est pas une date valide. Les comptes
        // étudiants conservent la validation stricte de la date.
        if ($role !== 'FORMATEUR'
            && !checkdate((int) substr($dob, 2, 2), (int) substr($dob, 0, 2), (int) substr($dob, 4, 4))) {
            throw new RuntimeException('Date de naissance invalide pour ' . $login . '.');
        }

        if ($group === 0 && $role !== 'FORMATEUR') {
            throw new RuntimeException('Le groupe 0 est réservé au formateur (' . $login . ').');
        }
        if ($group > 0 && $role === 'FORMATEUR') {
            throw new RuntimeException('Un formateur doit appartenir au groupe 0 (' . $login . ').');
        }
        if (isset($users[$login])) {
            throw new RuntimeException('Login en double dans users.txt : ' . $login);
        }

        $users[$login] = [
            'login' => $login,
            'dob' => $dob, // valeur initiale de réinitialisation, jamais transmise
            'team' => $group,
            'initial_role' => $role,
            'password_hash' => $passwordHash,
        ];
    }

    if ($users === []) {
        throw new RuntimeException('Aucun utilisateur valide dans users.txt.');
    }
    return $users;
}

/**
 * Valide un mot de passe applicatif : exactement 8 chiffres.
 */
function normalize_password_value(mixed $value): string
{
    if (!is_string($value)) {
        throw new AppError('Le mot de passe doit contenir exactement 8 chiffres.');
    }
    $password = preg_replace('/\s+/', '', $value) ?? '';
    if (!preg_match('/^\d{8}$/', $password)) {
        throw new AppError('Le mot de passe doit contenir exactement 8 chiffres.');
    }
    return $password;
}

/**
 * Produit un hash sécurisé pour un mot de passe à 8 chiffres.
 */
function password_hash_value(string $password): string
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (!is_string($hash) || $hash === '') {
        throw new RuntimeException('Impossible de produire le hash du mot de passe.');
    }
    return $hash;
}

/**
 * Vérifie un mot de passe contre le hash du compte. Les anciens fichiers
 * à quatre colonnes restent acceptés jusqu'à leur migration automatique.
 */
function verify_user_password(array $user, string $password): bool
{
    $hash = (string) ($user['password_hash'] ?? '');
    if ($hash !== '') {
        return password_verify($password, $hash);
    }
    return hash_equals((string) ($user['dob'] ?? ''), $password);
}

/**
 * Lit les lignes brutes de users.txt.
 */
function users_read_lines(): array
{
    $lines = file(USERS_FILE, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        throw new RuntimeException('Impossible de lire datas/users.txt.');
    }
    return $lines;
}

/**
 * Écrit users.txt de façon atomique. L'appelant doit détenir le verrou global.
 */
function users_write_lines(array $lines): void
{
    $content = implode("\n", $lines) . "\n";
    $tmp = tempnam(DATA_DIR, '.users_');
    if ($tmp === false) {
        throw new RuntimeException('Impossible de créer un fichier temporaire.');
    }
    try {
        $written = file_put_contents($tmp, $content);
        if ($written === false || $written !== strlen($content)) {
            throw new RuntimeException('Écriture incomplète de users.txt.');
        }
        @chmod($tmp, 0640);
        if (!rename($tmp, USERS_FILE)) {
            throw new RuntimeException('Impossible de remplacer users.txt.');
        }
    } finally {
        if (is_file($tmp)) {
            @unlink($tmp);
        }
    }
}

/**
 * Ajoute le hash manquant aux anciennes lignes de users.txt.
 * La date de naissance initiale reste conservée pour la réinitialisation.
 */
function ensure_users_password_hashes(): void
{
    if (!is_file(USERS_FILE)) {
        return;
    }

    $lines = users_read_lines();
    $needsMigration = false;
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        $parts = explode(';', $line);
        if (count($parts) === 4 || (count($parts) === 5 && trim($parts[4]) === '')) {
            $needsMigration = true;
            break;
        }
    }
    if (!$needsMigration) {
        return;
    }

    with_app_lock(static function (): void {
        $lines = users_read_lines();
        $changed = false;
        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            $parts = array_map('trim', explode(';', $line));
            if (count($parts) === 4) {
                $parts[] = password_hash_value($parts[1]);
            } elseif (count($parts) === 5 && $parts[4] === '') {
                $parts[4] = password_hash_value($parts[1]);
            } else {
                continue;
            }
            $lines[$index] = implode(';', $parts);
            $changed = true;
        }
        if ($changed) {
            users_write_lines($lines);
        }
    });
}

/**
 * Enregistre un nouveau hash pour un compte. L'appelant doit détenir le
 * verrou global afin d'éviter les écritures concurrentes.
 */
function users_set_password_hash(string $login, string $hash): void
{
    $lines = users_read_lines();
    $updated = false;
    foreach ($lines as $index => $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        $parts = explode(';', $line);
        if (count($parts) !== 4 && count($parts) !== 5) {
            throw new RuntimeException('Ligne invalide dans users.txt.');
        }
        $parts = array_map('trim', $parts);
        if (strtolower($parts[0]) !== $login) {
            continue;
        }
        $parts[4] = $hash;
        $lines[$index] = implode(';', $parts);
        $updated = true;
        break;
    }

    if (!$updated) {
        throw new RuntimeException('Compte introuvable dans users.txt.');
    }
    users_write_lines($lines);
}

/**
 * Change le mot de passe du compte connecté après vérification de l'ancien.
 */
function change_own_password(array $user, mixed $currentPassword, mixed $newPassword, mixed $confirmation): array
{
    $login = (string) ($user['login'] ?? '');
    if ($login === '') {
        throw new AppError('Session absente ou expirée. Reconnectez-vous.', 401);
    }

    $current = normalize_password_value($currentPassword);
    $new = normalize_password_value($newPassword);
    $confirm = normalize_password_value($confirmation);
    if (!hash_equals($new, $confirm)) {
        throw new AppError('La confirmation ne correspond pas au nouveau mot de passe.');
    }

    $users = load_users();
    $known = $users[$login] ?? null;
    if ($known === null) {
        throw new AppError('Compte introuvable.', 404);
    }
    if (!verify_user_password($known, $current)) {
        throw new AppError('Le mot de passe actuel est incorrect.', 403);
    }

    users_set_password_hash($login, password_hash_value($new));
    history_add(
        (int) ($user['team'] ?? 0),
        $login,
        'password_change',
        'Mot de passe personnel modifié.'
    );
    return ['message' => 'Mot de passe modifié. Utilisez ces 8 chiffres à la prochaine connexion.'];
}

/**
 * Ancien point d'entrée du changement de mot de passe formateur, conservé
 * pour compatibilité. Le nouveau parcours utilise change_own_password().
 */
function teacher_update_password(array $user, mixed $password): array
{
    if (empty($user['is_teacher'])
        || (int) ($user['team'] ?? -1) !== 0
        || (string) ($user['initial_role'] ?? '') !== 'FORMATEUR') {
        throw new AppError('Action réservée au formateur.', 403);
    }

    $new = normalize_password_value($password);
    $login = (string) $user['login'];
    $users = load_users();
    if (!isset($users[$login])
        || (int) $users[$login]['team'] !== 0
        || $users[$login]['initial_role'] !== 'FORMATEUR') {
        throw new AppError('Compte formateur introuvable.', 403);
    }

    users_set_password_hash($login, password_hash_value($new));
    history_add(0, $login, 'teacher_password', 'Mot de passe du formateur modifié.');
    return ['message' => 'Mot de passe modifié. Utilisez ces 8 chiffres à la prochaine connexion.'];
}

/**
 * Réinitialise le mot de passe d'un étudiant à sa date de naissance initiale.
 * La date n'est ni affichée ni renvoyée au navigateur.
 */
function teacher_reset_password(array $user, mixed $loginRaw): array
{
    if (empty($user['is_teacher'])
        || (int) ($user['team'] ?? -1) !== 0
        || (string) ($user['initial_role'] ?? '') !== 'FORMATEUR') {
        throw new AppError('Action réservée au formateur.', 403);
    }

    $login = strtolower(trim((string) $loginRaw));
    if (!preg_match('/^[a-z]{1,30}$/', $login)) {
        throw new AppError('Identifiant invalide.');
    }

    $users = load_users();
    $target = $users[$login] ?? null;
    if ($target === null
        || (int) $target['team'] === 0
        || $target['initial_role'] === 'FORMATEUR') {
        throw new AppError('Compte étudiant introuvable.', 404);
    }

    $initialPassword = (string) $target['dob'];
    if (!preg_match('/^\d{8}$/', $initialPassword)) {
        throw new RuntimeException('Mot de passe initial invalide pour ' . $login . '.');
    }

    users_set_password_hash($login, password_hash_value($initialPassword));
    try {
        password_request_mark_processed($login);
    } catch (Throwable $exception) {
        error_log('WorkFlow IT password request : ' . $exception->getMessage());
    }

    history_add(
        (int) $target['team'],
        (string) $user['login'],
        'password_reset',
        'Mot de passe réinitialisé pour ' . $login . '.'
    );
    return ['message' => 'Mot de passe de @' . $login . ' réinitialisé.'];
}

/**
 * Structure par défaut du fichier des demandes de réinitialisation.
 */
function password_requests_default(): array
{
    return ['revision' => 0, 'pending' => [], 'rate' => []];
}

/**
 * Enregistre une demande d'oubli pour un compte étudiant.
 * Retourne true si une demande a été créée ou renouvelée.
 */
function request_password_reset(string $loginRaw): bool
{
    $login = strtolower(trim($loginRaw));
    if (!preg_match('/^[a-z]{1,30}$/', $login)) {
        return false;
    }

    $users = load_users();
    $target = $users[$login] ?? null;
    if ($target === null
        || (int) $target['team'] === 0
        || $target['initial_role'] === 'FORMATEUR') {
        return false;
    }

    $now = time();
    return with_app_lock(static function () use ($login, $now, $target): bool {
        $created = with_locked_json(PASSWORD_REQUESTS_FILE, static function (array &$data) use ($login, $now): bool {
            $data['revision'] = (int) ($data['revision'] ?? 0);
            $data['pending'] = is_array($data['pending'] ?? null) ? $data['pending'] : [];
            $data['rate'] = is_array($data['rate'] ?? null) ? $data['rate'] : [];

            $last = (int) ($data['rate'][$login] ?? 0);
            if ($last > 0 && $now - $last < PASSWORD_REQUEST_COOLDOWN_SECONDS) {
                return false;
            }

            $data['revision']++;
            $data['rate'][$login] = $now;
            $data['pending'][$login] = [
                'login' => $login,
                'requested_at' => iso_now(),
                'requested_ts' => $now,
            ];
            return true;
        }, password_requests_default());

        if ($created) {
            history_add(
                (int) $target['team'],
                $login,
                'password_request',
                'Demande de réinitialisation du mot de passe.'
            );
        }
        return $created;
    });
}

/**
 * Retourne les demandes en attente, sans aucune donnée personnelle sensible.
 */
function password_requests_state(): array
{
    $data = json_read(PASSWORD_REQUESTS_FILE, password_requests_default());
    $pending = is_array($data['pending'] ?? null) ? $data['pending'] : [];
    $requests = [];
    foreach ($pending as $login => $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $requests[] = [
            'login' => (string) ($entry['login'] ?? $login),
            'requested_at' => (string) ($entry['requested_at'] ?? ''),
            'requested_ts' => (int) ($entry['requested_ts'] ?? 0),
        ];
    }
    usort($requests, static function (array $a, array $b): int {
        return $b['requested_ts'] <=> $a['requested_ts'];
    });
    return [
        'revision' => (int) ($data['revision'] ?? 0),
        'requests' => $requests,
    ];
}

/**
 * Marque comme traitée la demande en attente d'un compte.
 */
function password_request_mark_processed(string $login): void
{
    if (!is_file(PASSWORD_REQUESTS_FILE)) {
        return;
    }
    with_locked_json(PASSWORD_REQUESTS_FILE, static function (array &$data) use ($login): void {
        if (!isset($data['pending'][$login])) {
            return;
        }
        unset($data['pending'][$login]);
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
    }, password_requests_default());
}

/**
 * Retourne l'état de limitation d'un login.
 */
function login_throttle_status(string $login): array
{
    $data = json_read(LOGIN_ATTEMPTS_FILE, ['users' => []]);
    $entry = $data['users'][$login] ?? ['failures' => 0, 'locked_until' => 0];
    return [
        'failures' => (int) ($entry['failures'] ?? 0),
        'locked_until' => (int) ($entry['locked_until'] ?? 0),
    ];
}

/**
 * Applique un échec de connexion et verrouille après le seuil.
 */
function login_register_failure(string $login): void
{
    $now = time();
    with_locked_json(LOGIN_ATTEMPTS_FILE, function (array &$data) use ($login, $now): void {
        $data['users'] ??= [];
        $entry = $data['users'][$login] ?? ['failures' => 0, 'locked_until' => 0];

        // Un verrou expiré remet le compteur à zéro.
        if ((int) $entry['locked_until'] > 0 && (int) $entry['locked_until'] <= $now) {
            $entry = ['failures' => 0, 'locked_until' => 0];
        }

        $entry['failures'] = (int) $entry['failures'] + 1;
        $entry['last_failure'] = $now;
        if ($entry['failures'] >= MAX_LOGIN_ATTEMPTS) {
            $entry['locked_until'] = $now + LOGIN_LOCK_SECONDS;
        }
        $entry['failures'] = (int) $entry['failures'];
        $data['users'][$login] = $entry;
    }, ['users' => []]);
}

/**
 * Efface les échecs après une connexion réussie.
 */
function login_clear_failures(string $login): void
{
    with_locked_json(LOGIN_ATTEMPTS_FILE, function (array &$data) use ($login): void {
        $data['users'] ??= [];
        unset($data['users'][$login]);
    }, ['users' => []]);
}

/**
 * Vérifie les identifiants et prépare une connexion en attente de confirmation.
 * Aucun accès authentifié n'est accordé avant login_confirm().
 */
function login_begin(string $login, string $dob): array
{
    // Toute nouvelle tentative remplace une éventuelle confirmation en attente.
    unset($_SESSION['pending_login']);

    $login = strtolower(trim($login));
    $dob = preg_replace('/\s+/', '', $dob) ?? '';

    if (!preg_match('/^[a-z]{1,30}$/', $login) || !preg_match('/^\d{8}$/', $dob)) {
        throw new AppError('Identifiant ou mot de passe incorrect.');
    }

    $throttle = login_throttle_status($login);
    if ($throttle['locked_until'] > time()) {
        $wait = max(1, $throttle['locked_until'] - time());
        throw new AppError(sprintf('Trop de tentatives. Réessayez dans %d seconde(s).', $wait), 429);
    }

    $users = load_users();
    $known = $users[$login] ?? null;

    // Comparaison avec le hash du compte. Pour un login inconnu, un hash
    // factice est vérifié afin de limiter les différences de temps de réponse.
    if ($known === null) {
        password_verify($dob, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
        $valid = false;
    } else {
        $valid = verify_user_password($known, $dob);
    }

    if (!$valid) {
        login_register_failure($login);
        throw new AppError('Identifiant ou mot de passe incorrect.');
    }

    if ($known['team'] === 0 && $known['initial_role'] !== 'FORMATEUR') {
        throw new RuntimeException('Utilisateur formateur invalide.');
    }

    login_clear_failures($login);
    $token = bin2hex(random_bytes(32));
    $now = time();
    $_SESSION['pending_login'] = [
        'token' => $token,
        'login' => $known['login'],
        'team' => $known['team'],
        'issued_at' => $now,
        'expires_at' => $now + LOGIN_CONFIRM_TTL_SECONDS,
    ];

    return [
        'pending' => $token,
        'login' => $known['login'],
        'expires_in' => LOGIN_CONFIRM_TTL_SECONDS,
    ];
}

/**
 * Retourne la connexion en attente si elle est encore valide.
 */
function login_pending(): ?array
{
    $pending = $_SESSION['pending_login'] ?? null;
    if (!is_array($pending) || empty($pending['login']) || empty($pending['token'])) {
        return null;
    }
    if ((int) ($pending['expires_at'] ?? 0) <= time()) {
        unset($_SESSION['pending_login']);
        return null;
    }
    return $pending;
}

/**
 * Finalise une connexion préalablement vérifiée.
 */
function login_confirm(string $token): array
{
    $pending = login_pending();
    if ($pending === null
        || !is_string($token)
        || !hash_equals((string) $pending['token'], $token)) {
        unset($_SESSION['pending_login']);
        throw new AppError('Confirmation de connexion invalide ou expirée. Recommencez la connexion.', 403);
    }

    $users = load_users();
    $known = $users[(string) $pending['login']] ?? null;
    if ($known === null
        || (int) $known['team'] !== (int) $pending['team']
        || ($known['team'] === 0 && $known['initial_role'] !== 'FORMATEUR')) {
        unset($_SESSION['pending_login']);
        throw new AppError('Connexion impossible. Les informations du compte ont changé.', 403);
    }

    unset($_SESSION['pending_login']);
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $_SESSION['user'] = [
        'login' => $known['login'],
        'team' => $known['team'],
        'is_teacher' => $known['team'] === 0,
        'initial_role' => $known['initial_role'],
    ];
    $_SESSION['last_seen'] = time();

    history_add(
        $known['team'],
        $known['login'],
        'login',
        $known['team'] === 0 ? 'Connexion du formateur.' : 'Connexion de ' . $known['login'] . ' (équipe ' . $known['team'] . ').'
    );

    return $_SESSION['user'];
}

/**
 * Annule une connexion en attente (fermeture de la modale de confirmation).
 */
function login_cancel(): void
{
    unset($_SESSION['pending_login']);
}

/**
 * Authentifie immédiatement un utilisateur (compatibilité interne).
 * Le parcours utilisateur passe par login_begin() puis login_confirm().
 */
function login_user(string $login, string $dob): array
{
    $pending = login_begin($login, $dob);
    return login_confirm((string) $pending['pending']);
}

/**
 * Déconnecte proprement l'utilisateur.
 */
function logout_user(): void
{
    $user = current_user();
    if ($user !== null) {
        history_add(
            (int) $user['team'],
            (string) $user['login'],
            'logout',
            'Déconnexion.'
        );
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
    }
    session_destroy();
}

/**
 * Retourne l'utilisateur authentifié, ou null.
 * Applique une expiration d'inactivité simple.
 */
function current_user(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }
    $user = $_SESSION['user'] ?? null;
    if (!is_array($user) || empty($user['login']) || !isset($user['team'])) {
        return null;
    }

    $lastSeen = (int) ($_SESSION['last_seen'] ?? 0);
    if ($lastSeen > 0 && time() - $lastSeen > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_destroy();
        return null;
    }
    $_SESSION['last_seen'] = time();

    return [
        'login' => (string) $user['login'],
        'team' => (int) $user['team'],
        'is_teacher' => (bool) ($user['is_teacher'] ?? false),
        'initial_role' => (string) ($user['initial_role'] ?? ''),
    ];
}

/**
 * Exige une session authentifiée pour une page HTML.
 */
function require_login_page(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('workflow.php');
    }
    return $user;
}

/**
 * Place un message flash en session.
 */
function set_flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Récupère et supprime le message flash.
 */
function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}
