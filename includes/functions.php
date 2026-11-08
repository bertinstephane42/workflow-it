<?php
/**
 * Fonctions générales : JSON, verrous, validation, historique, sécurité.
 */

declare(strict_types=1);

/**
 * Erreur métier ou de validation destinée à être renvoyée au navigateur.
 */
class AppError extends RuntimeException
{
    public int $status;

    public function __construct(string $message, int $status = 400)
    {
        parent::__construct($message);
        $this->status = $status;
    }
}

/**
 * Envoie une réponse JSON puis termine le script.
 */
function json_respond(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Réponse d'erreur JSON normalisée.
 */
function json_error(string $message, int $status = 400): void
{
    json_respond(['ok' => false, 'error' => $message], $status);
}

/**
 * Horodatage courant au format ISO (fuseau Europe/Paris).
 */
function iso_now(): string
{
    return date('c');
}

/**
 * Horodatage ISO à partir d'un timestamp Unix.
 */
function iso_at(int $timestamp): string
{
    return date('c', $timestamp);
}

/**
 * Heure courante en millisecondes (utilisée pour l'horloge du navigateur).
 */
function now_ms(): int
{
    return (int) round(microtime(true) * 1000);
}

/**
 * Prépare le dossier de données si nécessaire.
 */
function ensure_data_dir(): void
{
    if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0750, true) && !is_dir(DATA_DIR)) {
        throw new RuntimeException('Le dossier datas est introuvable et ne peut pas être créé.');
    }
}

/**
 * Lit un fichier JSON. Retourne $default s'il n'existe pas.
 * Lève une exception si le fichier est illisible ou corrompu.
 */
function json_read(string $path, array $default = []): array
{
    if (!is_file($path)) {
        return $default;
    }
    if (filesize($path) > MAX_JSON_BYTES) {
        throw new RuntimeException('Fichier de données trop volumineux : ' . basename($path));
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Impossible de lire ' . basename($path));
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Fichier JSON invalide : ' . basename($path));
    }
    return $data;
}

/**
 * Écriture atomique : fichier temporaire dans le même dossier puis rename().
 * Le verrou doit être détenu par l'appelant (voir with_locked_json).
 */
function json_write_atomic(string $path, array $data): void
{
    $dir = dirname($path);
    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($json === false) {
        throw new RuntimeException('Impossible d\'encoder les données de ' . basename($path));
    }

    $tmp = tempnam($dir, '.tmp_');
    if ($tmp === false) {
        throw new RuntimeException('Impossible de créer un fichier temporaire.');
    }

    try {
        $written = file_put_contents($tmp, $json);
        if ($written === false || $written !== strlen($json)) {
            throw new RuntimeException('Écriture incomplète de ' . basename($path));
        }
        @chmod($tmp, 0640);
        if (!rename($tmp, $path)) {
            throw new RuntimeException('Impossible de remplacer ' . basename($path));
        }
    } finally {
        if (is_file($tmp)) {
            @unlink($tmp);
        }
    }
}

/**
 * Exécute une lecture-modification-écriture sous verrou exclusif.
 *
 * Le verrou porte sur un fichier .lock stable : remplacer le fichier JSON
 * pendant l'opération ne casse donc pas le mécanisme de verrouillage.
 * Le callback reçoit les données par référence et peut retourner une valeur.
 */
