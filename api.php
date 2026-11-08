<?php
/**
 * Point d'entrée AJAX unique de WorkFlow IT.
 *
 * - GET  api.php?action=state   : état consolidé (synchronisation)
 * - GET  api.php?action=history : historique (formateur)
 * - POST api.php?action=...     : actions, avec jeton CSRF
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';

start_app_session();
header('X-Content-Type-Options: nosniff');

// ---------------------------------------------------------------------------
// Limitation globale des requêtes dynamiques (25 req/s par défaut)
// ---------------------------------------------------------------------------
try {
    if (!api_rate_limit_hit()) {
        header('Retry-After: 1');
        json_error('Trop de requêtes. Veuillez patienter quelques instants.', 429);
    }
} catch (Throwable $exception) {
    error_log('WorkFlow IT rate limit : ' . $exception->getMessage());
    json_error('Le service est temporairement indisponible.', 503);
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Lecture du corps JSON éventuel.
$input = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
    $input = array_merge($_POST, $input);
}

$authActions = ['login', 'login_confirm', 'login_cancel'];
$readActions = ['state', 'history'];
$writeActions = [
    'estimate_story', 'set_priority', 'move_story', 'toggle_sprint',
    'declare_ready', 'consult_client_feedback', 'validate_story', 'refuse_story',
    'create_task', 'update_task', 'move_task', 'archive_task', 'report_blockage',
    'chat_send', 'update_goal', 'update_retrospective',
    'propose_swap', 'respond_swap', 'cancel_swap',
    'respond_event',
    'teacher_pause', 'teacher_resume', 'teacher_announce', 'teacher_stop',
    'teacher_reset',
    'teacher_update_password', 'teacher_reset_password',
    'change_password',
    'moderate_edit', 'moderate_delete',
    'challenge_start', 'challenge_answer',
    'challenge_terminal_start', 'challenge_terminal_answer',
];

if (!in_array($action, array_merge($authActions, $readActions, $writeActions), true)) {
    json_error('Action inconnue.', 400);
}

// ---------------------------------------------------------------------------
// Authentification en deux étapes : vérification puis confirmation explicite.
// Ces actions ne donnent aucun accès aux données avant login_confirm().
// ---------------------------------------------------------------------------
if (in_array($action, $authActions, true)) {
    if ($method !== 'POST') {
        json_error('Méthode non autorisée : cette action nécessite POST.', 405);
    }
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf'] ?? '');
    if (!csrf_valid($token)) {
        json_error('Jeton CSRF invalide ou expiré. Rechargez la page.', 403);
    }

    try {
        bootstrap_storage();
        if ($action === 'login') {
            $pending = login_begin((string) ($input['login'] ?? ''), (string) ($input['dob'] ?? ''));
            json_respond(array_merge(['ok' => true], $pending));
        }
        if ($action === 'login_confirm') {
            login_confirm((string) ($input['pending'] ?? ''));
            json_respond(['ok' => true, 'redirect' => 'workflow.php']);
        }
        login_cancel();
        json_respond(['ok' => true]);
    } catch (AppError $exception) {
        json_error($exception->getMessage(), $exception->status);
    } catch (Throwable $exception) {
        error_log('WorkFlow IT authentification : ' . $exception->getMessage());
        json_error('Connexion impossible pour le moment. Réessayez.', 500);
    }
}

if (in_array($action, $writeActions, true)) {
    if ($method !== 'POST') {
        json_error('Méthode non autorisée : cette action nécessite POST.', 405);
    }
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf'] ?? '');
    if (!csrf_valid($token)) {
        json_error('Jeton CSRF invalide ou expiré. Rechargez la page.', 403);
    }
} elseif ($method !== 'GET' && $method !== 'POST') {
    json_error('Méthode non autorisée.', 405);
}

$user = current_user();
if ($user === null) {
    json_error('Session absente ou expirée. Reconnectez-vous.', 401);
}

try {
    bootstrap_storage();

    if ($action === 'state') {
        $clientVersion = isset($_GET['v']) && is_string($_GET['v']) ? $_GET['v'] : null;
        if ($clientVersion !== null && !preg_match('/^[a-f0-9]{32}$/', $clientVersion)) {
            $clientVersion = null;
        }
        json_respond(build_state_payload($user, $clientVersion));
    }

    if ($action === 'history') {
        json_respond(array_merge(['ok' => true], teacher_history($user)));
    }

    // Toutes les écritures sont sérialisées par un verrou global. Cela protège
    // les opérations qui touchent plusieurs fichiers JSON (événements, reset).
    $response = with_app_lock(static function () use ($action, $input, $user): array {
        return match ($action) {
            'estimate_story' => action_estimate_story($user, $input['story_id'] ?? null, $input['estimation'] ?? null),
            'set_priority' => action_set_priority($user, $input['story_id'] ?? null, $input['priority'] ?? null),
            'move_story' => action_move_story($user, $input['story_id'] ?? null, $input['direction'] ?? null),
            'toggle_sprint' => action_toggle_sprint($user, $input['story_id'] ?? null, $input['selected'] ?? null),
            'declare_ready' => action_declare_ready($user, $input['story_id'] ?? null),
            'consult_client_feedback' => action_consult_client_feedback($user, $input['story_id'] ?? null),
            'validate_story' => action_validate_story($user, $input['story_id'] ?? null),
            'refuse_story' => action_refuse_story($user, $input['story_id'] ?? null, $input['comment'] ?? null),
            'create_task' => action_create_task($user, $input),
            'update_task' => action_update_task($user, $input),
            'move_task' => action_move_task($user, $input['task_id'] ?? null, $input['etat'] ?? null, $input['blocage'] ?? ''),
            'archive_task' => action_archive_task($user, $input['task_id'] ?? null, $input['team'] ?? null, $input['confirmation'] ?? null),
            'report_blockage' => action_report_blockage($user, $input['task_id'] ?? null, $input['blocage'] ?? null),
            'chat_send' => action_chat_send($user, $input['text'] ?? null),
            'update_goal' => action_update_goal($user, $input['goal'] ?? null, $input['justification'] ?? null, $input['participants'] ?? []),
            'update_retrospective' => action_update_retrospective($user, $input['text'] ?? null),
            'propose_swap' => action_propose_swap($user, $input['target'] ?? null),
            'respond_swap' => action_respond_swap($user, $input['proposal_id'] ?? null, $input['accept'] ?? null),
            'cancel_swap' => action_cancel_swap($user, $input['proposal_id'] ?? null),
            'respond_event' => action_respond_event($user, $input['event_id'] ?? null, $input['option'] ?? null),
            'teacher_pause' => action_teacher_pause($user),
            'teacher_resume' => action_teacher_resume($user),
            'teacher_announce' => action_teacher_announce($user, $input['target'] ?? null, $input['text'] ?? null),
            'teacher_stop' => action_teacher_stop($user),
            'teacher_reset' => action_teacher_reset($user, $input['confirmation'] ?? null, $input['date'] ?? null),
            'teacher_update_password' => teacher_update_password($user, $input['password'] ?? null),
            'teacher_reset_password' => teacher_reset_password($user, $input['login'] ?? null),
            'change_password' => change_own_password(
                $user,
                $input['current_password'] ?? null,
                $input['new_password'] ?? null,
                $input['confirm_password'] ?? null
            ),
            'moderate_edit' => action_moderate_edit($user, $input),
            'moderate_delete' => action_moderate_delete($user, $input),
            'challenge_start' => action_challenge_start($user, $input['task_id'] ?? null),
            'challenge_answer' => action_challenge_answer($user, $input['challenge_id'] ?? null, $input['option'] ?? null),
            'challenge_terminal_start' => action_challenge_terminal_start($user, $input['story_id'] ?? null),
            'challenge_terminal_answer' => action_challenge_terminal_answer($user, $input['challenge_id'] ?? null, $input['command'] ?? null),
            default => throw new AppError('Action inconnue.', 400),
        };
    });

    json_respond(array_merge(['ok' => true], is_array($response) ? $response : []));
} catch (AppError $exception) {
    json_error($exception->getMessage(), $exception->status);
} catch (Throwable $exception) {
    error_log('WorkFlow IT API : ' . $exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine());
    json_error('Erreur serveur. Les données n’ont pas été modifiées. Consultez les journaux si le problème persiste.', 500);
}
