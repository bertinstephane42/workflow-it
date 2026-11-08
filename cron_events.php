<?php
/**
 * Déclencheur serveur des événements automatiques.
 *
 * Ce script est facultatif : il permet à une tâche planifiée LWS (cron)
 * de faire avancer les événements même lorsqu'aucun navigateur n'est
 * connecté. Il n'est jamais exécutable depuis le Web.
 *
 * Exemple de tâche planifiée (toutes les minutes) :
 *   php /chemin/vers/workflow-it/cron_events.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';

try {
    bootstrap_storage();
    trigger_events_if_due();
    echo "WorkFlow IT : tick événements terminé.\n";
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'WorkFlow IT cron : ' . $exception->getMessage() . "\n");
    exit(1);
}