function with_locked_json(string $path, callable $callback, array $default = []): mixed
{
    ensure_data_dir();
    $lockPath = $path . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false) {
        throw new RuntimeException('Impossible d\'ouvrir le verrou de ' . basename($path));
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        throw new RuntimeException('Verrou indisponible pour ' . basename($path));
    }

    try {
        $data = json_read($path, $default);
        $result = $callback($data);
        json_write_atomic($path, $data);
        return $result;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

/**
 * Verrou global utilisé pour les opérations touchant plusieurs fichiers.
 * Il sérialise les écritures de l'application sans bloquer les lectures.
 */
function with_app_lock(callable $callback): mixed
{
    ensure_data_dir();
    $lock = fopen(APP_LOCK_FILE, 'c');
    if ($lock === false) {
        throw new RuntimeException('Impossible d\'ouvrir le verrou global.');
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        throw new RuntimeException('Verrou global indisponible.');
    }
    try {
        return $callback();
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

/**
 * Nettoie et valide une chaîne UTF-8.
 */
function clean_text(mixed $value): string
{
    if (!is_string($value)) {
        throw new AppError('Valeur texte attendue.');
    }
    if (!mb_check_encoding($value, 'UTF-8')) {
        throw new AppError('Le texte contient des caractères invalides.');
    }
    return trim(str_replace("\0", '', $value));
}

/**
 * Chaîne obligatoire avec longueur contrôlée.
 */
function require_text(mixed $value, int $min, int $max, string $label): string
{
    $text = clean_text($value);
    $length = mb_strlen($text);
    if ($length < $min || $length > $max) {
        throw new AppError(sprintf('%s : longueur attendue entre %d et %d caractères.', $label, $min, $max));
    }
    return $text;
}

/**
 * Entier obligatoire dans un intervalle.
 */
function require_int(mixed $value, int $min, int $max, string $label): int
{
    if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
        $value = (int) $value;
    }
    if (!is_int($value) || $value < $min || $value > $max) {
        throw new AppError(sprintf('%s : valeur entière attendue entre %d et %d.', $label, $min, $max));
    }
    return $value;
}

/**
 * Nombre décimal obligatoire (heures, par exemple).
 */
function require_decimal(mixed $value, float $min, float $max, string $label): float
{
    if (!is_numeric($value)) {
        throw new AppError($label . ' : nombre attendu.');
    }
    $number = (float) $value;
    if (!is_finite($number) || $number < $min || $number > $max) {
        throw new AppError(sprintf('%s : valeur attendue entre %s et %s.', $label, $min, $max));
    }
    return round($number, 2);
}

/**
 * Valeur appartenant à une liste fermée.
 */
function require_choice(mixed $value, array $choices, string $label): string
{
    if (!is_string($value) || !in_array($value, $choices, true)) {
        throw new AppError($label . ' : valeur non autorisée.');
    }
    return $value;
}

/**
 * Ajoute une entrée à l'historique global.
 */
function history_add(int $team, string $author, string $type, string $message, array $details = []): void
{
    if (mb_strlen($message) > 1000) {
        $message = mb_substr($message, 0, 997) . '...';
    }
    $entry = [
        'at' => iso_now(),
        'ts' => time(),
        'team' => $team,
        'author' => $author,
        'type' => $type,
        'message' => $message,
        'details' => $details,
    ];

    with_locked_json(HISTORY_FILE, function (array &$data) use ($entry): void {
        if (!isset($data['entries']) || !is_array($data['entries'])) {
            $data['entries'] = [];
        }
        $data['entries'][] = $entry;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();
    }, ['entries' => [], 'revision' => 0, 'updated_at' => iso_now()]);
}

/**
 * Applique la limitation globale de requêtes dynamiques.
 * Retourne true si la requête est autorisée.
 */
function api_rate_limit_hit(): bool
{
    $currentSecond = (int) floor(microtime(true));
    return (bool) with_locked_json(RATE_LIMIT_FILE, function (array &$data) use ($currentSecond): bool {
        if ((int) ($data['second'] ?? 0) !== $currentSecond) {
            $data = ['second' => $currentSecond, 'count' => 0];
        }
        $data['count'] = (int) ($data['count'] ?? 0) + 1;
        return $data['count'] <= API_RATE_LIMIT_PER_SECOND;
    }, ['second' => 0, 'count' => 0]);
}

/**
 * Jeton CSRF de la session courante.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Vérifie un jeton CSRF.
 */
function csrf_valid(mixed $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && is_string($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}

/**
 * En-têtes de sécurité communs aux pages HTML.
 */
function send_html_security_headers(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header(
        "Content-Security-Policy: default-src 'self'; " .
        "script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; " .
        "font-src 'self'; connect-src 'self'; object-src 'none'; form-action 'self'; " .
        "frame-ancestors 'none'; base-uri 'none'"
    );
}

/**
 * Échappement HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirection HTTP puis fin de script.
 */
function redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}
