<?php
/**
 * Point d'entrée de l'application WorkFlow IT.
 * Affiche la page de connexion ou l'interface adaptée au rôle.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';

start_app_session();
send_html_security_headers();

$fatal = null;
try {
    bootstrap_storage();
} catch (Throwable $exception) {
    error_log('WorkFlow IT bootstrap : ' . $exception->getMessage());
    $fatal = 'L’application ne peut pas démarrer. Vérifiez les droits sur le dossier datas et la validité de users.txt.';
}

$loginError = null;
$notice = null;
$pendingLogin = null;

// ---------------------------------------------------------------------------
// Connexion en deux étapes (repli sans JavaScript) / déconnexion
// ---------------------------------------------------------------------------
if ($fatal === null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postAction = (string) ($_POST['action'] ?? '');

    if (in_array($postAction, ['login', 'login_confirm', 'login_cancel'], true)) {
        $token = $_POST['csrf'] ?? '';
        if (!csrf_valid($token)) {
            login_cancel();
            $loginError = 'Session expirée. Rechargez la page et réessayez.';
        } elseif ($postAction === 'login') {
            try {
                $pendingLogin = login_begin((string) ($_POST['login'] ?? ''), (string) ($_POST['dob'] ?? ''));
            } catch (AppError $exception) {
                $loginError = $exception->getMessage();
            } catch (Throwable $exception) {
                error_log('WorkFlow IT login : ' . $exception->getMessage());
                $loginError = 'Connexion impossible pour le moment.';
            }
        } elseif ($postAction === 'login_confirm') {
            try {
                login_confirm((string) ($_POST['pending'] ?? ''));
                redirect('workflow.php');
            } catch (AppError $exception) {
                login_cancel();
                $loginError = $exception->getMessage();
            } catch (Throwable $exception) {
                error_log('WorkFlow IT login confirm : ' . $exception->getMessage());
                login_cancel();
                $loginError = 'Connexion impossible pour le moment.';
            }
        } else {
            login_cancel();
            redirect('workflow.php');
        }
    } elseif ($postAction === 'forgot_password') {
        if (!csrf_valid($_POST['csrf'] ?? '')) {
            $notice = 'La demande n’a pas pu être envoyée. Rechargez la page et réessayez.';
        } else {
            try {
                request_password_reset((string) ($_POST['forgot_login'] ?? ''));
            } catch (Throwable $exception) {
                error_log('WorkFlow IT mot de passe oublié : ' . $exception->getMessage());
            }
            $notice = 'Si ce compte est éligible, la demande a été transmise au formateur.';
        }
    } elseif ($postAction === 'logout') {
        if (csrf_valid($_POST['csrf'] ?? '')) {
            logout_user();
            redirect('workflow.php?deconnecte=1');
        }
        $notice = 'La déconnexion a échoué. Rechargez la page.';
    }
}

if (isset($_GET['deconnecte'])) {
    $notice = 'Vous êtes déconnecté.';
}

$user = current_user();
if ($user === null && $loginError === null) {
    $pendingLogin = login_pending();
}
$csrf = csrf_token();

// ---------------------------------------------------------------------------
// Données de session pour l'affichage initial
// ---------------------------------------------------------------------------
$sessionForPage = null;
$phaseForPage = 'closed';
if ($fatal === null) {
    try {
        $sessionForPage = current_session();
        $phaseForPage = session_phase($sessionForPage);
    } catch (Throwable $exception) {
        $fatal = 'Les données de séance sont illisibles.';
    }
}

$sessionInfo = null;
if ($sessionForPage !== null) {
    $sessionInfo = session_public($sessionForPage, $phaseForPage);
}

$loginValue = '';
$loginPendingToken = '';
if ($pendingLogin !== null) {
    $loginValue = (string) ($pendingLogin['login'] ?? '');
    $loginPendingToken = (string) ($pendingLogin['pending'] ?? $pendingLogin['token'] ?? '');
} elseif ($loginError !== null) {
    $loginValue = trim((string) ($_POST['login'] ?? ''));
}

// ===========================================================================
// Page de connexion
// ===========================================================================
if ($user === null || $fatal !== null):
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e($csrf) ?>">
    <title><?= e(APP_NAME) ?> — connexion</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
<main class="login-shell">
    <section class="login-card" aria-labelledby="login-title">
        <div class="brand brand-large">
            <span class="brand-mark" aria-hidden="true">WF</span>
            <div>
                <h1 id="login-title"><?= e(APP_NAME) ?></h1>
                <p class="muted">Simulation Scrum — gestion des demandes informatiques</p>
            </div>
        </div>

        <?php if ($fatal !== null): ?>
            <div class="alert alert-error" role="alert"><?= e($fatal) ?></div>
        <?php else: ?>
            <div class="alert alert-error login-alert" id="login-error" role="alert" aria-live="assertive"<?= $loginError === null ? ' hidden' : '' ?>><?= e((string) ($loginError ?? '')) ?></div>
            <?php if ($notice !== null): ?>
                <div class="alert alert-info" role="status"><?= e($notice) ?></div>
            <?php endif; ?>

            <form method="post" action="workflow.php" class="login-form" id="login-form" autocomplete="on">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <label for="login">Identifiant</label>
                <input id="login" name="login" type="text" required maxlength="40"
                       autocapitalize="none" autocomplete="username"
                       spellcheck="false" placeholder="votre identifiant (ex: jharrisson)" value="<?= e($loginValue) ?>">
                <label for="dob">Mot de passe</label>
                <input id="dob" name="dob" type="password" required inputmode="numeric"
                       pattern="[0-9]{8}" maxlength="8" autocomplete="current-password"
                       placeholder="8 chiffres">
                <p class="hint">8 chiffres. À la première connexion, utilisez votre date de naissance au format JJMMAAAA.</p>
                <button class="btn btn-primary btn-block" type="submit" id="login-submit">Se connecter</button>
            </form>

            <details class="login-forgot">
                <summary>Mot de passe oublié ?</summary>
                <form method="post" action="workflow.php" class="login-form">
                    <input type="hidden" name="action" value="forgot_password">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <label for="forgot-login">Identifiant</label>
                    <input id="forgot-login" name="forgot_login" type="text" required maxlength="30"
                           autocapitalize="none" autocomplete="username" spellcheck="false"
                           placeholder="votre identifiant">
                    <p class="hint">La demande est transmise au formateur. Aucun mot de passe n’est modifié automatiquement.</p>
                    <button class="btn btn-secondary btn-block" type="submit">Envoyer la demande</button>
                </form>
            </details>

            <div class="login-meta">
                <?php if ($sessionInfo !== null): ?>
                    <p>
                        <strong>Séance :</strong> <?= e($sessionInfo['date']) ?>
                        · <?= e($sessionInfo['schedule']['open']) ?> ouverture
                        · <?= e($sessionInfo['schedule']['start']) ?> — <?= e($sessionInfo['schedule']['end']) ?> simulation
                    </p>
                    <p><span class="badge badge-phase phase-<?= e($sessionInfo['phase']) ?>"><?= e($sessionInfo['phase_label']) ?></span></p>
                <?php endif; ?>
                <p class="muted">Le serveur ne renvoie jamais les dates de naissance au navigateur. Le mot de passe initial est votre date de naissance au format JJMMAAAA ; vous pouvez le changer après la connexion.</p>
            </div>
        <?php endif; ?>
    </section>

    <aside class="login-aside" aria-label="Présentation">
        <h2>WorkFlow IT</h2>
        <p>Une application interne de gestion des demandes et interventions informatiques.</p>
        <ul>
            <li>Trois équipes Scrum travaillent sur le même produit fictif.</li>
            <li>85 minutes de simulation active, après 10 minutes de prise en main.</li>
            <li>Backlog, Story Points, tâches, Kanban, événements, Review, rétrospective et chat.</li>
        </ul>
        <p class="muted">Les échanges d’équipe peuvent être consultés par le formateur.</p>
    </aside>
</main>

<div class="modal" id="login-confirm-modal"<?= $pendingLogin === null ? ' hidden' : '' ?>>
    <div class="modal-backdrop" data-login-cancel></div>
    <div class="modal-panel modal-panel-dialog" role="dialog" aria-modal="true" aria-labelledby="login-confirm-title" aria-describedby="login-confirm-message">
        <div class="modal-header">
            <div>
                <p class="ui-modal-kicker">Connexion vérifiée</p>
                <h2 id="login-confirm-title">Rejoindre la simulation</h2>
            </div>
            <button type="button" class="icon-button" data-login-cancel aria-label="Fermer">×</button>
        </div>
        <form id="login-confirm-form" method="post" action="workflow.php">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="pending" id="login-pending" value="<?= e($loginPendingToken) ?>">
            <p class="ui-modal-message" id="login-confirm-message">
                Authentification réussie. Vous êtes sur le point de rejoindre la simulation Scrum
                avec le compte <strong id="login-confirm-account"><?= e($loginValue) ?></strong>.
                Souhaitez-vous rejoindre la partie maintenant ?
            </p>
            <div class="modal-actions">
                <button type="submit" class="btn btn-ghost" name="action" value="login_cancel">Annuler</button>
                <button type="submit" class="btn btn-primary" name="action" value="login_confirm" id="login-confirm-submit">Rejoindre la partie</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/login.js" defer></script>
</body>
</html>
<?php
    exit;
endif;

// ===========================================================================
// Interface authentifiée
// ===========================================================================
$isTeacher = !empty($user['is_teacher']);
$team = null;
$role = 'FORMATEUR';
$roleLabel = 'Formateur';
$initialRoleLabel = role_label((string) ($user['initial_role'] ?? 'FORMATEUR'));
if (!$isTeacher) {
    try {
        $team = load_team((int) $user['team']);
        $currentRole = team_role($team, (string) $user['login']);
        if ($currentRole === null) {
            throw new RuntimeException('Membre absent de l’équipe.');
        }
        $role = $currentRole;
        $roleLabel = role_label($currentRole);
    } catch (Throwable $exception) {
        error_log('WorkFlow IT team : ' . $exception->getMessage());
        logout_user();
        redirect('workflow.php');
    }
}
$roleBadgeClass = ['PO' => 'po', 'SCRUM_MASTER' => 'sm', 'DEVELOPER' => 'dev'][$role] ?? 'formateur';

$defaultView = $isTeacher ? 'global' : 'dashboard';
$flash = take_flash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e($csrf) ?>">
    <meta name="app-version" content="<?= e(APP_VERSION) ?>">
    <title><?= e(APP_NAME) ?> — <?= $isTeacher ? 'formateur' : e('équipe ' . $user['team']) ?></title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="app-page" data-teacher="<?= $isTeacher ? '1' : '0' ?>">
<div class="app-shell" id="app-shell" data-view="<?= e($defaultView) ?>">

    <header class="topbar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">WF</span>
            <div class="brand-text">
                <strong><?= e(APP_NAME) ?></strong>
                <span class="muted small"><?= $isTeacher ? 'Supervision formateur' : 'Équipe ' . (int) $user['team'] ?></span>
            </div>
        </div>

        <div class="clock-block" aria-label="Horloge de la simulation">
            <div class="clock-line">
                <span class="clock" id="clock-time">--:--:--</span>
                <span class="badge badge-phase phase-<?= e($phaseForPage) ?>" id="session-badge">
                    <?= e(phase_label($phaseForPage)) ?>
                </span>
            </div>
            <div class="clock-sub">
                <span>Début <?= e(SIM_START_TIME) ?></span>
                <span>Fin <?= e(SIM_END_TIME) ?></span>
                <span class="countdown" id="clock-remaining">--:--</span>
            </div>
            <div class="progress" aria-hidden="true"><span id="clock-progress"></span></div>
            <div class="sync-status-wrap">
                <div class="sync-status" id="sync-status" aria-live="polite">Synchronisation…</div>
                <div class="sync-progress" aria-hidden="true"><span id="sync-progress"></span></div>
            </div>
        </div>

        <div class="user-block">
            <div class="user-identity">
                <strong><?= e($user['login']) ?></strong>
                <?php if ($isTeacher): ?>
                    <span class="muted small">Formateur</span>
                <?php else: ?>
                    <span class="user-role-line">Rôle initial : <span id="user-role-initial"><?= e($initialRoleLabel) ?></span></span>
                    <span class="user-role-line user-role-current" id="user-role-current-line" aria-live="polite">Rôle actuel :
                        <span class="badge badge-role-<?= e($roleBadgeClass) ?>" id="user-role-current"><?= e($roleLabel) ?></span>
                    </span>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-technical btn-small" data-action="change-password">Mon mot de passe</button>
            <?php if ($isTeacher): ?>
                <button type="button" class="btn btn-help btn-small" id="teacher-help-button" aria-haspopup="dialog">Aide formateur</button>
            <?php endif; ?>
            <button type="button" class="btn btn-help btn-small" id="help-button" aria-haspopup="dialog">Aide générale</button>
            <form method="post" action="workflow.php" class="inline-form" data-form="logout">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="btn btn-technical btn-small">Déconnexion</button>
            </form>
        </div>
    </header>

    <nav class="main-nav" id="main-nav" aria-label="Navigation principale">
        <?php if ($isTeacher): ?>
            <button type="button" class="nav-item" data-view="global">Vue globale <span class="nav-badge" id="password-requests-badge" hidden></span></button>
            <button type="button" class="nav-item" data-view="team-1">Équipe 1</button>
            <button type="button" class="nav-item" data-view="team-2">Équipe 2</button>
            <button type="button" class="nav-item" data-view="team-3">Équipe 3</button>
            <button type="button" class="nav-item" data-view="announcements">Annonces</button>
            <button type="button" class="nav-item" data-view="moderation">Modération</button>
            <button type="button" class="nav-item" data-view="history">Historique</button>
            <button type="button" class="nav-item" data-view="bilan">Bilan</button>
        <?php else: ?>
            <button type="button" class="nav-item" data-view="dashboard">Tableau de bord</button>
            <button type="button" class="nav-item" data-view="review">Review & rétro</button>
            <button type="button" class="nav-item" data-view="backlog">Product Backlog <span class="nav-badge" id="backlog-badge" hidden></span></button>
            <button type="button" class="nav-item" data-view="kanban">Kanban <span class="nav-badge" id="kanban-badge" hidden></span></button>
            <button type="button" class="nav-item" data-view="events">Événements <span class="nav-badge" id="events-badge" hidden></span></button>
            <button type="button" class="nav-item" data-view="chat">Chat <span class="nav-badge" id="chat-badge" hidden></span></button>
            <button type="button" class="nav-item" data-view="roles">Rôles <span class="nav-badge" id="swaps-badge" hidden></span></button>
            <button type="button" class="nav-item" data-view="history">Historique</button>
            <button type="button" class="nav-item" data-view="bilan">Bilan</button>
        <?php endif; ?>
    </nav>

    <main class="app-main" id="main-content">
        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <div id="flash-zone" class="flash-zone" role="status" aria-live="polite"></div>
        <div id="network-status" class="network-status" hidden></div>
        <div id="view-root" class="view-root" aria-live="polite">
            <div class="loading-card">
                <span class="spinner" aria-hidden="true"></span>
                Chargement de l’état de la simulation…
            </div>
        </div>
    </main>
</div>

<!-- Boîte de dialogue générique : alertes, confirmations et saisies. -->
<div class="modal" id="ui-modal" hidden>
    <div class="modal-backdrop" data-close-modal="ui-modal"></div>
    <div class="modal-panel modal-panel-dialog" id="ui-modal-panel" role="dialog" aria-modal="true" aria-labelledby="ui-modal-title" aria-describedby="ui-modal-message">
        <div class="modal-header">
            <div>
                <p class="ui-modal-kicker" id="ui-modal-kicker">Confirmation</p>
                <h2 id="ui-modal-title">Confirmer l’action</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="ui-modal" aria-label="Fermer">×</button>
        </div>
        <form id="ui-modal-form">
            <p class="ui-modal-message" id="ui-modal-message"></p>
            <div class="field" id="ui-modal-field" hidden>
                <label for="ui-modal-input" id="ui-modal-label">Saisie</label>
                <textarea id="ui-modal-input" rows="4" maxlength="500"></textarea>
                <p class="hint" id="ui-modal-hint" hidden></p>
                <p class="form-error" id="ui-modal-error" role="alert" hidden></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" id="ui-modal-cancel" data-close-modal="ui-modal">Annuler</button>
                <button type="submit" class="btn btn-primary" id="ui-modal-confirm">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<!-- Réception client simulée, consultée par le PO avant sa décision. -->
<div class="modal" id="client-feedback-modal" hidden>
    <div class="modal-backdrop" data-close-modal="client-feedback-modal"></div>
    <div class="modal-panel modal-panel-dialog" id="client-feedback-panel" role="dialog" aria-modal="true" aria-labelledby="client-feedback-title">
        <div class="modal-header">
            <div>
                <p class="ui-modal-kicker">Réception client</p>
                <h2 id="client-feedback-title">Réponse du client</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="client-feedback-modal" aria-label="Fermer">×</button>
        </div>
        <p class="client-feedback-meta" id="client-feedback-story"></p>
        <p class="client-feedback-meta"><strong>Responsable client :</strong> <span id="client-feedback-responsible"></span></p>
        <div class="client-feedback-message" id="client-feedback-message" data-tone="success"></div>
        <p class="client-feedback-meta"><strong>Développeur :</strong> <span id="client-feedback-developer"></span></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-primary" data-close-modal="client-feedback-modal">J’ai compris</button>
        </div>
    </div>
</div>

<!-- Statistiques des décisions du client, consultables depuis le Kanban. -->
<div class="modal" id="kanban-stats-modal" hidden>
    <div class="modal-backdrop" data-close-modal="kanban-stats-modal"></div>
    <div class="modal-panel modal-panel-wide" role="dialog" aria-modal="true" aria-labelledby="kanban-stats-title">
        <div class="modal-header">
            <div>
                <p class="ui-modal-kicker">Qualité des tâches</p>
                <h2 id="kanban-stats-title">Statistiques des décisions client</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="kanban-stats-modal" aria-label="Fermer">×</button>
        </div>
        <div id="kanban-stats-body"></div>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" data-close-modal="kanban-stats-modal">Fermer</button>
        </div>
    </div>
</div>

<!-- Changement du mot de passe du compte connecté. -->
<div class="modal" id="password-modal" hidden>
    <div class="modal-backdrop" data-close-modal="password-modal"></div>
    <div class="modal-panel modal-panel-dialog" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
        <div class="modal-header">
            <div>
                <p class="ui-modal-kicker">Sécurité du compte</p>
                <h2 id="password-modal-title">Changer mon mot de passe</h2>
            </div>
            <button type="button" class="icon-button" data-close-modal="password-modal" aria-label="Fermer">×</button>
        </div>
        <form id="password-form" data-form="password">
            <div class="field">
                <label for="password-current">Mot de passe actuel</label>
                <input type="password" id="password-current" name="current_password" inputmode="numeric"
                       pattern="[0-9]{8}" maxlength="8" autocomplete="current-password" required>
            </div>
            <div class="field" style="margin-top:10px">
                <label for="password-new">Nouveau mot de passe</label>
                <input type="password" id="password-new" name="new_password" inputmode="numeric"
                       pattern="[0-9]{8}" maxlength="8" autocomplete="new-password" required>
            </div>
            <div class="field" style="margin-top:10px">
                <label for="password-confirm">Confirmer le nouveau mot de passe</label>
                <input type="password" id="password-confirm" name="confirm_password" inputmode="numeric"
                       pattern="[0-9]{8}" maxlength="8" autocomplete="new-password" required>
            </div>
            <p class="hint">Exactement 8 chiffres. Si vous n’avez jamais changé votre mot de passe, il s’agit de votre date de naissance au format JJMMAAAA.</p>
            <p class="form-error" id="password-error" role="alert" hidden></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" data-close-modal="password-modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Validation technique avant « Terminé » ou « Prête à valider ». -->
<div class="modal" id="challenge-modal" hidden>
    <div class="modal-backdrop" data-close-modal="challenge-modal"></div>
    <div class="modal-panel modal-panel-dialog" role="dialog" aria-modal="true" aria-labelledby="challenge-modal-title">
        <div class="modal-header">
            <div class="modal-title-row">
                <div>
                    <p class="ui-modal-kicker">Validation technique</p>
                    <h2 id="challenge-modal-title">Challenge avant « Terminé »</h2>
                </div>
                <button type="button" class="btn btn-help btn-small" id="challenge-help-button" data-action="challenge-help" hidden>Aide</button>
            </div>
            <button type="button" class="icon-button" data-close-modal="challenge-modal" aria-label="Fermer">×</button>
        </div>
        <div class="modal-body">
            <p class="muted small" id="challenge-story"></p>
            <p class="muted small" id="challenge-instruction"></p>
            <p id="challenge-question"></p>
            <div class="field" id="challenge-options"></div>
            <div id="challenge-terminal" class="terminal-shell" hidden>
                <div id="challenge-terminal-output" class="terminal-output" aria-live="polite" aria-label="Historique du terminal"></div>
                <form id="challenge-terminal-form" class="terminal-form">
                    <span id="challenge-terminal-prompt" class="terminal-prompt" aria-hidden="true"></span>
                    <input type="text" id="challenge-terminal-input" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="200" aria-label="Terminal" disabled>
                </form>
            </div>
            <p class="form-error" id="challenge-error" role="alert" hidden></p>
            <p class="muted small" id="challenge-countdown"></p>
            <p class="muted small" id="challenge-explanation" hidden></p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" data-close-modal="challenge-modal">Annuler</button>
            <button type="button" class="btn btn-primary" id="challenge-submit" disabled>Valider</button>
        </div>
    </div>
</div>

<!-- Boîte de dialogue unique pour créer / modifier une tâche. -->
<div class="modal" id="task-modal" hidden>
    <div class="modal-backdrop" data-close-modal="task-modal"></div>
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="task-modal-title">
        <div class="modal-header">
            <div class="modal-title-row">
                <h2 id="task-modal-title">Tâche technique</h2>
                <button type="button" class="btn btn-help btn-small" id="task-help-button" data-action="task-help">Aide</button>
            </div>
            <button type="button" class="icon-button" data-close-modal="task-modal" aria-label="Fermer">×</button>
        </div>
        <form id="task-form" class="form-grid">
            <input type="hidden" name="task_id" id="task-id">
            <div class="field">
                <label for="task-story">User Story</label>
                <select name="us_id" id="task-story" required></select>
            </div>
            <div class="field">
                <label for="task-title">Titre</label>
                <input type="text" name="titre" id="task-title" required minlength="3" maxlength="120">
            </div>
            <div class="field field-full">
                <label for="task-description">Description <span class="muted">(obligatoire, 10 caractères minimum)</span></label>
                <textarea name="description" id="task-description" rows="3" minlength="10" maxlength="500" required></textarea>
            </div>
            <div class="field">
                <label for="task-hours">Estimation (heures)</label>
                <input type="number" name="heures" id="task-hours" min="0.5" max="2" step="0.5" required value="2">
            </div>
            <div class="field">
                <label for="task-assignee">Membre affecté <span class="muted">(obligatoire)</span></label>
                <select name="assigne" id="task-assignee" required>
                </select>
            </div>
            <div class="field">
                <label>Risques de la User Story</label>
                <p id="task-risks" class="tiny muted" style="margin:0">Aucun risque majeur identifié à ce stade.</p>
            </div>
            <div class="field">
                <label for="task-state">État Kanban</label>
                <select name="etat" id="task-state">
                    <option value="a_faire">À faire</option>
                    <option value="en_cours">En cours</option>
                    <option value="bloque">Bloqué</option>
                </select>
            </div>
            <div class="field field-full" id="task-blocking-field" hidden>
                <label for="task-blocking">Explication du blocage <span class="muted">(si bloqué)</span></label>
                <input type="text" name="blocage" id="task-blocking" maxlength="300">
            </div>
            <div class="modal-actions field-full">
                <button type="button" class="btn btn-ghost" data-close-modal="task-modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Boîte de dialogue de modération : le formateur corrige un texte libre. -->
<div class="modal" id="moderation-modal" hidden>
    <div class="modal-backdrop" data-close-modal="moderation-modal"></div>
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="moderation-modal-title">
        <div class="modal-header">
            <h2 id="moderation-modal-title">Modifier un contenu</h2>
            <button type="button" class="icon-button" data-close-modal="moderation-modal" aria-label="Fermer">×</button>
        </div>
        <form id="moderation-form" data-form="moderation">
            <input type="hidden" name="type" id="moderation-type">
            <input type="hidden" name="team" id="moderation-team">
            <input type="hidden" name="content_id" id="moderation-content">
            <input type="hidden" name="field" id="moderation-field">
            <input type="hidden" name="index" id="moderation-index">
            <div class="field">
                <label for="moderation-text" id="moderation-label">Contenu</label>
                <textarea name="text" id="moderation-text" rows="6" required></textarea>
                <p class="hint">Cette correction sera visible par les étudiants et tracée dans l’historique.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" data-close-modal="moderation-modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer la correction</button>
            </div>
        </form>
    </div>
</div>

<!-- Aide pédagogique, accessible à tout moment. -->
<div class="modal" id="help-modal" hidden>
    <div class="modal-backdrop" data-close-modal="help-modal"></div>
    <div class="modal-panel modal-panel-wide" role="dialog" aria-modal="true" aria-labelledby="help-modal-title">
        <div class="modal-header">
            <h2 id="help-modal-title">Aide — simulation Scrum WorkFlow IT</h2>
            <button type="button" class="icon-button" data-close-modal="help-modal" aria-label="Fermer">×</button>
        </div>
        <div class="help-content">
            <?php if ($isTeacher): ?>
                <div class="help-callout">
                    <strong>Vous êtes formateur.</strong> Cette aide décrit le parcours étudiant.
                    Votre aide dédiée est accessible avec « Aide formateur » dans l’en-tête.
                </div>
            <?php endif; ?>

            <section>
                <h3>Bien démarrer</h3>
                <p>Après 10 minutes de prise en main, vous simulez pendant <strong>85 minutes</strong> le pilotage du produit <strong>WorkFlow IT</strong>, une application interne de gestion des demandes informatiques pour une entreprise de 450 salariés répartis sur trois sites. Vous ne développez pas réellement l’application : vous priorisez, estimez, décomposez, suivez, adaptez et validez le travail.</p>
                <p>Utilisez le menu pour passer du <strong>Tableau de bord</strong> au <strong>Product Backlog</strong>, au <strong>Kanban</strong>, aux <strong>Événements</strong>, à <strong>Review & rétro</strong>, au <strong>Chat</strong> et aux <strong>Rôles</strong>. Chaque outil dispose d’une aide contextuelle adaptée aux actions permises selon les rôles.</p>
            </section>

            <section>
                <h3>Votre rôle : ce que vous devez faire</h3>
                <p class="small">Chaque membre peut sélectionner des User Stories, participer au Sprint Goal, répondre aux événements, utiliser le chat et proposer un échange de rôle. Les actions ci-dessous sont celles qui changent selon votre rôle.</p>
                <div class="help-role-grid">
                    <article class="help-role">
                        <h4>Product Owner (PO)</h4>
                        <p>Vous portez la valeur du produit et représentez le client.</p>
                        <ul>
                            <li>Lire les annonces, le Sprint Goal et l’événement actif.</li>
                            <li>Classer les User Stories : priorité haute, moyenne ou basse, et ordre avec les flèches ↑ / ↓.</li>
                            <li>Laisser les Developers estimer ; vérifier la valeur métier et les critères d’acceptation.</li>
                            <li>Valider une User Story « à valider » seulement si la Definition of Done est respectée ; sinon la refuser avec un commentaire actionnable.</li>
                            <li>Participer aux décisions collectives et expliquer vos arbitrages dans le chat.</li>
                        </ul>
                        <p class="help-limit"><strong>Vos limites :</strong> pas d’estimation, pas de création, de modification ou de déplacement de tâches, pas de changement de rôle sans accord. Vous pouvez signaler un blocage.</p>
                    </article>
                    <article class="help-role">
                        <h4>Scrum Master</h4>
                        <p>Vous êtes le facilitateur. Vous n’avez pas d’action exclusive : votre efficacité se voit dans la coordination.</p>
                        <ul>
                            <li>Surveiller le Kanban : blocages, tâches stagnantes, travail en cours trop élevé.</li>
                            <li>Signaler un blocage avec une cause précise, puis relancer sa résolution.</li>
                            <li>Vérifier que l’équipe répond aux événements et garde un Sprint Goal clair.</li>
                            <li>Reformuler les décisions dans le chat et aider à répartir le travail.</li>
                            <li>Proposer un échange de rôle si un déséquilibre bloque l’équipe, avec l’accord des deux membres.</li>
                        </ul>
                        <p class="help-limit"><strong>Vos limites :</strong> pas de priorisation, d’estimation, de création/modification/déplacement de tâches, de validation ou de refus. Vous pouvez signaler un blocage, mais ne faites pas le travail à la place des autres.</p>
                    </article>
                    <article class="help-role">
                        <h4>Developers</h4>
                        <p>Vous produisez l’incrément et faites avancer les tâches.</p>
                        <ul>
                            <li>Estimer les User Stories sélectionnées avec l’échelle 1, 2, 3, 5, 8, 13 après discussion.</li>
                            <li>Découper les User Stories sélectionnées en tâches de 0,5 à 2 h, les affecter et les démarrer.</li>
                            <li>Déplacer les tâches dans le Kanban ; pour une tâche bloquée, écrire la cause.</li>
                            <li>Terminer toutes les tâches, vérifier les critères et la Definition of Done, puis déclarer « Prête à valider ».</li>
                            <li>Signaler une dépendance ou demander une clarification au PO plutôt que de deviner.</li>
                        </ul>
                        <p class="help-limit"><strong>Vos limites :</strong> pas de priorisation, de validation ou de refus, ni de changement de rôle sans accord. Vous pouvez signaler un blocage.</p>
                    </article>
                </div>
            </section>

            <section>
                <h3>Déroulé conseillé</h3>
                <ol class="help-steps">
                    <li><strong>Lire (5 min) :</strong> Sprint Goal, annonces, événement actif, backlog, capacité et rôles.</li>
                    <li><strong>Choisir (10 min) :</strong> sélectionner un lot cohérent de User Stories. Il n’y a ni minimum ni maximum imposé.</li>
                    <li><strong>Estimer (10 min) :</strong> les Developers estiment ; le PO clarifie les critères, pas les Story Points.</li>
                    <li><strong>Découper (15 min) :</strong> les Developers créent des tâches, les affectent et passent les premières en cours.</li>
                    <li><strong>Produire et adapter (25 min) :</strong> déplacer les tâches, résoudre les blocages et traiter les événements. Si le Sprint Goal change, la décision doit être collective et justifiée.</li>
                    <li><strong>Valider (10 min) :</strong> les Developers déclarent les User Stories prêtes, le PO vérifie la Definition of Done puis valide ou refuse.</li>
                    <li><strong>Review et rétrospective (10 min) :</strong> examiner l’incrément, les refus et les blocages, puis enregistrer les points forts, difficultés et améliorations dans « Review & rétro ».</li>
                </ol>
                <p class="help-note">Ces durées sont indicatives et totalisent 85 minutes. Le chronomètre du serveur et les événements peuvent modifier le rythme ; adaptez-vous sans attendre la fin.</p>
            </section>

            <section>
                <h3>Backlog, Sprint et valeur</h3>
                <p><strong>Product Backlog :</strong> liste ordonnée de toutes les User Stories candidates, avec leur priorité, leur valeur métier, leur estimation en Story Points et leur état. C’est la source de référence pour choisir ce qui entre dans le Sprint. Le PO en porte l’ordre et la valeur ; les Developers l’estiment ; l’équipe le fait évoluer au fil des décisions.</p>
                <p><strong>Sprint :</strong> période de la simulation pendant laquelle l’équipe traite les User Stories sélectionnées. <strong>Incrément :</strong> résultat cumulé des User Stories terminées et conformes à la Definition of Done. Dans la simulation, l’incrément présenté ne retient que celles validées par le PO : une User Story « prête à valider » n’en fait pas encore partie tant que le PO ne l’a pas acceptée. L’incrément simulé est donc l’ensemble des User Stories validées, avec leur valeur métier.</p>
                <p><strong>Estimation en Story Points :</strong> unité relative de complexité et d’effort, choisie par les Developers après discussion sur l’échelle 1, 2, 3, 5, 8 et 13. Elle ne mesure ni des heures ni la productivité : une User Story à 8 points n’est pas nécessairement plus longue qu’une User Story à 3 points, elle est plus complexe à réaliser. Les heures servent à estimer les tâches techniques.</p>
                <p><strong>Valeur métier :</strong> importance relative d’une User Story pour le client et pour le produit. Elle est portée par la User Story et aide le PO à arbitrer la priorité et l’ordre ; elle ne se déduit pas des Story Points. Une User Story à 8 points peut avoir une faible valeur, et une User Story à 2 points peut être très utile : ce sont deux axes différents.</p>
                <p><strong>Capacité et charge :</strong> la capacité initiale est le temps de travail disponible des Developers ; les événements peuvent la réduire. La capacité disponible est ce temps après réduction. La charge des tâches est la somme des estimations en heures de toutes les tâches actives non archivées, terminées ou non ; le reste à faire ne compte que les tâches non terminées. La capacité restante est la capacité disponible diminuée des heures des tâches terminées : elle indique le temps encore mobilisable, tandis que le reste à faire mesure le volume de travail qui subsiste. Pour repérer une surcharge, comparez le reste à faire à la capacité restante, et non la charge totale à la capacité restante.</p>
                <p><strong>Valeur métier validée :</strong> somme de la valeur métier des User Stories validées par le PO. <strong>Story Points validés :</strong> somme de leurs Story Points. Ces deux indicateurs ne mesurent pas l’activité : ils ne prennent en compte que les User Stories validées par le PO. Une User Story seulement sélectionnée, dont les tâches sont terminées ou qui est prête à valider ne les augmente pas. Ils expriment donc le résultat réellement accepté par le PO, celui que la Review peut présenter.</p>
                <p><strong>États d’une User Story :</strong> à faire, en cours, à valider, validée ou refusée. « À valider » signifie que les Developers ont terminé ; seul le PO peut alors valider ou refuser. Une User Story refusée reste dans le Sprint avec un commentaire : l’équipe corrige le travail avant de la déclarer à nouveau prête.</p>
                <p>L’équipe sélectionne librement les User Stories à traiter. Une User Story validée ne peut plus être retirée du Sprint ; retirer une User Story qui possède encore des tâches actives impose de les archiver d’abord.</p>
                <p>Le Sprint Goal initial est collectif. Une modification doit être justifiée, impliquer au moins deux membres et est historisée. Il doit rester cohérent avec les User Stories sélectionnées.</p>
            </section>

            <section>
                <h3>Tâches, Kanban et Definition of Done</h3>
                <p>Les Developers créent, modifient et déplacent les tâches. Le PO et le Scrum Master peuvent seulement signaler un blocage sur une tâche active. Chaque tâche est rattachée à une User Story sélectionnée, estimée en heures simulées (0,5 à 2 h) et peut être affectée à un Developer. Une User Story ne peut recevoir qu’une seule tâche active : pour en ajouter une nouvelle, l’ancienne doit être archivée.</p>
                <p><strong>Kanban :</strong> À faire → En cours → Bloqué → Terminé. Une tâche bloquée doit être expliquée. Une tâche ne se supprime pas : elle s’archive.</p>
                <p><strong>Definition of Done :</strong> il s’agit d’une règle de qualité commune à toutes les User Stories. Elle décrit ce qui doit être vrai pour qu’un travail soit considéré comme terminé et démontrable. Elle ne remplace pas les critères d’acceptation propres à chaque User Story : ceux-ci précisent le besoin attendu, tandis que la Definition of Done garantit le niveau de qualité.</p>
                <ul>
                    <?php foreach (definition_of_done() as $item): ?>
                        <li><?= e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Une tâche marquée « Terminé » ne suffit pas : la User Story ne peut être déclarée prête que lorsqu’elle possède au moins une tâche, que toutes ses tâches sont terminées et que la Definition of Done est satisfaite. Le PO vérifie ensuite la Definition of Done et les critères ; s’il refuse, son commentaire indique ce qui reste à corriger.</p>
            </section>

            <section>
                <h3>Review et rétrospective</h3>
                <p>La <strong>Sprint Review</strong> est l’événement de fin de Sprint où l’équipe inspecte l’incrément et adapte le Product Backlog, en associant les parties prenantes. Dans la simulation, la vue <strong>Review &amp; rétro</strong> permet de la préparer : elle rassemble les User Stories validées, prêtes à valider, refusées ou encore en travail, ainsi que la Definition of Done. Elle ne remplace pas la validation : le PO valide ou refuse depuis le Product Backlog.</p>
                <p><strong>Incrément :</strong> seules les User Stories validées par le PO entrent dans l’incrément simulé. Une User Story prête à valider, refusée ou encore en travail reste un élément à examiner ; elle ne compte ni dans la valeur métier validée ni dans les Story Points validés tant que le PO ne l’a pas acceptée.</p>
                <p>La <strong>rétrospective collective</strong> est un texte partagé, enregistré par les membres de l’équipe : points forts, difficultés, blocages et améliorations. Elle porte sur la manière de travailler, pas sur la valeur livrée. Chaque contribution est conservée dans l’historique de l’équipe. Le formateur peut la consulter et la modérer.</p>
            </section>

            <section>
                <h3>Événements, chat, annonces et rôles</h3>
                <p>Le serveur déclenche des événements : nouvelles demandes, incidents, contraintes RGPD, sécurité, absences ou dette technique. Un événement propose généralement deux options avec des conséquences différentes ; la décision est irréversible. N’importe quel membre peut répondre après concertation : lisez les deux options avant de cliquer.</p>
                <p>Le chat est privé à l’équipe, mais le formateur peut le consulter et modérer un contenu. Les annonces du formateur sont globales ou ciblées : lisez-les à chaque connexion.</p>
                <p>Pour un échange de rôles, proposez à un membre qui a un autre rôle. Un membre ne peut avoir qu’une seule proposition en attente ; elle expire après cinq minutes. L’acceptation est immédiate et les deux rôles permutent. Un échange entre deux membres du même rôle est refusé. L’équipe conserve toujours un PO, un Scrum Master et au moins un Developer : un échange qui casserait cette composition est refusé.</p>
            </section>

            <section class="help-tips">
                <h3>Trucs et astuces</h3>
                <ul>
                    <li><strong>Choisissez peu, terminez beaucoup :</strong> mieux vaut valider quelques User Stories que d’en commencer beaucoup sans les finir.</li>
                    <li><strong>Ne confondez pas complexité et durée :</strong> les Story Points mesurent la complexité ; les heures servent à estimer les tâches.</li>
                    <li><strong>Découpez en tâches courtes :</strong> une tâche de 0,5 à 2 h est souvent plus facile à suivre ; le maximum autorisé est 2 h.</li>
                    <li><strong>Limitez le travail en cours :</strong> une ou deux tâches actives par Developer ; finissez avant d’en commencer une autre.</li>
                    <li><strong>Un blocage doit être précis :</strong> dites ce qui manque et qui peut aider. « Ça ne marche pas » n’aide personne.</li>
                    <li><strong>Avant « Prête à valider » :</strong> relisez chaque critère d’acceptation et la Definition of Done. Seul le Developer qui a traité une tâche terminée de l’US peut déclarer la User Story prête. Le PO refusera ce qui n’est pas démontrable.</li>
                    <li><strong>Feedback client :</strong> avant de refuser une User Story, consultez le feedback client : son motif est affiché en infobulle dans le champ du motif de refus.</li>
                    <li><strong>Détail des tâches :</strong> dans le Kanban, le détail d’une tâche (description et critères d’acceptation de la User Story associée) est visible en infobulle sur le champ description du formulaire de modification.</li>
                    <li><strong>Validation technique :</strong> la commande attendue se trouve en tâtonnant ; tapez « help » pour lister les commandes, puis la commande contenant --help pour un premier indice. Au troisième indice, « solution » révèle la commande à copier-coller. Vous pouvez aussi saisir directement la bonne commande.</li>
                    <li><strong>Modification concurrente :</strong> si un autre Developer vient de modifier la même tâche, un avertissement vous invite à vous coordonner dans le chat.</li>
                    <li><strong>Notifications chat :</strong> après une réinitialisation de séance, les notifications se réinitialisent automatiquement ; aucune action n’est requise.</li>
                    <li><strong>Une tâche par User Story :</strong> une User Story ne peut recevoir qu’une seule tâche active. Pour en ajouter une nouvelle, l’ancienne doit être archivée.</li>
                    <li><strong>Terminé via Modifier :</strong> dans le Kanban, la colonne « Terminé » n'est pas proposée dans le menu déroulant en ligne ; utilisez le bouton « Modifier » pour déclencher le challenge technique.</li>
<li><strong>Statistiques :</strong> le bouton « Statistiques » du Kanban ouvre les statistiques des décisions du client par développeur (acceptations, refus, risques) pour suivre la qualité du travail ; ces données alimentent aussi le palmarès généré automatiquement à la fin de la simulation.</li>
                    <li><strong>PO :</strong> un refus doit être un conseil concret. <strong>SM :</strong> regardez le Kanban et les blocages, pas seulement le chat.</li>
                    <li><strong>Traitez les événements rapidement :</strong> ils peuvent réduire la capacité ou changer les priorités.</li>
                    <li><strong>Ne changez pas de rôle au dernier moment :</strong> une proposition dure cinq minutes et peut perturber l’équipe.</li>
                    <li><strong>Si un bouton manque :</strong> vérifiez votre rôle, l’état de la User Story ou la phase. En pause ou à la fin, les modifications des équipes sont bloquées. Le serveur est l’autorité : il contrôle les droits, valide les actions, déclenche les événements et conserve l’historique ; lisez les messages d’erreur, ne doublez pas les clics et actualisez la page si nécessaire.</li>
                    <li><strong>Export JSON :</strong> à la fin de la simulation, le formateur peut exporter le bilan et l’historique au format JSON depuis l’onglet Bilan.</li>
                </ul>
            </section>

            <section>
                <h3>Horloge, pause et fin</h3>
                <p>La simulation démarre automatiquement à <?= e(SIM_START_TIME) ?> et se termine à <?= e(SIM_END_TIME) ?>, quelle que soit l’horloge de votre navigateur. Le formateur peut suspendre puis reprendre la simulation, mais ne peut ni avancer le démarrage ni prolonger la fin. Pendant une pause, la consultation reste possible mais les modifications des équipes sont bloquées ; le chronomètre continue.</p>
                <p>À la fin, les modifications des équipes sont bloquées. Le bilan compare les trois équipes sans classement ni note globale : il sert de support au débriefing.</p>
            </section>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-primary" data-close-modal="help-modal">J’ai compris</button>
        </div>
    </div>
</div>

<!-- Aide contextuelle d'un outil étudiant : le contenu est injecté par app.js. -->
<div class="modal" id="tool-help-modal" hidden>
    <div class="modal-backdrop" data-close-modal="tool-help-modal"></div>
    <div class="modal-panel modal-panel-wide" role="dialog" aria-modal="true" aria-labelledby="tool-help-modal-title">
        <div class="modal-header">
            <h2 id="tool-help-modal-title">Aide de l’outil</h2>
            <button type="button" class="icon-button" data-close-modal="tool-help-modal" aria-label="Fermer">×</button>
        </div>
        <div class="help-content" id="tool-help-content"></div>
        <div class="modal-actions">
            <button type="button" class="btn btn-primary" data-close-modal="tool-help-modal">J’ai compris</button>
        </div>
    </div>
</div>

<?php if ($isTeacher): ?>
    <!-- Aide réservée au formateur : supervision, annonces, modération et bilan. -->
    <div class="modal" id="teacher-help-modal" hidden>
        <div class="modal-backdrop" data-close-modal="teacher-help-modal"></div>
        <div class="modal-panel modal-panel-wide" role="dialog" aria-modal="true" aria-labelledby="teacher-help-modal-title">
            <div class="modal-header">
                <h2 id="teacher-help-modal-title">Aide formateur — supervision et modération</h2>
                <button type="button" class="icon-button" data-close-modal="teacher-help-modal" aria-label="Fermer">×</button>
            </div>
            <div class="help-content">
                <div class="help-callout">
                    <strong>Vous êtes formateur.</strong> Vous n’êtes pas joueur : votre rôle est de préparer, observer, faciliter et débriefer, sans faire le travail des équipes ni révéler les conséquences des événements.
                </div>

                <section>
                    <h3>Avant la séance</h3>
                    <ul>
                        <li>Vérifier la date, l’ouverture et les horaires de la séance.</li>
                        <li>Tester la connexion des comptes et l’affichage des trois équipes.</li>
                        <li>Vérifier que le dossier <code>datas/</code> est protégé et sauvegardé ; remplacer <code>RESET_WORD</code> dans <code>config.php</code> et ne pas le communiquer.</li>
                        <li>Utiliser <strong>Mon mot de passe</strong> pour choisir 8 chiffres difficiles à deviner, si possible sans rapport avec une date de naissance réelle.</li>
                        <li>Préparer une annonce d’accueil avec l’objectif, le temps disponible et les règles de communication.</li>
                    </ul>
                </section>

                <section>
                    <h3>Pendant la séance</h3>
                    <ul>
                        <li><strong>Vue globale :</strong> comparer les signaux des trois équipes — User Stories validées, valeur, Story Points, capacité, tâches terminées, blocages, événements, échanges et dernier message. Aucun classement n’est produit automatiquement.</li>
                        <li><strong>Ouvrir une équipe :</strong> lire le Sprint Goal, les rôles, le backlog, le Kanban, la Review, la rétrospective, le chat en lecture seule et l’historique des événements.</li>
                        <li><strong>Suspendre / Reprendre :</strong> la pause bloque les modifications des équipes, mais le chronomètre continue. Elle sert à clarifier une incompréhension collective, pas à faire le travail à la place des étudiants.</li>
                        <li><strong>Annonces :</strong> globales ou ciblées, 500 caractères maximum. Elles sont utiles pour le temps, une règle ou une clarification collective ; ne donnez pas la réponse aux événements.</li>
                        <li><strong>Observation :</strong> surveiller les blocages, les User Stories prêtes non validées, les événements non traités et les Sprint Goals flous. Poser des questions plutôt que corriger à leur place.</li>
                    </ul>
                </section>

                <section>
                    <h3>Modération</h3>
                    <ul>
                        <li>Modifier ou supprimer les textes libres saisis par les étudiants : chat ; titre, description et blocage d’une tâche ; commentaire de refus d’une User Story ; Sprint Goal et justification ; rétrospective collective. Les titres, descriptions et critères des User Stories, ainsi que les données produites par le moteur, ne sont pas modérables. Les annonces du formateur restent gérables dans leur vue dédiée.</li>
                        <li>La correction remplace le texte visible ; la suppression retire le contenu de l’affichage normal sans supprimer la tâche, la User Story ou l’annonce concernée. L’historique conserve l’ancien et le nouveau texte.</li>
                        <li>Une action critique demande une confirmation. Utilisez un texte factuel et pédagogique, sans données personnelles.</li>
                    </ul>
                </section>

                <section>
                    <h3>Après la séance</h3>
                    <ul>
                        <li><strong>Bilan :</strong> disponible à la fin. Il compare les indicateurs des trois équipes sans classement ni note globale et sert de support au débriefing.</li>
                        <li><strong>Historique :</strong> filtrer les entrées globales ou par équipe, vérifier les anciens et nouveaux textes des modérations.</li>
                        <li><strong>Nouvelle séance :</strong> possible uniquement hors simulation en cours. Elle demande le mot de remise à zéro et réinitialise backlog, tâches, événements, chats, annonces, historique et chronomètre. Sauvegarder <code>datas/</code> avant.</li>
                    </ul>
                </section>

                <section class="help-tips">
                    <h3>Trucs et astuces formateur</h3>
                    <ul>
                        <li>Annoncez les temps forts : démarrage, mi-parcours, approche de la validation finale.</li>
                        <li>Demandez à chaque équipe de formuler son Sprint Goal et de justifier ses choix, sans imposer le vôtre.</li>
                        <li>Ne validez pas à la place du PO : faites vérifier les critères et la Definition of Done.</li>
                        <li>Utilisez l’historique et le bilan pour le débrief : faits, décisions, blocages et adaptations.</li>
                        <li>Faites reformuler une décision par un étudiant : c’est souvent plus efficace qu’une correction magistrale.</li>
                        <li>En pause, demandez une synthèse de l’équipe avant de reprendre.</li>
                        <li>Ne projetez pas l’écran de modération : il peut contenir des échanges destinés à l’équipe.</li>
                    </ul>
                </section>

                <section>
                    <h3>Sécurité et confidentialité</h3>
                    <p>Ne communiquez jamais le mot de remise à zéro ni les identifiants. Les dates de naissance ne sont pas affichées dans l’application. Les contenus modérés restent tracés dans l’historique.</p>
                </section>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" data-close-modal="teacher-help-modal">Fermer</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="assets/app.js" defer></script>
</body>
</html>
