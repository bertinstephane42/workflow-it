<?php
/**
 * Règles de la simulation WorkFlow IT.
 *
 * Ce fichier regroupe le catalogue initial, les horaires, les équipes,
 * les événements, les actions autorisées, le bilan et la construction
 * de l'état envoyé au navigateur.
 */

declare(strict_types=1);

require_once __DIR__ . '/challenges.php';

// ===========================================================================
// Données initiales du produit
// ===========================================================================

/**
 * Product Backlog initial, source unique de vérité pour les trois équipes.
 */
function initial_product_backlog(): array
{
    return [
        [
            'id' => 'US01',
            'titre' => 'Se connecter à l’application',
            'description' => 'En tant que collaborateur, je souhaite me connecter à l’application afin d’accéder à mes services.',
            'criteres' => [
                'Le collaborateur peut se connecter avec ses identifiants.',
                'En cas d’échec, un message d’erreur générique s’affiche sans révéler si l’identifiant existe.',
                'La session reste active pendant la séance et se ferme à la déconnexion.',
            ],
            'estimation' => 3,
            'valeur' => 8,
            'priorite' => 'haute',
            'risques' => 'Risque de blocage si la gestion des sessions ou des mots de passe est mal comprise ; attention aux tentatives de force brute.',
        ],
        [
            'id' => 'US02',
            'titre' => 'Créer une demande informatique',
            'description' => 'En tant que collaborateur, je souhaite créer une demande informatique afin de signaler un problème ou un besoin.',
            'criteres' => [
                'Le formulaire exige un titre, une description et une catégorie.',
                'La demande créée apparaît dans la liste de son auteur avec un numéro unique.',
                'Un accusé de création s’affiche et confirme l’enregistrement.',
            ],
            'estimation' => 5,
            'valeur' => 13,
            'priorite' => 'haute',
            'risques' => 'Risque de perte ou de duplication de demandes en cas de soumission multiple ou de problème lors de l’enregistrement ; les champs doivent également être validés pour éviter les données incomplètes ou malveillantes.',
        ],
        [
            'id' => 'US03',
            'titre' => 'Catégoriser une demande',
            'description' => 'En tant que collaborateur, je souhaite catégoriser ma demande afin de faciliter son traitement.',
            'criteres' => [
                'Une liste de catégories prédéfinies est proposée.',
                'La catégorie choisie est enregistrée avec la demande.',
                'La catégorie reste visible dans la liste et le détail du suivi.',
            ],
            'estimation' => 3,
            'valeur' => 5,
            'priorite' => 'moyenne',
            'risques' => 'Risque de mauvaise catégorisation si les intitulés sont ambigus ou si les catégories ne correspondent pas aux besoins réels ; une modification ultérieure des catégories pourrait également rendre certaines demandes incohérentes.',
        ],
        [
            'id' => 'US04',
            'titre' => 'Consulter l’état de ses demandes',
            'description' => 'En tant que collaborateur, je souhaite consulter l’état de mes demandes afin de connaître leur avancement.',
            'criteres' => [
                'L’auteur voit uniquement ses demandes et leur statut dans son espace personnel.',
                'Le statut est mis à jour après traitement.',
                'Les techniciens et responsables accèdent aux demandes selon leurs droits.',
            ],
            'estimation' => 3,
            'valeur' => 8,
            'priorite' => 'moyenne',
            'risques' => 'Risque d’exposition de demandes appartenant à d’autres collaborateurs si les droits d’accès sont mal contrôlés ; risque de perte de confiance si les statuts affichés ne sont pas actualisés correctement.',
        ],
        [
            'id' => 'US05',
            'titre' => 'Consulter les demandes en attente',
            'description' => 'En tant que technicien, je souhaite consulter les demandes en attente afin de pouvoir les traiter.',
            'criteres' => [
                'Le technicien voit les demandes non assignées.',
                'La liste est triée par priorité puis par date de création.',
                'Le détail d’une demande est accessible depuis la liste.',
            ],
            'estimation' => 5,
            'valeur' => 13,
            'priorite' => 'haute',
            'risques' => 'Risque de surcharge ou de lenteur si le nombre de demandes augmente ; une erreur dans les filtres ou le tri pourrait masquer des demandes prioritaires et retarder leur prise en charge.',
        ],
        [
            'id' => 'US06',
            'titre' => 'Modifier le statut d’une demande',
            'description' => 'En tant que technicien, je souhaite modifier le statut d’une demande afin de suivre son traitement.',
            'criteres' => [
                'Le technicien peut choisir un nouveau statut parmi les statuts autorisés.',
                'Le changement est enregistré avec sa date et son auteur.',
                'L’auteur peut voir le nouveau statut dans le suivi.',
            ],
            'estimation' => 3,
            'valeur' => 3,
            'priorite' => 'moyenne',
            'risques' => 'Risque de progression incohérente si les transitions entre statuts ne sont pas contrôlées ; risque de perte de traçabilité si l’auteur ou la date du changement ne sont pas correctement enregistrés.',
        ],
        [
            'id' => 'US07',
            'titre' => 'Affecter une demande à un technicien',
            'description' => 'En tant que responsable informatique, je souhaite affecter une demande à un technicien afin de répartir le travail.',
            'criteres' => [
                'Le responsable choisit un technicien actif dans la liste de l’équipe.',
                'L’affectation et le nom du technicien sont visibles dans la demande.',
                'Le technicien retrouve la demande dans sa liste.',
            ],
            'estimation' => 3,
            'valeur' => 5,
            'priorite' => 'moyenne',
            'risques' => 'Risque de mauvaise répartition de la charge si les affectations ne tiennent pas compte de la disponibilité des techniciens ; une affectation à un technicien inactif pourrait empêcher le traitement de la demande.',
        ],
        [
            'id' => 'US08',
            'titre' => 'Recevoir une notification de changement',
            'description' => 'En tant que collaborateur, je souhaite recevoir une notification lors d’un changement de statut afin d’être informé.',
            'criteres' => [
                'Une notification est créée dans l’application à chaque changement de statut.',
                'La notification indique la demande concernée et le nouveau statut.',
                'Le collaborateur peut consulter et marquer ses notifications comme lues.',
            ],
            'estimation' => 5,
            'valeur' => 8,
            'priorite' => 'moyenne',
            'risques' => 'Risque de notifications manquantes ou dupliquées si leur création n’est pas synchronisée avec le changement de statut ; risque d’envoyer une information à un mauvais destinataire.',
        ],
        [
            'id' => 'US09',
            'titre' => 'Commenter une demande',
            'description' => 'En tant que collaborateur, je souhaite ajouter un commentaire à une demande afin de compléter les informations.',
            'criteres' => [
                'Un commentaire non vide peut être ajouté à une demande.',
                'Le commentaire affiche son auteur et sa date.',
                'Les commentaires sont visibles dans l’historique de la demande.',
            ],
            'estimation' => 3,
            'valeur' => 2,
            'priorite' => 'basse',
            'risques' => 'Risque de divulgation d’informations confidentielles dans les commentaires ; les contenus libres doivent être protégés contre les injections de code et leur auteur doit être correctement identifié.',
        ],
        [
            'id' => 'US10',
            'titre' => 'Visualiser les demandes urgentes',
            'description' => 'En tant que responsable informatique, je souhaite visualiser les demandes urgentes afin de traiter les situations prioritaires.',
            'criteres' => [
                'Les demandes urgentes portent un indicateur visible dans la liste et le détail.',
                'Un filtre permet d’afficher uniquement les demandes urgentes.',
                'Le responsable peut ouvrir une demande urgente depuis la liste ou le filtre.',
            ],
            'estimation' => 5,
            'valeur' => 13,
            'priorite' => 'haute',
            'risques' => 'Risque de masquer un incident critique si l’indicateur ou le filtre d’urgence est incorrect ; une mauvaise identification des demandes urgentes peut également détourner les ressources des véritables priorités.',
        ],
        [
            'id' => 'US11',
            'titre' => 'Filtrer les demandes',
            'description' => 'En tant que responsable informatique, je souhaite filtrer les demandes par service ou catégorie afin de faciliter leur suivi.',
            'criteres' => [
                'Un filtre par service est disponible.',
                'Un filtre par catégorie est disponible.',
                'Les filtres par service et par catégorie peuvent être combinés.',
            ],
            'estimation' => 3,
            'valeur' => 5,
            'priorite' => 'moyenne',
            'risques' => 'Risque de résultats incomplets ou erronés si les filtres combinés appliquent une logique incorrecte ; des valeurs incohérentes entre services et catégories pourraient fausser l’analyse.',
        ],
        [
            'id' => 'US12',
            'titre' => 'Signaler une demande urgente',
            'description' => 'En tant que collaborateur, je souhaite signaler une demande urgente afin d’attirer l’attention du service informatique.',
            'criteres' => [
                'Le collaborateur peut marquer une demande comme urgente.',
                'L’urgence et sa justification sont visibles par le service informatique.',
                'Une justification non vide est exigée avant l’enregistrement.',
            ],
            'estimation' => 2,
            'valeur' => 8,
            'priorite' => 'moyenne',
            'risques' => 'Risque de multiplication des fausses urgences si les collaborateurs utilisent ce signalement de manière abusive ; une justification insuffisante peut compliquer l’évaluation réelle de la priorité.',
        ],
        [
            'id' => 'US13',
            'titre' => 'Consulter l’historique des interventions',
            'description' => 'En tant que technicien, je souhaite consulter l’historique des interventions afin de connaître les actions déjà réalisées.',
            'criteres' => [
                'L’historique liste les interventions d’une demande dans l’ordre chronologique.',
                'Chaque entrée affiche la date, l’auteur et l’action réalisée.',
                'L’historique est en lecture seule pour les utilisateurs.',
            ],
            'estimation' => 5,
            'valeur' => 2,
            'priorite' => 'basse',
            'risques' => 'Risque de perte de traçabilité si certaines actions ne sont pas historisées ou si les dates sont incorrectes ; risque d’altération des preuves de traitement si l’historique n’est pas réellement protégé en écriture.',
        ],
        [
            'id' => 'US14',
            'titre' => 'Consulter les statistiques d’activité',
            'description' => 'En tant que responsable informatique, je souhaite consulter des statistiques d’activité afin de suivre l’activité du service informatique.',
            'criteres' => [
                'Le responsable voit le nombre de demandes par statut.',
                'Le responsable voit le volume de demandes par catégorie.',
                'Les indicateurs sont calculés entre une date de début et une date de fin choisies.',
            ],
            'estimation' => 8,
            'valeur' => 5,
            'priorite' => 'basse',
            'risques' => 'Risque d’indicateurs trompeurs si les données sont incomplètes, mal agrégées ou si les périodes sont mal interprétées ; les volumes de données peuvent également dégrader les temps de calcul.',
        ],
        [
            'id' => 'US22',
            'titre' => 'Rechercher une demande',
            'description' => 'En tant que technicien, je souhaite rechercher une demande par numéro ou par mot-clé afin de retrouver rapidement un dossier parmi les demandes de l’entreprise.',
            'criteres' => [
                'Une recherche par numéro de demande ou par mot-clé du titre est disponible.',
                'Les résultats restent limités aux demandes que l’utilisateur est autorisé à consulter.',
                'La recherche peut être combinée avec le filtre par service ou par catégorie.',
            ],
            'estimation' => 3,
            'valeur' => 8,
            'priorite' => 'moyenne',
            'risques' => 'Risque de résultats manquants si la recherche ne gère pas correctement les accents, les caractères spéciaux ou les mots partiels ; risque de divulgation de demandes si les droits d’accès ne sont pas appliqués avant le retour des résultats.',
        ],
        [
            'id' => 'US23',
            'titre' => 'Planifier une intervention',
            'description' => 'En tant que technicien, je souhaite planifier une intervention afin d’organiser le passage sur site et d’informer le collaborateur.',
            'criteres' => [
                'Une date et un créneau d’intervention peuvent être enregistrés sur une demande.',
                'Le planning empêche deux interventions qui se chevauchent pour un même technicien.',
                'La date et le créneau planifiés sont visibles par le collaborateur.',
            ],
            'estimation' => 5,
            'valeur' => 8,
            'priorite' => 'moyenne',
            'risques' => 'Risque de conflits de planning si deux interventions sont réservées simultanément pour un même technicien ; une mauvaise gestion des dates, créneaux ou disponibilités peut entraîner des rendez-vous impossibles à honorer.',
        ],
    ];
}

/**
 * Users Stories ajoutées par les événements.
 */
/**
 * User Stories ajoutées par les événements.
 */
function event_stories(): array
{
    return [
        'US15' => [
            'id' => 'US15',
            'titre' => 'Joindre un document à une demande',
            'description' => 'En tant que collaborateur, je souhaite joindre une capture d’écran ou un document à ma demande afin de mieux décrire mon besoin.',
            'criteres' => [
                'Un fichier d’un format autorisé peut être joint à une demande.',
                'Le fichier joint est visible par le technicien.',
                'Un message explicite signale un format ou une taille refusés.',
            ],
            'estimation' => 5,
            'valeur' => 8,
            'risques' => 'Risque de dépôt de fichiers malveillants ou contenant des données confidentielles ; une validation insuffisante du format, de la taille ou du nom du fichier peut compromettre la sécurité et le stockage de l’application.',
        ],
        'US16' => [
            'id' => 'US16',
            'titre' => 'Recevoir une alerte en temps réel',
            'description' => 'En tant que technicien, je souhaite recevoir une alerte en temps réel afin de réagir rapidement aux incidents majeurs.',
            'criteres' => [
                'Une alerte apparaît dans l’application lors d’un incident majeur.',
                'L’alerte indique le service concerné et l’heure de l’incident.',
                'Le technicien peut acquitter l’alerte.',
            ],
            'estimation' => 5,
            'valeur' => 13,
            'risques' => 'Risque de retard ou de perte d’alerte si la synchronisation échoue ou si les notifications sont trop nombreuses ; une alerte non acquittée ou acquittée à tort peut retarder la prise en charge d’un incident majeur.',
        ],
        'US17' => [
            'id' => 'US17',
            'titre' => 'Supprimer ses données personnelles',
            'description' => 'En tant que collaborateur, je souhaite demander la suppression de mes données personnelles afin de respecter le RGPD.',
            'criteres' => [
                'Une demande de suppression peut être créée depuis l’espace du collaborateur.',
                'La demande est tracée dans l’historique avec sa date.',
                'Un délai de traitement est affiché au collaborateur.',
            ],
            'estimation' => 5,
            'valeur' => 5,
            'risques' => 'Risque de suppression prématurée de données nécessaires au traitement d’une demande ou à une obligation légale ; une suppression incomplète ou non tracée peut entraîner une non-conformité au RGPD.',
        ],
        'US18' => [
            'id' => 'US18',
            'titre' => 'Renforcer l’authentification',
            'description' => 'En tant qu’administrateur, je souhaite renforcer l’authentification afin de protéger les données du service informatique.',
            'criteres' => [
                'Un second facteur peut être activé sur le compte.',
                'Un échec du second facteur bloque la connexion.',
                'L’activation et les échecs sont tracés dans l’historique.',
            ],
            'estimation' => 8,
            'valeur' => 13,
            'risques' => 'Risque de verrouillage de comptes légitimes en cas de perte du second facteur ou de dysfonctionnement du mécanisme ; une implémentation insuffisamment sécurisée pourrait également permettre de contourner cette protection.',
        ],
        'US19' => [
            'id' => 'US19',
            'titre' => 'Suivre les demandes par service',
            'description' => 'En tant que responsable informatique, je souhaite suivre les demandes par service afin d’identifier les besoins récurrents.',
            'criteres' => [
                'Un indicateur par service est disponible pour une période choisie.',
                'La période d’analyse est paramétrable par le responsable.',
                'Les données affichées peuvent être exportées.',
            ],
            'estimation' => 3,
            'valeur' => 5,
            'risques' => 'Risque de comparaison erronée si les demandes sont rattachées au mauvais service ou si les périodes ne sont pas homogènes ; l’export peut également exposer des données qui ne devraient pas être diffusées.',
        ],
        'US20' => [
            'id' => 'US20',
            'titre' => 'Prendre en compte les sites distants',
            'description' => 'En tant que technicien, je souhaite prendre en compte les sites distants afin d’organiser les interventions sur les trois sites.',
            'criteres' => [
                'Le site de l’intervention est enregistré avec la demande.',
                'Les interventions peuvent être filtrées par site.',
                'La charge de travail par site est visible.',
            ],
            'estimation' => 5,
            'valeur' => 8,
            'risques' => 'Risque d’affectation incorrecte si le site d’une demande est absent ou erroné ; les contraintes de déplacement et les différences de charge entre sites peuvent rendre la planification difficile.',
        ],
        'US21' => [
            'id' => 'US21',
            'titre' => 'Proposer une assistance contextualisée',
            'description' => 'En tant que collaborateur, je souhaite une assistance contextualisée afin de trouver une solution avant de créer une demande.',
            'criteres' => [
                'Une aide est proposée selon la catégorie sélectionnée.',
                'L’aide est accessible depuis le formulaire de création.',
                'Le collaborateur peut créer une demande après avoir consulté l’aide.',
            ],
            'estimation' => 3,
            'valeur' => 3,
            'risques' => 'Risque de proposer une aide inadaptée ou obsolète si les contenus ne sont pas régulièrement mis à jour ; une assistance peu pertinente peut décourager les utilisateurs et ne pas réduire le volume de demandes.',
        ],
    ];
}

/**
 * Definition of Done commune.
 */
function definition_of_done(): array
{
    return [
        'Les critères d’acceptation de la User Story sont satisfaits.',
        'Toutes les tâches actives associées sont terminées ; les tâches archivées ne comptent pas.',
        'Aucun défaut bloquant connu ne subsiste.',
        'Le travail peut être intégré à l’incrément sans dépendance bloquante non traitée.',
        'L’équipe peut démontrer le résultat à partir des critères d’acceptation.',
    ];
}

/**
 * Sprint Goal initial, identique pour les trois équipes.
 */
function initial_sprint_goal(): string
{
    return 'Permettre aux collaborateurs de déclarer leurs demandes informatiques et au service informatique de les prendre en charge et de suivre leur traitement.';
}

// ===========================================================================
// Horaires et session
// ===========================================================================

/**
 * Calcule les horaires de la prochaine séance.
 * La séance du jour est utilisée avant sa fin ; après 14 h 35, la suivante
 * est programmée au lendemain (pratique pour préparer une séance).
 */
function session_schedule_for_date(string $day): array
{
    $open = strtotime($day . ' ' . SESSION_OPEN_TIME);
    $start = strtotime($day . ' ' . SIM_START_TIME);
    $end = strtotime($day . ' ' . SIM_END_TIME);
    if ($open === false || $start === false || $end === false) {
        throw new RuntimeException('Horaires de séance invalides dans config.php.');
    }
    if ($start >= $end) {
        throw new RuntimeException('Horaires de séance invalides dans config.php.');
    }
    return [
        'date' => $day,
        'opened_at' => $open,
        'sim_start_at' => $start,
        'sim_end_at' => $end,
    ];
}

function next_session_schedule(?int $now = null): array
{
    $now ??= time();
    $date = SESSION_DATE_OVERRIDE !== '' ? SESSION_DATE_OVERRIDE : date('Y-m-d', $now);
    $schedule = session_schedule_for_date($date);
    if (SESSION_DATE_OVERRIDE === '' && $now >= $schedule['sim_end_at']) {
        $schedule = session_schedule_for_date(date('Y-m-d', strtotime('+1 day', $now)));
    }
    return $schedule;
}

/**
 * Calcule l’horaire effectif d’une séance à partir de la configuration,
 * pour la date de la séance en cours. Un changement des heures dans config.php
 * est donc pris en compte immédiatement, même pendant une session.
 */
function session_effective_schedule(array $session, ?int $now = null): array
{
    $now ??= time();
    $date = (string) ($session['date'] ?? '');
    if ($date === '') {
        $date = date('Y-m-d', $now);
    }
    return session_schedule_for_date($date);
}

/**
 * Détermine la phase courante à partir de l'heure serveur.
 */
function session_phase(array $session, ?int $now = null): string
{
    $now ??= time();
    if (!empty($session['stopped_at'])) {
        return 'finished';
    }
    $schedule = session_effective_schedule($session, $now);
    $opened = (int) $schedule['opened_at'];
    $start = (int) $schedule['sim_start_at'];
    $end = (int) $schedule['sim_end_at'];

    if ($now < $opened) {
        return 'closed';
    }
    if ($now < $start) {
        return 'discovery';
    }
    if ($now >= $end) {
        return 'finished';
    }
    return !empty($session['paused']) ? 'paused' : 'running';
}

/**
 * Libellé lisible d'une phase.
 */
function phase_label(string $phase): string
{
    return match ($phase) {
        'closed' => 'Avant ouverture',
        'discovery' => 'Découverte',
        'running' => 'Simulation en cours',
        'paused' => 'Simulation en pause',
        'finished' => 'Simulation terminée',
        default => 'État inconnu',
    };
}

/**
 * Version publique de la session : aucune donnée sensible.
 */
function session_public(array $session, string $phase): array
{
    $nowMs = now_ms();
    $schedule = session_effective_schedule($session, (int) floor($nowMs / 1000));
    $startMs = ((int) $schedule['sim_start_at']) * 1000;
    $endMs = ((int) $schedule['sim_end_at']) * 1000;
    $remaining = max(0, (int) $schedule['sim_end_at'] - time());
    $duration = max(1, (int) $schedule['sim_end_at'] - (int) $schedule['sim_start_at']);

    return [
        'date' => (string) $schedule['date'],
        'generation' => (string) ($session['generation'] ?? $session['reset_at'] ?? $session['date']),
        'phase' => $phase,
        'phase_label' => phase_label($phase),
        'opened_at_ms' => ((int) $schedule['opened_at']) * 1000,
        'sim_start_ms' => $startMs,
        'sim_end_ms' => $endMs,
        'remaining_seconds' => $remaining,
        'progress' => min(100, max(0, (int) round((1 - $remaining / $duration) * 100))),
        'paused' => !empty($session['paused']),
        'can_students_modify' => $phase === 'running',
        'schedule' => [
            'open' => SESSION_OPEN_TIME,
            'start' => SIM_START_TIME,
            'end' => SIM_END_TIME,
        ],
        'server_time_ms' => $nowMs,
    ];
}

/**
 * Charge le fichier session.
 */
function current_session(): array
{
    $session = json_read(SESSION_FILE, []);
    if (!isset($session['opened_at'], $session['sim_start_at'], $session['sim_end_at'])) {
        throw new RuntimeException('Fichier session.json invalide. Une remise à zéro est nécessaire.');
    }
    return $session;
}

/**
 * Vérifie que la simulation accepte les modifications des joueurs.
 */
function assert_simulation_running(string $phase): void
{
    if ($phase === 'running') {
        return;
    }
    $message = match ($phase) {
        'closed' => 'La séance n’est pas encore ouverte.',
        'discovery' => 'La simulation commence à l’heure prévue. La consultation et l’aide restent disponibles.',
        'paused' => 'La simulation est temporairement suspendue par le formateur.',
        'finished' => 'La simulation est terminée : les modifications sont bloquées.',
        default => 'La simulation n’accepte pas de modification pour le moment.',
    };
    throw new AppError($message, 409);
}

/**
 * Initialise tous les fichiers de données pour une nouvelle séance.
 * L'appelant doit détenir le verrou global (with_app_lock).
 */
function reset_all(string $author = 'systeme', ?string $day = null): void
{
    ensure_data_dir();
    $schedule = $day !== null ? session_schedule_for_date($day) : next_session_schedule();
    $users = load_users();

    // Session
    $session = [
        'revision' => 1,
        'generation' => bin2hex(random_bytes(8)),
        'date' => $schedule['date'],
        'opened_at' => $schedule['opened_at'],
        'sim_start_at' => $schedule['sim_start_at'],
        'sim_end_at' => $schedule['sim_end_at'],
        'paused' => false,
        'paused_at' => null,
        'paused_by' => null,
        'announcements' => [],
        'next_announcement_id' => 1,
        'pedagogical_messages' => [],
        'reset_at' => iso_now(),
        'updated_at' => iso_now(),
    ];

    // Équipes
    $teams = [];
    for ($teamId = 1; $teamId <= 3; $teamId++) {
        $members = [];
        foreach ($users as $user) {
            if ($user['team'] === $teamId) {
                $members[] = [
                    'login' => $user['login'],
                    'role' => $user['initial_role'],
                ];
            }
        }
        if ($members === []) {
            throw new RuntimeException('Aucun étudiant dans l’équipe ' . $teamId . '.');
        }
        validate_team_composition($members);
        $teams[$teamId] = initial_team_data($teamId, $members);
    }

    $events = initial_events_data($schedule);

    json_write_atomic(SESSION_FILE, $session);
    foreach ($teams as $teamId => $team) {
        json_write_atomic(team_file_path($teamId), $team);
    }
    json_write_atomic(EVENTS_FILE, $events);
    json_write_atomic(HISTORY_FILE, ['entries' => [], 'revision' => 0, 'updated_at' => iso_now()]);
    json_write_atomic(CHALLENGES_FILE, ['entries' => [], 'revision' => 0, 'updated_at' => iso_now()]);
    json_write_atomic(LOGIN_ATTEMPTS_FILE, ['users' => []]);
    json_write_atomic(RATE_LIMIT_FILE, ['second' => 0, 'count' => 0]);
    json_write_atomic(PASSWORD_REQUESTS_FILE, password_requests_default());

    history_add(0, $author, 'reset', 'Initialisation d’une nouvelle séance (' . $schedule['date'] . ').', [
        'date' => $schedule['date'],
        'sim_start' => iso_at($schedule['sim_start_at']),
        'sim_end' => iso_at($schedule['sim_end_at']),
    ]);
}

/**
 * Prépare les fichiers manquants au premier lancement.
 */
function bootstrap_storage(): void
{
    ensure_data_dir();
    ensure_users_password_hashes();

    if (!is_file(SESSION_FILE)) {
        with_app_lock(static function (): void {
            reset_all('systeme');
        });
        return;
    }

    current_session(); // valide le fichier

    if (!is_file(EVENTS_FILE)) {
        with_app_lock(static function (): void {
            $session = current_session();
            $schedule = [
                'date' => $session['date'],
                'opened_at' => (int) $session['opened_at'],
                'sim_start_at' => (int) $session['sim_start_at'],
                'sim_end_at' => (int) $session['sim_end_at'],
            ];
            json_write_atomic(EVENTS_FILE, initial_events_data($schedule));
        });
    }

    if (!is_file(HISTORY_FILE)) {
        with_app_lock(static function (): void {
            json_write_atomic(HISTORY_FILE, ['entries' => [], 'revision' => 0, 'updated_at' => iso_now()]);
            history_add(0, 'systeme', 'init', 'Création du journal d’activité.');
        });
    }

    for ($teamId = 1; $teamId <= 3; $teamId++) {
        if (!is_file(team_file_path($teamId))) {
            throw new RuntimeException(
                'Le fichier de l’équipe ' . $teamId . ' est manquant. Une remise à zéro est nécessaire.'
            );
        }
        assert_team_data(load_team($teamId));
    }
}

// ===========================================================================
// Équipes, rôles et capacité
// ===========================================================================

/**
 * Chemin du fichier d'une équipe.
 */
function team_file_path(int $teamId): string
{
    if ($teamId < 1 || $teamId > 3) {
        throw new AppError('Équipe invalide.', 403);
    }
    return DATA_DIR . '/team_' . $teamId . '.json';
}

/**
 * Valide la structure minimale d'une équipe.
 */
function assert_team_data(array $team): void
{
    foreach (['members', 'stories', 'tasks', 'messages', 'swaps'] as $key) {
        if (!isset($team[$key]) || !is_array($team[$key])) {
            throw new RuntimeException('Données d’équipe incomplètes (' . $key . ').');
        }
    }
    if (!isset($team['sprint_goal']) || !is_string($team['sprint_goal'])) {
        throw new RuntimeException('Sprint Goal absent des données d’équipe.');
    }
}

/**
 * Charge une équipe.
 */
function load_team(int $teamId): array
{
    $team = json_read(team_file_path($teamId), []);
    assert_team_data($team);
    return $team;
}

/**
 * Construit les données initiales d'une équipe.
 */
function initial_team_data(int $teamId, array $members): array
{
    $stories = [];
    foreach (initial_product_backlog() as $story) {
        $story['selectionnee'] = false;
        $story['etat'] = 'a_faire';
        $story['risques'] = $story['risques'] ?? 'Aucun risque majeur identifié à ce stade.';
        $story['refus_commentaire'] = null;
        $story['validee_at'] = null;
        $story['refusee_at'] = null;
        $stories[] = $story;
    }

    return [
        'team' => $teamId,
        'sprint_goal' => initial_sprint_goal(),
        'sprint_goal_justification' => '',
        'sprint_goal_updated_by' => null,
        'sprint_goal_updated_at' => null,
        'sprint_goal_moderation' => [],
        'retrospective' => '',
        'retrospective_updated_by' => null,
        'retrospective_updated_at' => null,
        'retrospective_history' => [],
        'retrospective_moderation' => [],
        'members' => $members,
        'capacite_reduction' => 0,
        'stories' => $stories,
        'tasks' => [],
        'messages' => [],
        'swaps' => [],
        'client_decisions' => [],
        'next_task_number' => 1,
        'next_message_number' => 1,
        'revision' => 1,
        'initialized_at' => iso_now(),
        'updated_at' => iso_now(),
    ];
}

/**
 * Vérifie la composition : un PO, un Scrum Master, au moins un Developer.
 */
function validate_team_composition(array $members): void
{
    $counts = ['PO' => 0, 'SCRUM_MASTER' => 0, 'DEVELOPER' => 0];
    foreach ($members as $member) {
        $role = $member['role'] ?? '';
        if (!isset($counts[$role])) {
            throw new RuntimeException('Rôle invalide dans une équipe : ' . $role);
        }
        $counts[$role]++;
    }
    if ($counts['PO'] !== 1 || $counts['SCRUM_MASTER'] !== 1 || $counts['DEVELOPER'] < 1) {
        throw new RuntimeException(
            'Chaque équipe doit conserver exactement un PO, un Scrum Master et au moins un Developer.'
        );
    }
}

/**
 * Retourne un membre d'équipe par login.
 */
function team_member(array $team, string $login): ?array
{
    foreach ($team['members'] as $member) {
        if (($member['login'] ?? '') === $login) {
            return $member;
        }
    }
    return null;
}

/**
 * Rôle courant d'un membre.
 */
function team_role(array $team, string $login): ?string
{
    $member = team_member($team, $login);
    return $member !== null ? (string) $member['role'] : null;
}

/**
 * Libellé d'un rôle.
 */
function role_label(string $role): string
{
    return match ($role) {
        'PO' => 'Product Owner',
        'SCRUM_MASTER' => 'Scrum Master',
        'DEVELOPER' => 'Developer',
        'FORMATEUR' => 'Formateur',
        default => $role,
    };
}

/**
 * Informations de capacité d'une équipe.
 */
function capacity_info(array $team): array
{
    $developers = 0;
    foreach ($team['members'] as $member) {
        if (($member['role'] ?? '') === 'DEVELOPER') {
            $developers++;
        }
    }
    $initial = $developers * DEVELOPER_CAPACITY_HOURS;
    $reduction = max(0, (float) ($team['capacite_reduction'] ?? 0));
    $available = max(0, $initial - $reduction);

    $charge = 0.0;
    $doneHours = 0.0;
    $remainingWork = 0.0;
    $activeTasks = 0;
    $doneTasks = 0;
    $blockedTasks = 0;
    foreach ($team['tasks'] as $task) {
        if (!empty($task['archived'])) {
            continue;
        }
        $activeTasks++;
        $hours = (float) ($task['heures'] ?? 0);
        $charge += $hours;
        if (($task['etat'] ?? '') === 'termine') {
            $doneHours += $hours;
            $doneTasks++;
        } else {
            $remainingWork += $hours;
            if (($task['etat'] ?? '') === 'bloque') {
                $blockedTasks++;
            }
        }
    }

    return [
        'initial' => $initial,
        'reduction' => $reduction,
        'available' => $available,
        'charge' => round($charge, 2),
        'done_hours' => round($doneHours, 2),
        'remaining_work' => round($remainingWork, 2),
        'remaining_capacity' => round(max(0, $available - $doneHours), 2),
        'developers' => $developers,
        'active_tasks' => $activeTasks,
        'done_tasks' => $doneTasks,
        'blocked_tasks' => $blockedTasks,
    ];
}

/**
 * Statistiques simples d'une équipe.
 */
function team_stats(array $team): array
{
    $selected = 0;
    $validated = 0;
    $ready = 0;
    $refused = 0;
    $validatedValue = 0;
    $validatedPoints = 0;
    $selectedPoints = 0;
    foreach ($team['stories'] as $story) {
        if (!empty($story['selectionnee'])) {
            $selected++;
            $selectedPoints += (int) $story['estimation'];
        }
        $state = $story['etat'] ?? 'a_faire';
        if ($state === 'validee') {
            $validated++;
            $validatedValue += (int) $story['valeur'];
            $validatedPoints += (int) $story['estimation'];
        } elseif ($state === 'a_valider') {
            $ready++;
        } elseif ($state === 'refusee') {
            $refused++;
        }
    }

    return [
        'us_total' => count($team['stories']),
        'us_selectionnees' => $selected,
        'us_validees' => $validated,
        'us_pretes' => $ready,
        'us_refusees' => $refused,
        'valeur_validee' => $validatedValue,
        'points_valides' => $validatedPoints,
        'points_selectionnes' => $selectedPoints,
        'messages' => count($team['messages']),
    ];
}

// ===========================================================================
// Événements automatiques
// ===========================================================================

/**
 * Catalogue des quatorze événements. Les effets sont appliqués par le serveur.
 */
function event_catalog(): array
{
    return [
        'evt_dsi' => [
            'titre' => 'Nouvelle demande de la DSI',
            'description' => 'La DSI demande que les collaborateurs puissent joindre une capture d’écran ou un document à leur demande informatique.',
            'options' => [
                [
                    'label' => 'Intégrer la fonctionnalité au Sprint',
                    'resultat' => 'La User Story de pièce jointe est ajoutée en priorité haute.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US15', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
                [
                    'label' => 'La placer au backlog sans urgence',
                    'resultat' => 'La User Story de pièce jointe est ajoutée en bas du backlog.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US15', 'priorite' => 'basse', 'top' => false],
                    ],
                ],
            ],
        ],
        'evt_incident' => [
            'titre' => 'Incident majeur',
            'description' => 'Une panne de messagerie provoque un afflux de demandes. La visibilité sur les urgences et les alertes devient critique.',
            'options' => [
                [
                    'label' => 'Prioriser la visualisation des urgences',
                    'resultat' => 'La User Story US10 passe en priorité haute et remonte en tête du backlog.',
                    'effets' => [
                        ['type' => 'set_priority', 'story' => 'US10', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
                [
                    'label' => 'Ajouter une alerte temps réel',
                    'resultat' => 'Une User Story d’alerte temps réel est ajoutée au backlog.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US16', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
            ],
        ],
        'evt_priorite' => [
            'titre' => 'Changement de priorité',
            'description' => 'Le responsable informatique souhaite rendre immédiatement visible la répartition des demandes entre les techniciens.',
            'options' => [
                [
                    'label' => 'Prioriser l’affectation des demandes',
                    'resultat' => 'US07 remonte en tête du backlog.',
                    'effets' => [
                        ['type' => 'set_priority', 'story' => 'US07', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
                [
                    'label' => 'Prioriser la file d’attente des demandes',
                    'resultat' => 'US05 remonte en tête du backlog.',
                    'effets' => [
                        ['type' => 'set_priority', 'story' => 'US05', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
            ],
        ],
        'evt_rgpd' => [
            'titre' => 'Contrainte RGPD',
            'description' => 'La direction juridique impose une nouvelle exigence : les collaborateurs doivent pouvoir demander la suppression de leurs données personnelles.',
            'options' => [
                [
                    'label' => 'Prévoir une User Story dédiée',
                    'resultat' => 'La User Story RGPD est ajoutée en priorité haute.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US17', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
                [
                    'label' => 'Limiter les données collectées',
                    'resultat' => 'Une tâche de revue RGPD de 2 heures est ajoutée au Sprint.',
                    'effets' => [
                        ['type' => 'add_task', 'title' => 'Revue RGPD des écrans de saisie', 'hours' => 2, 'story' => null],
                    ],
                ],
            ],
        ],
        'evt_securite' => [
            'titre' => 'Problème de sécurité',
            'description' => 'Une vulnérabilité est identifiée dans le mécanisme d’authentification des collaborateurs.',
            'options' => [
                [
                    'label' => 'Auditer et corriger immédiatement',
                    'resultat' => 'US01 est réouverte et une tâche d’audit de 2 heures est ajoutée.',
                    'effets' => [
                        ['type' => 'select_story', 'story' => 'US01'],
                        ['type' => 'reopen_story', 'story' => 'US01'],
                        ['type' => 'add_task', 'title' => 'Audit de sécurité de l’authentification', 'hours' => 2, 'story' => 'US01'],
                    ],
                ],
                [
                    'label' => 'Ajouter une authentification renforcée',
                    'resultat' => 'La User Story d’authentification renforcée est ajoutée en priorité haute.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US18', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
            ],
        ],
        'evt_indispo' => [
            'titre' => 'Indisponibilité technique',
            'description' => 'Le service d’annuaire utilisé pour identifier les collaborateurs devient temporairement indisponible.',
            'options' => [
                [
                    'label' => 'Mettre en place un mode dégradé',
                    'resultat' => 'Une tâche de contournement de 2 heures est ajoutée au Sprint.',
                    'effets' => [
                        ['type' => 'add_task', 'title' => 'Mise en place d’un mode dégradé annuaire', 'hours' => 2, 'story' => null],
                    ],
                ],
                [
                    'label' => 'Attendre le rétablissement du service',
                    'resultat' => 'La capacité disponible est réduite de 2 heures.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 2],
                    ],
                ],
            ],
        ],
        'evt_interlocuteur' => [
            'titre' => 'Changement d’interlocuteur client',
            'description' => 'Le nouvel interlocuteur de la DSI souhaite réexaminer les priorités et mieux suivre les demandes par service.',
            'options' => [
                [
                    'label' => 'Créer un suivi par service',
                    'resultat' => 'La User Story de suivi par service est ajoutée en priorité haute.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US19', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
                [
                    'label' => 'Revenir au parcours de demande',
                    'resultat' => 'US02 remonte en tête du backlog.',
                    'effets' => [
                        ['type' => 'set_priority', 'story' => 'US02', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
            ],
        ],
        'evt_dette' => [
            'titre' => 'Dette technique',
            'description' => 'La conception du socle de données montre ses limites : les évolutions deviennent coûteuses à intégrer.',
            'options' => [
                [
                    'label' => 'Reprendre la conception maintenant',
                    'resultat' => 'Une tâche de refactorisation de 2 heures est ajoutée au Sprint.',
                    'effets' => [
                        ['type' => 'add_task', 'title' => 'Refactorisation du socle de données', 'hours' => 2, 'story' => null],
                    ],
                ],
                [
                    'label' => 'Documenter et reporter la reprise',
                    'resultat' => 'La capacité disponible est réduite de 2 heures pour documenter la dette.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 2],
                    ],
                ],
            ],
        ],
        'evt_integration' => [
            'titre' => 'Difficulté d’intégration',
            'description' => 'La connexion avec l’annuaire des collaborateurs révèle des cas d’authentification non prévus. L’intégration doit être stabilisée avant la démonstration.',
            'options' => [
                [
                    'label' => 'Tester et corriger maintenant',
                    'resultat' => 'Une tâche de test et de correction de 2 heures est ajoutée au Sprint.',
                    'effets' => [
                        ['type' => 'add_task', 'title' => 'Test et correction de l’intégration annuaire', 'hours' => 2, 'story' => null],
                    ],
                ],
                [
                    'label' => 'Reporter l’intégration',
                    'resultat' => 'La capacité disponible est réduite de 2 heures pour sécuriser le contournement.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 2],
                    ],
                ],
            ],
        ],
        'evt_clarification' => [
            'titre' => 'Demande de clarification métier',
            'description' => 'La DSI demande de clarifier les règles de priorité entre les demandes urgentes et les incidents majeurs avant de poursuivre.',
            'options' => [
                [
                    'label' => 'Organiser un atelier de clarification',
                    'resultat' => 'La capacité disponible est réduite de 2 heures pour préparer et animer l’atelier.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 2],
                    ],
                ],
                [
                    'label' => 'Ajouter une analyse des règles',
                    'resultat' => 'Une tâche d’analyse des règles de priorité de 2 heures est ajoutée au Sprint.',
                    'effets' => [
                        ['type' => 'add_task', 'title' => 'Analyse des règles de priorité métier', 'hours' => 2, 'story' => null],
                    ],
                ],
            ],
        ],
        'evt_defaut' => [
            'titre' => 'Défaut critique',
            'description' => 'Un défaut bloquant est découvert sur le changement de statut d’une demande. Le service informatique ne peut plus suivre correctement les traitements.',
            'options' => [
                [
                    'label' => 'Analyser puis corriger immédiatement',
                    'resultat' => 'US06 est réouverte et une tâche d’analyse et de correction de 2 heures est ajoutée.',
                    'effets' => [
                        ['type' => 'select_story', 'story' => 'US06'],
                        ['type' => 'reopen_story', 'story' => 'US06'],
                        ['type' => 'add_task', 'title' => 'Analyse et correction du défaut de changement de statut', 'hours' => 2, 'story' => 'US06'],
                    ],
                ],
                [
                    'label' => 'Appliquer un correctif rapide',
                    'resultat' => 'US06 est réouverte et une tâche de correction de 2 heures est ajoutée.',
                    'effets' => [
                        ['type' => 'select_story', 'story' => 'US06'],
                        ['type' => 'reopen_story', 'story' => 'US06'],
                        ['type' => 'add_task', 'title' => 'Correction du défaut de changement de statut', 'hours' => 2, 'story' => 'US06'],
                    ],
                ],
            ],
        ],
        'evt_delai' => [
            'titre' => 'Réduction du délai',
            'description' => 'La DSI avance la démonstration de fin de Sprint. L’équipe doit préparer une présentation convaincante plus tôt que prévu.',
            'options' => [
                [
                    'label' => 'Mobiliser du temps pour la démonstration',
                    'resultat' => 'La capacité disponible est réduite de 3 heures.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 3],
                    ],
                ],
                [
                    'label' => 'Préparer la démonstration dans le Sprint',
                    'resultat' => 'Une tâche de préparation de 2 heures est ajoutée et la capacité baisse de 1 heure.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 1],
                        ['type' => 'add_task', 'title' => 'Préparation de la démonstration', 'hours' => 2, 'story' => null],
                    ],
                ],
            ],
        ],
        'evt_metier' => [
            'titre' => 'Nouvelle contrainte métier',
            'description' => 'Le service logistique demande que les interventions soient organisées entre les trois sites de l’entreprise.',
            'options' => [
                [
                    'label' => 'Prendre en compte les sites distants',
                    'resultat' => 'La User Story multi-site est ajoutée en priorité haute.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US20', 'priorite' => 'haute', 'top' => true],
                    ],
                ],
                [
                    'label' => 'Proposer une assistance contextualisée',
                    'resultat' => 'La User Story d’assistance est ajoutée en priorité basse.',
                    'effets' => [
                        ['type' => 'add_story', 'story' => 'US21', 'priorite' => 'basse', 'top' => false],
                    ],
                ],
            ],
        ],
        'evt_absence' => [
            'titre' => 'Absence d’un Developer',
            'description' => 'Un Developer sera indisponible pendant une durée imprévue pour une raison personnelle ou professionnelle.',
            'options' => [
                [
                    'label' => 'Réorganiser le travail de l’équipe',
                    'resultat' => 'La capacité disponible est réduite de 2 heures.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 2],
                    ],
                ],
                [
                    'label' => 'Acter une absence longue',
                    'resultat' => 'La capacité disponible est réduite de 3 heures.',
                    'effets' => [
                        ['type' => 'reduce_capacity', 'hours' => 3],
                    ],
                ],
            ],
        ],
    ];
}

/**
 * Génère un calendrier d'événements indépendant pour chaque équipe.
 */
function initial_events_data(array $schedule): array
{
    $start = (int) $schedule['sim_start_at'];
    $end = (int) $schedule['sim_end_at'];
    $teams = [];

    foreach ([1, 2, 3] as $teamId) {
        $scheduled = [];
        $count = max(1, MAX_EVENTS_PER_TEAM);
        // Premier événement après 5 à 8 minutes, dernier 5 à 8 minutes avant
        // la fin : les sept événements sont répartis sur les 85 minutes
        // utiles, sans temps d'attente imposé.
        $first = $start + random_int(5 * 60, 8 * 60);
        $last = $end - random_int(5 * 60, 8 * 60);
        $minimumSpan = ($count - 1) * EVENT_MIN_GAP_SECONDS;
        if ($last - $first < $minimumSpan) {
            $last = $first + $minimumSpan;
        }
        $step = $count > 1 ? intdiv($last - $first, $count - 1) : 0;
        $limit = $end - 3 * 60;

        for ($index = 0; $index < $count; $index++) {
            $cursor = $first + ($index * $step);
            if ($cursor >= $limit) {
                break;
            }
            $scheduled[] = $cursor;
        }

        $teams[(string) $teamId] = [
            'scheduled' => $scheduled,
            'cursor' => 0,
            'used' => [],
            'active' => null,
            'history' => [],
            'last_trigger_at' => null,
        ];
    }

    return ['revision' => 1, 'generated_at' => iso_now(), 'teams' => $teams];
}

/**
 * Version publique des événements d'une équipe.
 */
function events_public(array $events, int $teamId): array
{
    $teamEvents = $events['teams'][(string) $teamId] ?? [
        'active' => null,
        'history' => [],
        'used' => [],
    ];

    return [
        'active' => $teamEvents['active'] ?? null,
        'history' => array_slice(array_reverse($teamEvents['history'] ?? []), 0, 20),
        'triggered' => count($teamEvents['used'] ?? []),
        'resolved' => count($teamEvents['history'] ?? []),
    ];
}

/**
 * Vérifie qu'un événement est cohérent avec l'état courant de l'équipe.
 * Cela évite de proposer des conséquences sans objet (par exemple une tâche
 * technique alors qu'aucune User Story n'est encore sélectionnée).
 */
function event_allowed_for_team(string $eventId, array $team): bool
{
    $hasWorkableStory = false;
    foreach ($team['stories'] as $story) {
        if (!empty($story['selectionnee']) && ($story['etat'] ?? '') !== 'validee') {
            $hasWorkableStory = true;
            break;
        }
    }

    return match ($eventId) {
        // Ces événements peuvent ajouter une tâche technique : ils ne sont
        // proposés que si une User Story du Sprint peut la porter.
        'evt_dette', 'evt_indispo', 'evt_rgpd', 'evt_delai', 'evt_integration', 'evt_clarification' => $hasWorkableStory,
        'evt_absence' => capacity_info($team)['available'] >= 3,
        default => true,
    };
}

/**
 * Déclenche les événements dus, sans jamais dépasser un événement actif
 * par équipe. Appelé à chaque synchronisation.
 */
function trigger_events_if_due(): void
{
    trigger_auto_blockages_if_due();
    trigger_pedagogical_messages_if_due();

    $session = current_session();
    $phase = session_phase($session);
    if ($phase !== 'running') {
        return;
    }

    $now = time();
    $events = json_read(EVENTS_FILE, []);
    $due = false;
    foreach ([1, 2, 3] as $teamId) {
        $t = $events['teams'][(string) $teamId] ?? null;
        if (!is_array($t) || !empty($t['active'])) {
            continue;
        }
        $cursor = (int) ($t['cursor'] ?? 0);
        $scheduled = $t['scheduled'] ?? [];
        if (!isset($scheduled[$cursor]) || $now < (int) $scheduled[$cursor]) {
            continue;
        }
        $last = $t['last_trigger_at'] ?? null;
        if ($last !== null && $now - (int) $last < EVENT_MIN_GAP_SECONDS) {
            continue;
        }
        $due = true;
        break;
    }
    if (!$due) {
        return;
    }

    with_app_lock(static function () use ($now): void {
        $session = current_session();
        if (session_phase($session) !== 'running') {
            return;
        }
        $events = json_read(EVENTS_FILE, []);
        $catalog = event_catalog();
        $catalogIds = array_keys($catalog);
        $changed = false;

        foreach ([1, 2, 3] as $teamId) {
            $key = (string) $teamId;
            $t = $events['teams'][$key] ?? null;
            if (!is_array($t) || !empty($t['active'])) {
                continue;
            }
            $cursor = (int) ($t['cursor'] ?? 0);
            $scheduled = $t['scheduled'] ?? [];
            if (!isset($scheduled[$cursor]) || $now < (int) $scheduled[$cursor]) {
                continue;
            }
            $last = $t['last_trigger_at'] ?? null;
            if ($last !== null && $now - (int) $last < EVENT_MIN_GAP_SECONDS) {
                continue;
            }

            $team = load_team($teamId);
            $available = array_values(array_filter(
                array_diff($catalogIds, $t['used'] ?? []),
                static fn(string $eventId): bool => event_allowed_for_team($eventId, $team)
            ));
            $t['cursor'] = $cursor + 1;
            if ($available === []) {
                $events['teams'][$key] = $t;
                $changed = true;
                continue;
            }

            $eventId = $available[array_rand($available)];
            $definition = $catalog[$eventId];
            $options = [];
            foreach ($definition['options'] as $option) {
                $options[] = [
                    'label' => $option['label'],
                    'resultat' => $option['resultat'],
                ];
            }

            $t['used'][] = $eventId;
            $t['last_trigger_at'] = $now;
            $t['active'] = [
                'id' => 'EV' . $teamId . '-' . count($t['used']),
                'event_id' => $eventId,
                'titre' => $definition['titre'],
                'description' => $definition['description'],
                'triggered_at' => $now,
                'options' => $options,
            ];
            $events['teams'][$key] = $t;
            $changed = true;

            history_add($teamId, 'systeme', 'event_trigger', 'Événement automatique : ' . $definition['titre'], [
                'event_id' => $eventId,
            ]);
        }

        if ($changed) {
            $events['revision'] = (int) ($events['revision'] ?? 0) + 1;
            $events['updated_at'] = iso_now();
            json_write_atomic(EVENTS_FILE, $events);
        }
    });
}

/**
 * Texte des messages pédagogiques automatiques.
 * Le login du Scrum Master est injecté dans les messages qui le ciblent.
 */
function pedagogical_message_text(string $key, string $scrumMaster = '', string $productOwner = ''): string
{
    $texts = [
        'welcome' => 'Bonjour @{sm}, la simulation commence ! En tant que Scrum Master, votre rôle est de faciliter le déroulement du sprint et de veiller à sa bonne progression. Consultez régulièrement l’onglet Historique pour superviser les opérations de votre équipe. Consultez aussi l’onglet Bilan et lisez son aide pour comprendre les indicateurs calculés. Portez une attention particulière au taux de couverture du sprint : il permet de suivre la progression des User Stories validées. À la fin de la simulation, un palmarès sera généré automatiquement pour classer les équipes, les Product Owners, les Scrum Masters et les Developers. Votre score de Scrum Master combine l’activité de votre chat (messages distincts et utiles) et la performance moyenne de vos Developers : animez le chat avec des messages apportant une information nouvelle et accompagnez vos Developers pour qu’ils livrent des User Stories de qualité.',
        'welcome_po' => 'Bonjour @{po}, la simulation commence ! En tant que Product Owner, votre rôle est de représenter le client et de maximiser la valeur livrée par l’équipe. Vous priorisez le Product Backlog selon la valeur métier, validez ou refusez les User Stories selon la Definition of Done et consultez le feedback client. Consultez régulièrement le bilan pour suivre la valeur validée. N’oubliez pas que seules les User Stories validées par vous comptent dans l’incrément et dans les indicateurs. À la fin de la simulation, un palmarès sera généré automatiquement pour classer les équipes, les Product Owners, les Scrum Masters et les Developers. Votre score de Product Owner combine la qualité moyenne de vos Developers et votre volume de validations : validez les User Stories dès qu’elles respectent la Definition of Done et aidez vos Developers à livrer des incrément de qualité pour maximiser l’acceptation client.',
        'welcome_dev' => 'Bonjour les Developers, la simulation commence ! En tant que Developer, votre rôle est de réaliser le travail nécessaire pour transformer les User Stories en incrément. Vous créez et estimez les tâches techniques, les déplacez dans le Kanban, signalez et résolvez les blocages, et déclarez une User Story prête à valider lorsqu’elle respecte la Definition of Done. Consultez le Kanban et le bilan pour suivre votre charge. N’hésitez pas à utiliser le chat pour coordonner le traitement des tâches. À la fin de la simulation, un palmarès sera généré automatiquement pour classer les équipes, les Product Owners, les Scrum Masters et les Developers. Votre score de Developer combine la qualité de vos livraisons (taux d’acceptation client et absence de risque) et votre volume de décisions client : visez des User Stories acceptées, sans risque et suffisamment nombreuses.',
        'coverage' => 'Bonjour @{sm}, petit point de vigilance : le taux de couverture du sprint est toujours de 0 %. Aucune User Story n’a encore été validée par votre équipe. Consultez le tableau Kanban pour vérifier l’état d’avancement des tâches et identifier d’éventuels blocages. N’oubliez pas que le Product Owner doit valider les User Stories dès que les tâches correspondantes ont été traitées par les Developers. À vous de faciliter les échanges pour permettre à votre équipe de progresser !',
        'midpoint' => '@équipe, vous êtes à mi-parcours de la simulation ! Profitez de cette étape pour échanger vos rôles si vous le souhaitez. Expérimentez les responsabilités de Scrum Master, Product Owner et Developer afin de mieux comprendre les enjeux de chacun. Découvrir les différents métiers Scrum vous permettra de mieux appréhender la collaboration et le fonctionnement d’une équipe agile. Bonne deuxième moitié de sprint !',
        'dev_coordination' => 'Bonjour les Developers, la simulation commence ! Votre équipe compte plusieurs développeurs : merci de vous coordonner pour gérer les User Stories dans le Kanban. Il est conseillé que chaque développeur traite en priorité les tâches qui lui sont affectées, et de vous répartir équitablement le traitement des tâches en bonne intelligence. Utilisez le chat pour communiquer à propos du traitement des tâches dans le Kanban et du Product Backlog pour la validation lorsqu’elles sont dans l’état terminé. Bon sprint ! À la fin de la simulation, un palmarès sera généré automatiquement pour classer les équipes, les Product Owners, les Scrum Masters et les Developers. Votre score de Developer combine la qualité de vos livraisons (taux d’acceptation client et absence de risque) et votre volume de décisions client : répartissez-vous équitablement les tâches, évitez les blocages et visez des User Stories acceptées sans risque.',
    ];

    return isset($texts[$key])
        ? str_replace(['{sm}', '{po}'], [$scrumMaster, $productOwner], $texts[$key])
        : '';
}

/**
 * Retourne le login du membre qui détient un rôle dans l'équipe.
 */
function team_login_for_role(array $team, string $role): ?string
{
    foreach ($team['members'] as $member) {
        if (($member['role'] ?? '') === $role) {
            return (string) ($member['login'] ?? '');
        }
    }
    return null;
}

/**
 * Ajoute un message au chat collectif, sans créer de canal privé.
 * L’auteur permet de distinguer un membre de l’équipe d’un message du système.
 */
function team_add_chat_message(array &$team, string $author, int $now, string $text): array
{
    $number = (int) ($team['next_message_number'] ?? 1);
    $team['messages'][] = [
        'id' => 'M' . $number,
        'author' => $author,
        'at' => iso_at($now),
        'ts' => $now,
        'text' => $text,
    ];
    $team['next_message_number'] = $number + 1;
    if (count($team['messages']) > 300) {
        $team['messages'] = array_slice($team['messages'], -300);
    }
    $lastIndex = count($team['messages']) - 1;
    return ['message' => $team['messages'][$lastIndex]];
}

/**
 * Ajoute un message automatique au chat collectif, sans créer de canal privé.
 */
function team_add_system_message(array &$team, string $text, int $now): void
{
    team_add_chat_message($team, 'systeme', $now, $text);
}

/**
 * Publie les trois messages pédagogiques dus, une seule fois par simulation.
 * Le verrou global et les drapeaux de session protègent contre les doublons.
 */
function trigger_pedagogical_messages_if_due(?int $now = null): void
{
    $now ??= time();
    $session = current_session();
    if (session_phase($session, $now) !== 'running') {
        return;
    }

    $schedule = session_effective_schedule($session, $now);
    $start = (int) $schedule['sim_start_at'];
    $end = (int) $schedule['sim_end_at'];
    if ($now < $start || $now >= $end) {
        return;
    }
    $coverageAt = $start + 20 * 60;
    $midpointAt = $start + intdiv(max(1, $end - $start), 2);

    $states = is_array($session['pedagogical_messages'] ?? null) ? $session['pedagogical_messages'] : [];
    $due = false;
    foreach ([1, 2, 3] as $teamId) {
        $flags = is_array($states[(string) $teamId] ?? null) ? $states[(string) $teamId] : [];
        if (empty($flags['welcome_at'])
            || ($now >= $coverageAt && empty($flags['coverage_checked_at']))
            || ($now >= $midpointAt && empty($flags['midpoint_at']))) {
            $due = true;
            break;
        }
    }
    if (!$due) {
        return;
    }

    with_app_lock(static function () use ($now, $coverageAt, $midpointAt): void {
        $session = current_session();
        if (session_phase($session, $now) !== 'running') {
            return;
        }

        $states = is_array($session['pedagogical_messages'] ?? null) ? $session['pedagogical_messages'] : [];
        $changed = false;

        foreach ([1, 2, 3] as $teamId) {
            $key = (string) $teamId;
            $flags = is_array($states[$key] ?? null) ? $states[$key] : [];
            $welcomeDue = empty($flags['welcome_at']);
            $coverageDue = $now >= $coverageAt && empty($flags['coverage_checked_at']);
            $midpointDue = $now >= $midpointAt && empty($flags['midpoint_at']);
            if (!$welcomeDue && !$coverageDue && !$midpointDue) {
                continue;
            }

            with_locked_json(team_file_path($teamId), static function (array &$team) use (
                $now,
                $welcomeDue,
                $coverageDue,
                $midpointDue,
                &$flags
            ): void {
                assert_team_data($team);
                $scrumMaster = team_login_for_role($team, 'SCRUM_MASTER');
                $added = false;

                if ($welcomeDue) {
                    if ($scrumMaster !== null) {
                        team_add_system_message($team, pedagogical_message_text('welcome', $scrumMaster), $now);
                        $added = true;
                    }
                    $productOwner = team_login_for_role($team, 'PO');
                    if ($productOwner !== null) {
                        team_add_system_message($team, pedagogical_message_text('welcome_po', '', $productOwner), $now);
                        $added = true;
                    }
                    $developerCount = count(array_filter($team['members'] ?? [], static fn(array $m): bool => ($m['role'] ?? '') === 'DEVELOPER'));
                    if ($developerCount > 0) {
                        team_add_system_message($team, pedagogical_message_text('welcome_dev'), $now);
                        $added = true;
                    }
                    if ($developerCount > 1) {
                        team_add_system_message($team, pedagogical_message_text('dev_coordination'), $now);
                        $added = true;
                    }
                    $flags['welcome_at'] = iso_at($now);
                }

                if ($coverageDue) {
                    $stats = team_stats($team);
                    $coverage = $stats['us_selectionnees'] > 0
                        ? (int) round($stats['us_validees'] / $stats['us_selectionnees'] * 100)
                        : 0;
                    $flags['coverage_checked_at'] = iso_at($now);
                    if ($coverage === 0 && $scrumMaster !== null) {
                        team_add_system_message($team, pedagogical_message_text('coverage', $scrumMaster), $now);
                        $flags['coverage_warning_at'] = iso_at($now);
                        $added = true;
                    }
                }

                if ($midpointDue) {
                    team_add_system_message($team, pedagogical_message_text('midpoint'), $now);
                    $flags['midpoint_at'] = iso_at($now);
                    $added = true;
                }

                if ($added) {
                    $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
                    $team['updated_at'] = iso_now();
                }
            });

            $states[$key] = $flags;
            $changed = true;
        }

        if ($changed) {
            $session['pedagogical_messages'] = $states;
            $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
            $session['updated_at'] = iso_now();
            json_write_atomic(SESSION_FILE, $session);
        }
    });
}

/**
 * Déclenche un blocage automatique et ponctuel sur une tâche en cours,
 * pour rendre la simulation plus réaliste. Aucun blocage n'est imposé si
 * aucune tâche n'est en cours. Le throttle et la probabilité gardent le
 * mécanisme occasionnel.
 */
function trigger_auto_blockages_if_due(?int $now = null): void
{
    $now ??= time();
    $session = current_session();
    if (session_phase($session, $now) !== 'running') {
        return;
    }

    $schedule = session_effective_schedule($session, $now);
    $start = (int) $schedule['sim_start_at'];
    if ($now < $start + AUTO_BLOCKAGE_GRACE_SECONDS) {
        return;
    }

    $states = is_array($session['pedagogical_messages'] ?? null) ? $session['pedagogical_messages'] : [];
    $anyEligible = false;
    foreach ([1, 2, 3] as $teamId) {
        $flags = is_array($states[(string) $teamId] ?? null) ? $states[(string) $teamId] : [];
        $last = (int) ($flags['last_auto_blockage_at'] ?? 0);
        if ($last === 0 || ($now - $last) >= AUTO_BLOCKAGE_THROTTLE_SECONDS) {
            $anyEligible = true;
            break;
        }
    }
    if (!$anyEligible) {
        return;
    }

    $reasons = [
        'Dépendance externe non disponible.',
        'Information manquante du client.',
        'Environnement de test indisponible.',
        'Accès à un service tiers refusé.',
        'Spécification ambiguë à clarifier.',
    ];

    with_app_lock(static function () use ($now, $reasons): void {
        $session = current_session();
        if (session_phase($session, $now) !== 'running') {
            return;
        }

        $states = is_array($session['pedagogical_messages'] ?? null) ? $session['pedagogical_messages'] : [];
        $changed = false;

        foreach ([1, 2, 3] as $teamId) {
            $key = (string) $teamId;
            $flags = is_array($states[$key] ?? null) ? $states[$key] : [];
            $last = (int) ($flags['last_auto_blockage_at'] ?? 0);
            if ($last > 0 && ($now - $last) < AUTO_BLOCKAGE_THROTTLE_SECONDS) {
                continue;
            }

            $team = load_team($teamId);
            $candidates = [];
            foreach ($team['tasks'] as $task) {
                if (!empty($task['archived'])) {
                    continue;
                }
                if (($task['etat'] ?? '') !== 'en_cours') {
                    continue;
                }
                $storyId = (string) ($task['us_id'] ?? '');
                $storyIndex = team_story_index($team, $storyId);
                if ($storyIndex === null) {
                    continue;
                }
                if (empty($team['stories'][$storyIndex]['selectionnee'])) {
                    continue;
                }
                $candidates[] = $task;
            }
            if ($candidates === []) {
                continue;
            }

            if (random_int(1, 100) > AUTO_BLOCKAGE_PROBABILITY) {
                continue;
            }

            $chosen = $candidates[array_rand($candidates)];
            $chosenId = (string) $chosen['id'];
            $reason = $reasons[array_rand($reasons)];

            $applied = with_locked_json(team_file_path($teamId), static function (array &$team) use ($chosenId, $reason, $now, &$flags): bool {
                assert_team_data($team);
                $index = team_task_index($team, $chosenId);
                if ($index === null || !empty($team['tasks'][$index]['archived'])) {
                    return false;
                }
                $task = &$team['tasks'][$index];
                if (($task['etat'] ?? '') !== 'en_cours') {
                    return false;
                }

                $task['etat'] = 'bloque';
                $task['blocage'] = $reason;
                $task['blocage_by'] = 'systeme';
                $task['updated_at'] = iso_now();
                $task['updated_by'] = 'systeme';

                $storyIndex = team_story_index($team, (string) $task['us_id']);
                if ($storyIndex !== null && ($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
                    $team['stories'][$storyIndex]['etat'] = 'a_faire';
                }

                team_add_system_message($team, 'Blocage automatique sur ' . $chosenId . ' : ' . $reason, $now);

                $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
                $team['updated_at'] = iso_now();

                $flags['last_auto_blockage_at'] = $now;
                return true;
            });

            if (!$applied) {
                continue;
            }

            $states[$key] = $flags;
            $changed = true;

            history_add($teamId, 'systeme', 'task_block', 'Blocage automatique sur ' . $chosenId . ' : ' . $reason, [
                'task' => $chosenId,
            ]);
        }

        if ($changed) {
            $session['pedagogical_messages'] = $states;
            $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
            $session['updated_at'] = iso_now();
            json_write_atomic(SESSION_FILE, $session);
        }
    });
}

/**
 * Applique les effets d'un événement à une équipe.
 */
function apply_event_effects(array &$team, int $teamId, array $effects): array
{
    $notes = [];
    $extraStories = event_stories();

    foreach ($effects as $effect) {
        $type = $effect['type'] ?? '';
        switch ($type) {
            case 'add_story':
                $storyId = (string) ($effect['story'] ?? '');
                if (!isset($extraStories[$storyId])) {
                    $notes[] = 'User Story d’événement inconnue.';
                    break;
                }
                $existing = null;
                foreach ($team['stories'] as $index => $story) {
                    if ($story['id'] === $storyId) {
                        $existing = $index;
                        break;
                    }
                }
                if ($existing === null) {
                    $story = $extraStories[$storyId];
                    $story['priorite'] = (string) ($effect['priorite'] ?? 'moyenne');
                    $story['risques'] = $story['risques'] ?? 'Aucun risque majeur identifié à ce stade.';
                    $story['selectionnee'] = false;
                    $story['etat'] = 'a_faire';
                    $story['refus_commentaire'] = null;
                    $story['validee_at'] = null;
                    $story['refusee_at'] = null;
                    if (!empty($effect['top'])) {
                        array_unshift($team['stories'], $story);
                    } else {
                        $team['stories'][] = $story;
                    }
                    $notes[] = 'La User Story ' . $storyId . ' a été ajoutée au backlog.';
                } else {
                    $team['stories'][$existing]['priorite'] = (string) ($effect['priorite'] ?? 'moyenne');
                    if (!empty($effect['top'])) {
                        $story = $team['stories'][$existing];
                        array_splice($team['stories'], $existing, 1);
                        array_unshift($team['stories'], $story);
                    }
                    $notes[] = 'La User Story ' . $storyId . ' est déjà présente : sa priorité a été ajustée.';
                }
                break;

            case 'set_priority':
                $storyId = (string) ($effect['story'] ?? '');
                $index = team_story_index($team, $storyId);
                if ($index === null) {
                    $notes[] = 'User Story ' . $storyId . ' introuvable pour le changement de priorité.';
                    break;
                }
                $team['stories'][$index]['priorite'] = (string) ($effect['priorite'] ?? 'haute');
                if (!empty($effect['top'])) {
                    $story = $team['stories'][$index];
                    array_splice($team['stories'], $index, 1);
                    array_unshift($team['stories'], $story);
                }
                $notes[] = 'La priorité de ' . $storyId . ' a été modifiée.';
                break;

            case 'select_story':
                $storyId = (string) ($effect['story'] ?? '');
                $index = team_story_index($team, $storyId);
                if ($index === null) {
                    $notes[] = 'User Story ' . $storyId . ' introuvable pour la sélection.';
                    break;
                }
                $team['stories'][$index]['selectionnee'] = true;
                $notes[] = 'La User Story ' . $storyId . ' a été ajoutée au Sprint.';
                break;

            case 'reopen_story':
                $storyId = isset($effect['story']) ? (string) $effect['story'] : null;
                $index = null;
                if ($storyId !== null) {
                    $index = team_story_index($team, $storyId);
                } else {
                    foreach ($team['stories'] as $i => $story) {
                        if (($story['etat'] ?? '') === 'validee') {
                            $index = $i;
                            break;
                        }
                    }
                }
                if ($index === null) {
                    $notes[] = 'Aucune User Story à rouvrir.';
                    break;
                }
                $team['stories'][$index]['etat'] = 'a_faire';
                $team['stories'][$index]['selectionnee'] = true;
                $team['stories'][$index]['refus_commentaire'] = null;
                $team['stories'][$index]['validee_at'] = null;
                $team['stories'][$index]['refusee_at'] = null;
                $notes[] = 'La User Story ' . $team['stories'][$index]['id'] . ' a été réouverte.';
                break;

            case 'add_task':
                $storyId = isset($effect['story']) ? (string) $effect['story'] : null;
                $targetIndex = null;
                if ($storyId !== null) {
                    $targetIndex = team_story_index($team, $storyId);
                    if ($targetIndex !== null && empty($team['stories'][$targetIndex]['selectionnee'])) {
                        $targetIndex = null;
                    }
                }
                if ($targetIndex === null) {
                    foreach ($team['stories'] as $i => $story) {
                        if (!empty($story['selectionnee']) && ($story['etat'] ?? '') !== 'validee') {
                            $targetIndex = $i;
                            break;
                        }
                    }
                }
                if ($targetIndex === null) {
                    $notes[] = 'Aucune User Story sélectionnée : la tâche technique n’a pas pu être ajoutée.';
                    break;
                }
                // Une nouvelle tâche remet en travail une User Story qui
                // attendait seulement la validation du PO.
                if (($team['stories'][$targetIndex]['etat'] ?? '') === 'a_valider') {
                    $team['stories'][$targetIndex]['etat'] = 'a_faire';
                }
                $taskNumber = (int) ($team['next_task_number'] ?? 1);
                $team['tasks'][] = [
                    'id' => 'T' . $taskNumber,
                    'us_id' => $team['stories'][$targetIndex]['id'],
                    'titre' => (string) ($effect['title'] ?? 'Tâche issue d’un événement'),
                    'titre_key' => task_title_key((string) ($effect['title'] ?? 'Tâche issue d’un événement')),
                    'description' => 'Tâche ajoutée automatiquement par un événement.',
                    'heures' => (float) ($effect['hours'] ?? 1),
                    'assigne' => null,
                    'etat' => 'a_faire',
                    'blocage' => null,
                    'archived' => false,
                    'created_at' => iso_now(),
                    'updated_at' => iso_now(),
                    'updated_by' => 'systeme',
                ];
                $team['next_task_number'] = $taskNumber + 1;
                $notes[] = 'Une tâche de ' . (float) ($effect['hours'] ?? 1) . ' h a été ajoutée.';
                break;

            case 'reduce_capacity':
                $hours = (float) ($effect['hours'] ?? 0);
                $max = DEVELOPER_CAPACITY_HOURS * max(1, count(array_filter(
                    $team['members'],
                    static fn(array $m): bool => ($m['role'] ?? '') === 'DEVELOPER'
                )));
                $team['capacite_reduction'] = min($max, (float) ($team['capacite_reduction'] ?? 0) + $hours);
                $notes[] = 'La capacité disponible a été réduite de ' . $hours . ' h.';
                break;

            default:
                $notes[] = 'Effet d’événement non reconnu.';
        }
    }

    $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
    $team['updated_at'] = iso_now();
    return $notes;
}

/**
 * Index d'une User Story dans le backlog.
 */
function team_story_index(array $team, string $storyId): ?int
{
    foreach ($team['stories'] as $index => $story) {
        if (($story['id'] ?? '') === $storyId) {
            return $index;
        }
    }
    return null;
}

/**
 * Index d'une tâche dans l'équipe.
 */
function team_task_index(array $team, string $taskId): ?int
{
    foreach ($team['tasks'] as $index => $task) {
        if (($task['id'] ?? '') === $taskId) {
            return $index;
        }
    }
    return null;
}

// ===========================================================================
// Contexte et mutations étudiantes
// ===========================================================================

/**
 * Vérifie le contexte d'un étudiant et retourne son équipe.
 */
function student_context(array $user, bool $mutable = true): array
{
    if (!empty($user['is_teacher'])) {
        throw new AppError('Cette action est réservée aux étudiants.', 403);
    }
    $teamId = (int) ($user['team'] ?? 0);
    if ($teamId < 1 || $teamId > 3) {
        throw new AppError('Équipe invalide.', 403);
    }

    $session = current_session();
    $phase = session_phase($session);
    if ($mutable) {
        assert_simulation_running($phase);
    }

    $team = load_team($teamId);
    if (team_member($team, (string) $user['login']) === null) {
        throw new AppError('Vous n’appartenez pas à cette équipe.', 403);
    }

    return [
        'team_id' => $teamId,
        'team' => $team,
        'phase' => $phase,
        'role' => team_role($team, (string) $user['login']),
    ];
}

/**
 * Exécute une mutation sur l'équipe de l'étudiant sous verrou.
 * Le rôle est toujours relu côté serveur.
 */
function mutate_student_team(array $user, callable $callback, ?string $requiredRole = null): array
{
    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $login = (string) $user['login'];

    $result = with_locked_json(team_file_path($teamId), function (array &$team) use ($callback, $login, $requiredRole, $teamId): array {
        // Recontrôle de l'horloge au moment d'écrire : si la simulation vient
        // de se terminer ou d'être suspendue, aucune donnée ne doit être modifiée.
        assert_simulation_running(session_phase(current_session()));
        assert_team_data($team);
        $role = team_role($team, $login);
        if ($role === null) {
            throw new AppError('Vous n’appartenez pas à cette équipe.', 403);
        }
        if ($requiredRole !== null && $role !== $requiredRole) {
            throw new AppError('Action réservée au rôle ' . role_label($requiredRole) . '.', 403);
        }
        $value = $callback($team, $role, $teamId);
        // Dernier contrôle juste avant l'écriture : la fin ou la pause peut
        // être survenue pendant le traitement de la requête.
        assert_simulation_running(session_phase(current_session()));
        $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
        $team['updated_at'] = iso_now();
        return ['value' => $value, 'role' => $role];
    });

    return [$teamId, $result['value']];
}

/**
 * Estimation d'une User Story par un Developer (Fibonacci).
 */
function action_estimate_story(array $user, mixed $storyId, mixed $estimation): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');
    $estimation = require_int($estimation, 1, 13, 'Estimation');
    if (!in_array($estimation, [1, 2, 3, 5, 8, 13], true)) {
        throw new AppError('L’estimation doit utiliser l’échelle de Fibonacci : 1, 2, 3, 5, 8 ou 13.');
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($storyId, $estimation): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        $old = (int) $team['stories'][$index]['estimation'];
        $team['stories'][$index]['estimation'] = $estimation;
        return ['old' => $old, 'titre' => $team['stories'][$index]['titre']];
    }, 'DEVELOPER');

    history_add($teamId, $user['login'], 'estimation', 'Estimation de ' . $storyId . ' : ' . $result['old'] . ' → ' . $estimation . ' points.', [
        'story' => $storyId,
        'old' => $result['old'],
        'new' => $estimation,
    ]);
    return ['message' => 'Estimation mise à jour.'];
}

/**
 * Modification de la priorité d'une User Story par le PO.
 */
function action_set_priority(array $user, mixed $storyId, mixed $priority): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');
    $priority = require_choice($priority, ['haute', 'moyenne', 'basse'], 'Priorité');

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($storyId, $priority): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        $old = (string) $team['stories'][$index]['priorite'];
        $team['stories'][$index]['priorite'] = $priority;
        if ($priority === 'haute') {
            $story = $team['stories'][$index];
            array_splice($team['stories'], $index, 1);
            array_unshift($team['stories'], $story);
        }
        return ['old' => $old];
    }, 'PO');

    history_add($teamId, $user['login'], 'priority', 'Priorité de ' . $storyId . ' : ' . $result['old'] . ' → ' . $priority . '.', [
        'story' => $storyId,
        'old' => $result['old'],
        'new' => $priority,
    ]);
    return ['message' => 'Priorité mise à jour.'];
}

/**
 * Déplacement d'une User Story dans le backlog.
 */
function action_move_story(array $user, mixed $storyId, mixed $direction): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');
    $direction = require_choice($direction, ['up', 'down'], 'Déplacement');

    [$teamId] = mutate_student_team($user, function (array &$team) use ($storyId, $direction): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($team['stories'])) {
            throw new AppError('La User Story est déjà en limite de backlog.');
        }
        $tmp = $team['stories'][$index];
        $team['stories'][$index] = $team['stories'][$target];
        $team['stories'][$target] = $tmp;
        return [];
    }, 'PO');

    history_add($teamId, $user['login'], 'order', 'Réordonnancement du backlog : ' . $storyId . ' (' . ($direction === 'up' ? 'monter' : 'descendre') . ').', [
        'story' => $storyId,
        'direction' => $direction,
    ]);
    return ['message' => 'Ordre du backlog mis à jour.'];
}

/**
 * Sélection ou retrait d'une User Story du Sprint.
 */
function action_toggle_sprint(array $user, mixed $storyId, mixed $selected): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');
    $selected = filter_var($selected, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($selected === null) {
        throw new AppError('Sélection invalide.');
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($storyId, $selected): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        $story = $team['stories'][$index];

        if ($selected) {
            if (($story['etat'] ?? '') === 'validee') {
                throw new AppError('Une User Story validée ne peut pas être resélectionnée.');
            }
            $team['stories'][$index]['selectionnee'] = true;
            $team['stories'][$index]['etat'] = 'a_faire';
        } else {
            if (in_array($story['etat'] ?? '', ['a_valider', 'validee'], true)) {
                throw new AppError('Retirez d’abord la User Story de l’état de validation ou de validation finale.');
            }
            foreach ($team['tasks'] as $task) {
                if (($task['us_id'] ?? '') === $storyId && empty($task['archived'])) {
                    throw new AppError('Cette User Story possède des tâches : archivez-les avant de la retirer du Sprint.');
                }
            }
            $team['stories'][$index]['selectionnee'] = false;
        }
        return ['titre' => $story['titre']];
    });

    history_add(
        $teamId,
        $user['login'],
        $selected ? 'sprint_select' : 'sprint_unselect',
        ($selected ? 'Ajout au Sprint : ' : 'Retrait du Sprint : ') . $storyId . ' — ' . $result['titre'],
        ['story' => $storyId]
    );
    return ['message' => $selected ? 'User Story ajoutée au Sprint.' : 'User Story retirée du Sprint.'];
}

/**
 * Scénarios de réception client simulée.
 * La position détermine uniquement la décision autorisée au PO ; aucun
 * contrôle technique des critères ou de la Definition of Done n'est effectué.
 */
function client_feedback_scenarios(): array
{
    return [
        'satisfaction' => [
            'label' => 'Satisfaction',
            'position' => 'positive',
            'tone' => 'success',
            'variants' => [
                'Bonjour, je suis {client}. Nous avons testé la livraison de {story_label}. Le travail de {developer} répond à notre demande et les critères que nous avions exprimés sont couverts. Nous validons cette version avec satisfaction.',
                'Bonjour, je suis {client}. Nous avons examiné {story_label} avec nos équipes. La réalisation de {developer} est conforme à notre attente et nous n’avons relevé aucun point bloquant. Merci pour ce travail.',
                'Bonjour, je suis {client}. La livraison de {story_label} par {developer} est arrivée à point nommé. Nos équipes l’ont testée sur leurs cas d’usage principaux et tout fonctionne comme prévu. Nous validons sans réserve.',
            ],
        ],
        'indifference' => [
            'label' => 'Indifférence',
            'position' => 'positive',
            'tone' => 'success',
            'variants' => [
                'Bonjour, {client} à l’appareil. {story_label} est bien disponible. Le travail de {developer} fait le nécessaire, sans plus. Nous n’avons pas d’objection à sa mise en service.',
                'Bonjour, je suis {client}. Nous avons pris connaissance de {story_label}. La réalisation de {developer} est effectuée et nous acceptons la livraison, même si nous n’avons pas constaté d’amélioration marquante.',
                'Bonjour, je suis {client}. Nous avons reçu {story_label}, réalisée par {developer}. Le résultat est conforme au minimum attendu : nous l’acceptons, sans y voir un changement déterminant pour notre quotidien.',
            ],
        ],
        'amelioration' => [
            'label' => 'Demande d’amélioration',
            'position' => 'positive',
            'tone' => 'warning',
            'variants' => [
                'Bonjour, je suis {client}. Nous apprécions le travail de {developer} sur {story_label}. La livraison est acceptable et nous l’acceptons, mais nous souhaiterions une amélioration complémentaire sur le confort d’usage dans une prochaine version.',
                'Bonjour, je suis {client}. La demande {story_label} livrée par {developer} est globalement satisfaisante. Nous validons cette version, tout en notant une réserve non bloquante sur la clarté de certains libellés.',
                'Bonjour, je suis {client}. {developer} nous a livré {story_label}, que nous acceptons. Nous suggérons toutefois d’enrichir la prochaine version avec des messages d’aide plus explicites et un meilleur retour visuel.',
            ],
        ],
        'bug' => [
            'label' => 'Bug constaté',
            'position' => 'negative',
            'tone' => 'danger',
            'variants' => [
                'Bonjour, je suis {client}. Nous avons testé {story_label} et un dysfonctionnement apparaît lors de l’utilisation. Le travail de {developer} ne peut pas être accepté en l’état : le problème doit être corrigé avant la mise en service.',
                'Bonjour, je suis {client}. Lors de notre vérification de {story_label}, nous avons constaté un défaut reproductible. Malgré l’implication de {developer}, nous ne pouvons pas accepter cette livraison.',
                'Bonjour, je suis {client}. Sur {story_label}, le parcours réalisé par {developer} provoque une erreur reproductible dès la première utilisation. Nous devons refuser la livraison tant que ce défaut bloquant n’est pas corrigé.',
            ],
        ],
        'refus' => [
            'label' => 'Refus de la modification',
            'position' => 'negative',
            'tone' => 'danger',
            'variants' => [
                'Bonjour, je suis {client}. Après examen de {story_label}, nous constatons que le résultat ne correspond pas à notre demande initiale. Nous refusons cette version et attendons une nouvelle livraison de {developer}.',
                'Bonjour, je suis {client}. La livraison de {story_label} ne répond pas à nos attentes. Le travail de {developer} doit être repris sur les points convenus avant validation.',
                'Bonjour, je suis {client}. Nous avons comparé {story_label} à notre demande d’origine : plusieurs points convenus manquent et le résultat reste partiel. Le travail de {developer} doit être complété avant toute validation.',
            ],
        ],
        'delai' => [
            'label' => 'Mécontentement lié aux délais',
            'position' => 'negative',
            'tone' => 'danger',
            'variants' => [
                'Bonjour, je suis {client}. Nous attendions {story_label} depuis trop longtemps. Même si {developer} a fini le travail, ce délai pénalise notre activité et nous ne pouvons pas valider cette livraison en l’état.',
                'Bonjour, je suis {client}. La mise à disposition de {story_label} a pris trop de temps. Malgré le travail de {developer}, l’insatisfaction de nos équipes est trop forte pour accepter cette version maintenant.',
                'Bonjour, je suis {client}. La livraison de {story_label} a été repoussée à plusieurs reprises. Le travail de {developer} est désormais terminé, mais le retard accumulé nous empêche d’accepter cette version aujourd’hui.',
            ],
        ],
    ];
}

/**
 * Identités fictives des responsables client.
 */
function client_feedback_names(): array
{
    return [
        'Claire Moreau',
        'Julien Bernard',
        'Sophie Lefèvre',
        'Thomas Girard',
        'Nadia Benali',
        'Marc Dubois',
        'Élodie Perrin',
        'Antoine Roussel',
    ];
}

/**
 * Formulations utilisées après le premier refus négatif : la correction
 * précédemment demandée est prise en compte pour rendre le retour crédible.
 */
function client_feedback_followup_variants(): array
{
    return [
        'Bonjour, je suis {client}. Nous avons vérifié la nouvelle livraison de {story_label}. La correction apportée par {developer} répond au problème précédemment signalé et nous validons cette version avec satisfaction.',
        'Bonjour, je suis {client}. À la suite de notre précédent retour, nous avons testé {story_label}. {developer} a corrigé le point bloquant et le résultat correspond désormais à notre attente. Nous validons la livraison.',
        'Bonjour, je suis {client}. Nous avons contrôlé la nouvelle version de {story_label}. {developer} a tenu compte de notre retour précédent et le problème est résolu ; nous acceptons cette livraison avec satisfaction.',
    ];
}

/**
 * Décision de refus pour un tirage donné (1 à 100).
 * Sans risque : 30 % de refus. Avec au moins un risque : 60 % de refus.
 */
function client_decision_refuse_for_draw(bool $risk, int $draw): bool
{
    return $draw <= ($risk ? 60 : 30);
}

/**
 * Effectue l'unique tirage aléatoire de la décision du client.
 */
function client_decision_refuse(bool $risk): bool
{
    return client_decision_refuse_for_draw($risk, random_int(1, 100));
}

/**
 * Calcule le temps passé par une tâche dans un état donné.
 * Retourne null lorsque l'historique ne permet pas de le mesurer.
 */
function client_decision_seconds_in_state(int $teamId, array $task, string $state): ?int
{
    $taskId = (string) ($task['id'] ?? '');
    $createdAt = strtotime((string) ($task['created_at'] ?? ''));
    if ($taskId === '' || $createdAt === false) {
        return null;
    }

    try {
        $history = json_read(HISTORY_FILE, ['entries' => []]);
    } catch (Throwable $exception) {
        return null;
    }
    $entries = is_array($history['entries'] ?? null) ? $history['entries'] : [];
    $transitions = [];
    foreach ($entries as $entry) {
        if ((int) ($entry['team'] ?? -1) !== $teamId) {
            continue;
        }
        $details = is_array($entry['details'] ?? null) ? $entry['details'] : [];
        if ((string) ($details['task'] ?? '') !== $taskId) {
            continue;
        }
        $oldState = (string) ($details['old_state'] ?? '');
        $newState = (string) ($details['new_state'] ?? '');
        if ($oldState === '' || $newState === '') {
            continue;
        }
        $ts = (int) ($entry['ts'] ?? 0);
        if ($ts <= 0) {
            $parsed = strtotime((string) ($entry['at'] ?? ''));
            if ($parsed !== false) {
                $ts = $parsed;
            }
        }
        if ($ts > 0) {
            $transitions[] = ['ts' => $ts, 'old' => $oldState, 'new' => $newState];
        }
    }
    if ($transitions === []) {
        return null;
    }
    usort($transitions, static fn(array $a, array $b): int => $a['ts'] <=> $b['ts']);

    $total = 0;
    $cursor = $createdAt;
    $current = $transitions[0]['old'];
    foreach ($transitions as $transition) {
        if ($transition['ts'] > $cursor) {
            if ($current === $state) {
                $total += $transition['ts'] - $cursor;
            }
            $cursor = $transition['ts'];
        }
        $current = $transition['new'];
    }
    $now = time();
    if ($now > $cursor && $current === $state) {
        $total += $now - $cursor;
    }
    return $total;
}

/**
 * Indicateurs disponibles dans le fichier des challenges pour la décision
 * du client : erreur au mini-quiz et durée du défi terminal.
 */
function client_decision_challenge_flags(int $teamId, array $task, string $storyId): array
{
    $flags = ['quiz_error' => false, 'terminal_slow' => false];
    $taskId = (string) ($task['id'] ?? '');
    try {
        $data = json_read(CHALLENGES_FILE, ['entries' => []]);
    } catch (Throwable $exception) {
        return $flags;
    }
    $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];

    $latestQuiz = null;
    $latestQuizAt = null;
    $latestTerminal = null;
    $latestTerminalAt = null;
    foreach ($entries as $entry) {
        if ((int) ($entry['team'] ?? 0) !== $teamId) {
            continue;
        }
        $type = (string) ($entry['type'] ?? 'quiz');
        $status = (string) ($entry['status'] ?? '');
        if (empty($entry['success_at']) || ($status !== 'success' && $status !== 'used')) {
            continue;
        }
        $successAt = strtotime((string) $entry['success_at']);
        if ($successAt === false) {
            continue;
        }
        if ($type === 'terminal') {
            if ((string) ($entry['story'] ?? '') === $storyId
                && ($latestTerminalAt === null || $successAt >= $latestTerminalAt)) {
                $latestTerminal = $entry;
                $latestTerminalAt = $successAt;
            }
            continue;
        }
        if ($taskId !== '' && (string) ($entry['task'] ?? '') === $taskId
            && ($latestQuizAt === null || $successAt >= $latestQuizAt)) {
            $latestQuiz = $entry;
            $latestQuizAt = $successAt;
        }
    }

    if ($latestQuiz !== null) {
        $flags['quiz_error'] = (int) ($latestQuiz['attempts'] ?? 0) > 0;
    }
    if ($latestTerminal !== null && $latestTerminalAt !== null) {
        $startedAt = (int) ($latestTerminal['started_at'] ?? 0);
        if ($startedAt > 0 && ($latestTerminalAt - $startedAt) > 300) {
            $flags['terminal_slow'] = true;
        }
    }
    return $flags;
}

/**
 * Tâche active rattachée à une User Story, sans recalcul de règle.
 */
function client_decision_task_for_story(array $team, array $story): ?array
{
    $storyId = (string) ($story['id'] ?? '');
    foreach (($team['tasks'] ?? []) as $candidate) {
        if ((string) ($candidate['us_id'] ?? '') === $storyId && empty($candidate['archived'])) {
            return $candidate;
        }
    }
    return null;
}

/**
 * Détaille les critères de risque exploitables pour une User Story.
 * Un indicateur absent ou inexploitable reste à false : il est ignoré.
 */
function client_decision_risk_flags(array $team, array $story): array
{
    $flags = [
        'temps_a_faire' => false,
        'erreur_quiz' => false,
        'temps_terminal' => false,
        'duree_traitement' => false,
        'description_courte' => false,
    ];

    $storyId = (string) ($story['id'] ?? '');
    $teamId = (int) ($team['team'] ?? 0);
    $task = client_decision_task_for_story($team, $story);
    if ($task === null) {
        return $flags;
    }

    $secondsInTodo = client_decision_seconds_in_state($teamId, $task, 'a_faire');
    if ($secondsInTodo !== null) {
        $flags['temps_a_faire'] = $secondsInTodo > 90;
    }

    $challengeFlags = client_decision_challenge_flags($teamId, $task, $storyId);
    $flags['erreur_quiz'] = $challengeFlags['quiz_error'];
    $flags['temps_terminal'] = $challengeFlags['terminal_slow'];

    // Le champ « heures » est une estimation, pas une durée réelle : on
    // utilise les horodatages réels de création et de fin de tâche.
    $createdAt = strtotime((string) ($task['created_at'] ?? ''));
    $completedAt = strtotime((string) ($task['updated_at'] ?? ''));
    if ($createdAt !== false && $completedAt !== false && $completedAt >= $createdAt) {
        $flags['duree_traitement'] = ($completedAt - $createdAt) < 30;
    }

    if (isset($task['description']) && is_string($task['description'])) {
        $flags['description_courte'] = mb_strlen($task['description']) < 15;
    }

    return $flags;
}

/**
 * Indique si au moins un critère de risque est présent.
 */
function client_decision_has_risk(array $team, array $story): bool
{
    foreach (client_decision_risk_flags($team, $story) as $detected) {
        if ($detected) {
            return true;
        }
    }
    return false;
}

/**
 * Libellés stables des cinq critères de risque.
 */
function client_decision_risk_labels(): array
{
    return [
        'temps_a_faire' => 'Séjour prolongé à l’état « À faire » (> 90 s)',
        'erreur_quiz' => 'Erreur au mini-quiz',
        'temps_terminal' => 'Défi terminal trop long (> 300 s)',
        'duree_traitement' => 'Durée de traitement trop courte (< 30 s)',
        'description_courte' => 'Description trop courte (< 15 caractères)',
    ];
}

/**
 * Fige, dans la réponse client, les métadonnées utiles aux statistiques.
 * Les valeurs sont celles constatées au moment de la décision : aucun
 * recalcul ultérieur n'est effectué.
 */
function client_feedback_attach_risk(array $feedback, array $team, array $story, array $riskFlags): array
{
    $labels = client_decision_risk_labels();
    $task = client_decision_task_for_story($team, $story);
    $activeKeys = [];
    $activeLabels = [];
    foreach ($riskFlags as $key => $detected) {
        if (!$detected) {
            continue;
        }
        $activeKeys[] = (string) $key;
        $activeLabels[] = $labels[$key] ?? (string) $key;
    }

    $feedback['task_id'] = $task !== null ? (string) ($task['id'] ?? '') : null;
    $feedback['task_titre'] = $task !== null ? (string) ($task['titre'] ?? '') : null;
    $feedback['story_id'] = (string) ($story['id'] ?? '');
    $feedback['story_titre'] = (string) ($story['titre'] ?? '');
    $feedback['risk'] = $activeKeys !== [];
    $feedback['risk_flags'] = $activeKeys;
    $feedback['risk_labels'] = $activeLabels;
    return $feedback;
}

/**
 * Enregistre une décision du client dans l'historique de l'équipe.
 * L'identifiant story#tentative rend l'enregistrement idempotent : un
 * rafraîchissement, une reprise ou un doublon réseau ne crée jamais deux
 * lignes pour la même décision.
 */
function client_decision_record(array &$team, array $story, string $decision, string $decidedBy): void
{
    if ($decision !== 'validee' && $decision !== 'refusee') {
        return;
    }
    $storyId = (string) ($story['id'] ?? '');
    if ($storyId === '') {
        return;
    }

    $feedback = is_array($story['client_feedback'] ?? null) ? $story['client_feedback'] : [];
    $attempt = max(1, (int) ($feedback['attempt'] ?? 1));
    $id = $storyId . '#' . $attempt;

    $records = is_array($team['client_decisions'] ?? null) ? $team['client_decisions'] : [];
    foreach ($records as $record) {
        if ((string) ($record['id'] ?? '') === $id) {
            return;
        }
    }

    $flags = [];
    foreach ((array) ($feedback['risk_flags'] ?? []) as $flag) {
        if (is_string($flag) && $flag !== '') {
            $flags[] = $flag;
        }
    }
    $labels = [];
    foreach ((array) ($feedback['risk_labels'] ?? []) as $label) {
        if (is_string($label) && $label !== '') {
            $labels[] = $label;
        }
    }

    $records[] = [
        'id' => $id,
        'attempt' => $attempt,
        'task_id' => $feedback['task_id'] ?? null,
        'task_titre' => $feedback['task_titre'] ?? null,
        'story_id' => $storyId,
        'story_titre' => (string) ($feedback['story_titre'] ?? ($story['titre'] ?? '')),
        'developer' => (string) ($feedback['developer'] ?? ''),
        'risk' => !empty($feedback['risk']),
        'risk_flags' => $flags,
        'risk_labels' => $labels,
        'decision' => $decision,
        'decided_at' => (string) ($feedback['decided_at'] ?? iso_now()),
        'decided_by' => $decidedBy,
    ];
    $team['client_decisions'] = array_values($records);
}

/**
 * Agrège les décisions enregistrées. Les anciennes équipes sans historique
 * renvoient des compteurs à zéro et des taux « N/A » (null), sans erreur.
 */
function client_decision_stats(array $team): array
{
    $developers = [];
    foreach (($team['members'] ?? []) as $member) {
        $login = (string) ($member['login'] ?? '');
        if ($login === '') {
            continue;
        }
        $developers[$login] = [
            'login' => $login,
            'role' => (string) ($member['role'] ?? ''),
            'role_label' => role_label((string) ($member['role'] ?? '')),
            'total' => 0,
            'sans_risque' => 0,
            'avec_risque' => 0,
            'acceptations' => 0,
            'refus' => 0,
            'refus_sans_risque' => 0,
            'refus_avec_risque' => 0,
        ];
    }

    $global = [
        'total' => 0,
        'sans_risque' => 0,
        'avec_risque' => 0,
        'acceptations' => 0,
        'refus' => 0,
        'refus_sans_risque' => 0,
        'refus_avec_risque' => 0,
    ];

    $risks = [];
    $seen = [];
    $records = is_array($team['client_decisions'] ?? null) ? $team['client_decisions'] : [];
    foreach ($records as $record) {
        if (!is_array($record)) {
            continue;
        }
        $id = (string) ($record['id'] ?? '');
        $decision = (string) ($record['decision'] ?? '');
        if ($id === '' || isset($seen[$id]) || ($decision !== 'validee' && $decision !== 'refusee')) {
            continue;
        }
        $seen[$id] = true;

        $developer = (string) ($record['developer'] ?? '');
        if ($developer === '') {
            $developer = 'non_identifie';
        }
        if (!isset($developers[$developer])) {
            $developers[$developer] = [
                'login' => $developer,
                'role' => '',
                'role_label' => 'Développeur non identifié',
                'total' => 0,
                'sans_risque' => 0,
                'avec_risque' => 0,
                'acceptations' => 0,
                'refus' => 0,
                'refus_sans_risque' => 0,
                'refus_avec_risque' => 0,
            ];
        }

        $risk = !empty($record['risk']);
        $refused = $decision === 'refusee';

        $global['total']++;
        $developers[$developer]['total']++;
        if ($risk) {
            $global['avec_risque']++;
            $developers[$developer]['avec_risque']++;
        } else {
            $global['sans_risque']++;
            $developers[$developer]['sans_risque']++;
        }
        if ($refused) {
            $global['refus']++;
            $developers[$developer]['refus']++;
            if ($risk) {
                $global['refus_avec_risque']++;
                $developers[$developer]['refus_avec_risque']++;
            } else {
                $global['refus_sans_risque']++;
                $developers[$developer]['refus_sans_risque']++;
            }
        } else {
            $global['acceptations']++;
            $developers[$developer]['acceptations']++;
        }

        if ($risk) {
            $riskFlags = [];
            foreach ((array) ($record['risk_flags'] ?? []) as $flag) {
                if (is_string($flag) && $flag !== '') {
                    $riskFlags[] = $flag;
                }
            }
            $riskLabels = [];
            foreach ((array) ($record['risk_labels'] ?? []) as $label) {
                if (is_string($label) && $label !== '') {
                    $riskLabels[] = $label;
                }
            }
            $risks[] = [
                'developer' => $developer,
                'developer_label' => $developers[$developer]['role_label'] !== ''
                    ? $developer . ' (' . $developers[$developer]['role_label'] . ')'
                    : $developer,
                'attempt' => max(1, (int) ($record['attempt'] ?? 1)),
                'story_id' => (string) ($record['story_id'] ?? ''),
                'story_titre' => (string) ($record['story_titre'] ?? ''),
                'task_id' => $record['task_id'] ?? null,
                'task_titre' => $record['task_titre'] ?? null,
                'risk_flags' => $riskFlags,
                'risk_labels' => $riskLabels,
                'decision' => $decision,
                'decided_at' => (string) ($record['decided_at'] ?? ''),
            ];
        }
    }

    $rate = static fn(int $numerator, int $denominator): ?int => $denominator > 0
        ? (int) round($numerator / $denominator * 100)
        : null;

    $global['pct_sans_risque'] = $rate($global['sans_risque'], $global['total']);
    $global['pct_avec_risque'] = $rate($global['avec_risque'], $global['total']);
    $global['taux_acceptation'] = $rate($global['acceptations'], $global['total']);
    $global['taux_refus'] = $rate($global['refus'], $global['total']);

    $developerRows = [];
    foreach ($developers as $row) {
        // Une ligne par développeur ; les autres rôles n'apparaissent que
        // s'ils ont réellement porté une décision (cas d'un échange de rôle).
        if ($row['total'] === 0 && $row['role'] !== 'DEVELOPER') {
            continue;
        }
        $row['taux_refus'] = $rate($row['refus'], $row['total']);
        $row['taux_refus_sans_risque'] = $rate($row['refus_sans_risque'], $row['sans_risque']);
        $row['taux_refus_avec_risque'] = $rate($row['refus_avec_risque'], $row['avec_risque']);
        $developerRows[] = $row;
    }

    return [
        'total' => $global['total'],
        'sans_risque' => $global['sans_risque'],
        'avec_risque' => $global['avec_risque'],
        'acceptations' => $global['acceptations'],
        'refus' => $global['refus'],
        'pct_sans_risque' => $global['pct_sans_risque'],
        'pct_avec_risque' => $global['pct_avec_risque'],
        'taux_acceptation' => $global['taux_acceptation'],
        'taux_refus' => $global['taux_refus'],
        'developers' => $developerRows,
        'risques' => $risks,
    ];
}

/**
 * Génère une réponse client pour la tentative en cours.
 * Le scénario est aléatoire ; seule la seconde tentative consécutive à un
 * refus négatif est forcée en position positive.
 */
function client_feedback_generate(array $story, string $developer, bool $forcePositive, bool $riskDetected = false): array
{
    $scenarios = client_feedback_scenarios();
    $previous = is_array($story['client_feedback'] ?? null) ? $story['client_feedback'] : null;
    $attempt = $previous !== null ? (int) ($previous['attempt'] ?? 0) + 1 : 1;
    $clientName = $forcePositive && $previous !== null
        ? (string) ($previous['client_name'] ?? '')
        : '';

    if ($forcePositive) {
        $scenarioKey = 'satisfaction';
        $definition = $scenarios[$scenarioKey];
        $variants = client_feedback_followup_variants();
    } else {
        $position = client_decision_refuse($riskDetected) ? 'negative' : 'positive';
        $candidates = [];
        foreach ($scenarios as $key => $scenario) {
            if (($scenario['position'] ?? '') === $position) {
                $candidates[] = $key;
            }
        }
        $scenarioKey = (string) $candidates[array_rand($candidates)];
        $definition = $scenarios[$scenarioKey];
        $variants = $definition['variants'];
    }

    if ($clientName === '') {
        $names = client_feedback_names();
        $clientName = $names[array_rand($names)];
    }

    $text = strtr($variants[array_rand($variants)], [
        '{client}' => $clientName,
        '{developer}' => '@' . $developer,
        '{story}' => (string) $story['id'],
        '{title}' => (string) $story['titre'],
        '{story_label}' => "'" . (string) $story['id'] . ' — ' . (string) $story['titre'] . "'",
    ]);

    return [
        'attempt' => $attempt,
        'developer' => $developer,
        'client_name' => $clientName,
        'scenario' => $scenarioKey,
        'scenario_label' => $definition['label'],
        'position' => $definition['position'],
        'tone' => $definition['tone'],
        'message' => $text,
        'followup' => $forcePositive,
        'consulted' => false,
        'consulted_at' => null,
        'consulted_by' => null,
        'decision' => null,
        'decided_at' => null,
        'decided_by' => null,
        'created_at' => iso_now(),
    ];
}

/**
 * Retrouve un Developer crédible pour une réponse générée sur une ancienne
 * User Story déjà en attente de validation, avant l'introduction du dispositif.
 */
function client_feedback_developer_for_story(array $team, array $story): string
{
    $developers = [];
    foreach ($team['members'] as $member) {
        if (($member['role'] ?? '') === 'DEVELOPER') {
            $developers[] = (string) $member['login'];
        }
    }

    $tasks = array_values(array_filter(
        $team['tasks'],
        static fn(array $task): bool => ($task['us_id'] ?? '') === ($story['id'] ?? '') && empty($task['archived'])
    ));
    for ($i = count($tasks) - 1; $i >= 0; $i--) {
        foreach (['assigne', 'updated_by'] as $field) {
            $candidate = (string) ($tasks[$i][$field] ?? '');
            if ($candidate !== '' && in_array($candidate, $developers, true)) {
                return $candidate;
            }
        }
    }
    if ($developers !== []) {
        return $developers[0];
    }
    throw new AppError('Aucun développeur ne peut être associé à cette réponse client.');
}

/**
 * Les Developers déclarent une User Story prête à être validée.
 */
function action_declare_ready(array $user, mixed $storyId): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');

    $now = time();

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($storyId, $now, $user): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        $story = $team['stories'][$index];
        if (empty($story['selectionnee'])) {
            throw new AppError('La User Story doit être sélectionnée dans le Sprint.');
        }
        if (($story['etat'] ?? '') === 'validee') {
            throw new AppError('La User Story est déjà validée.');
        }
        if (($story['etat'] ?? '') === 'a_valider') {
            throw new AppError('La User Story est déjà en attente de validation.');
        }

        $tasks = array_filter($team['tasks'], static fn(array $t): bool => ($t['us_id'] ?? '') === $storyId && empty($t['archived']));
        if ($tasks === []) {
            throw new AppError('Ajoutez au moins une tâche technique avant de déclarer la User Story prête.');
        }
        $handlers = story_task_handlers($team, $storyId);
        if ($handlers !== [] && !in_array((string) $user['login'], $handlers, true)) {
            throw new AppError('Vous n’êtes pas l’auteur de la tâche rattachée à cette User Story.', 403);
        }
        foreach ($tasks as $task) {
            if (($task['etat'] ?? '') !== 'termine') {
                throw new AppError('Toutes les tâches de la User Story doivent être terminées.');
            }
        }
        if (!challenge_terminal_success($user, $storyId)) {
            throw new AppError('Défi terminal requis avant de déclarer la User Story prête.', 409);
        }

        $poLogin = null;
        foreach ($team['members'] as $member) {
            if (($member['role'] ?? '') === 'PO') {
                $poLogin = (string) $member['login'];
                break;
            }
        }
        if ($poLogin === null) {
            throw new AppError('Product Owner introuvable dans l’équipe.');
        }

        $riskFlags = client_decision_risk_flags($team, $story);
        $feedback = client_feedback_generate(
            $story,
            (string) $user['login'],
            !empty($story['client_last_refusal_negative']),
            in_array(true, $riskFlags, true)
        );
        $feedback = client_feedback_attach_risk($feedback, $team, $story, $riskFlags);
        $history = is_array($story['client_feedback_history'] ?? null) ? $story['client_feedback_history'] : [];
        if (is_array($story['client_feedback'] ?? null)) {
            $history[] = $story['client_feedback'];
            $history = array_slice($history, -10);
        }

        $team['stories'][$index]['client_feedback_history'] = $history;
        $team['stories'][$index]['client_feedback'] = $feedback;
        $team['stories'][$index]['client_last_refusal_negative'] = false;
        $team['stories'][$index]['etat'] = 'a_valider';
        $team['stories'][$index]['refus_commentaire'] = null;

        $number = (int) ($team['next_message_number'] ?? 1);
        $message = [
            'id' => 'M' . $number,
            'author' => (string) $user['login'],
            'at' => iso_now(),
            'ts' => $now,
            'text' => 'Bonjour @' . $poLogin . ', le développeur @' . $user['login']
                . ' a déclaré la User Story ' . $storyId
                . ' prête à valider. Elle est en attente de votre validation dans le Product Backlog.',
        ];
        $team['messages'][] = $message;
        $team['next_message_number'] = $number + 1;
        if (count($team['messages']) > 300) {
            $team['messages'] = array_slice($team['messages'], -300);
        }

        return ['titre' => $story['titre'], 'tasks' => count($tasks), 'message' => $message['id']];
    }, 'DEVELOPER');

    challenge_terminal_success($user, $storyId, true);
    history_add($teamId, $user['login'], 'story_ready', 'User Story déclarée prête à valider : ' . $storyId . ' — ' . $result['titre'], [
        'story' => $storyId,
        'tasks' => $result['tasks'],
    ]);
    return ['message' => 'User Story déclarée prête à être validée par le PO.'];
}

/**
 * Consultation de la réponse client par le PO.
 * La réponse est générée une seule fois par tentative et reste inchangée.
 */
function action_consult_client_feedback(array $user, mixed $storyId): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');

    [, $result] = mutate_student_team($user, function (array &$team) use ($storyId, $user): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        if (($team['stories'][$index]['etat'] ?? '') !== 'a_valider') {
            throw new AppError('La réponse du client est disponible uniquement pour une User Story en attente de validation.');
        }

        $feedback = $team['stories'][$index]['client_feedback'] ?? null;
        if (!is_array($feedback)) {
            $developer = client_feedback_developer_for_story($team, $team['stories'][$index]);
            $riskFlags = client_decision_risk_flags($team, $team['stories'][$index]);
            $feedback = client_feedback_generate(
                $team['stories'][$index],
                $developer,
                false,
                in_array(true, $riskFlags, true)
            );
            $feedback = client_feedback_attach_risk($feedback, $team, $team['stories'][$index], $riskFlags);
            $team['stories'][$index]['client_feedback'] = $feedback;
            if (!isset($team['stories'][$index]['client_feedback_history'])) {
                $team['stories'][$index]['client_feedback_history'] = [];
            }
        }
        if (empty($feedback['consulted'])) {
            $team['stories'][$index]['client_feedback']['consulted'] = true;
            $team['stories'][$index]['client_feedback']['consulted_at'] = iso_now();
            $team['stories'][$index]['client_feedback']['consulted_by'] = (string) $user['login'];
            $feedback = $team['stories'][$index]['client_feedback'];
        }

        return ['feedback' => $feedback];
    }, 'PO');

    return ['feedback' => $result['feedback']];
}

/**
 * Validation finale par le PO.
 */
function action_validate_story(array $user, mixed $storyId): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');

    [$teamId] = mutate_student_team($user, function (array &$team) use ($storyId, $user): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        if (($team['stories'][$index]['etat'] ?? '') !== 'a_valider') {
            throw new AppError('Seule une User Story en attente de validation peut être validée.');
        }

        $feedback = $team['stories'][$index]['client_feedback'] ?? null;
        if (!is_array($feedback)) {
            throw new AppError('La réponse du client doit être générée avant toute décision.');
        }
        if (empty($feedback['consulted'])) {
            throw new AppError('Consultez d’abord la réponse du client avant de prendre une décision.');
        }
        if (($feedback['position'] ?? '') !== 'positive') {
            throw new AppError('Le client a une position négative : la User Story ne peut pas être validée.');
        }

        $team['stories'][$index]['client_feedback']['decision'] = 'validee';
        $team['stories'][$index]['client_feedback']['decided_at'] = iso_now();
        $team['stories'][$index]['client_feedback']['decided_by'] = (string) $user['login'];
        $team['stories'][$index]['client_last_refusal_negative'] = false;
        $team['stories'][$index]['etat'] = 'validee';
        $team['stories'][$index]['validee_at'] = iso_now();
        $team['stories'][$index]['refus_commentaire'] = null;
        client_decision_record($team, $team['stories'][$index], 'validee', (string) $user['login']);
        return ['titre' => $team['stories'][$index]['titre']];
    }, 'PO');

    history_add($teamId, $user['login'], 'story_validated', 'User Story validée par le PO : ' . $storyId, [
        'story' => $storyId,
    ]);
    return ['message' => 'User Story validée.'];
}

/**
 * Refus de validation par le PO, avec commentaire.
 */
function action_refuse_story(array $user, mixed $storyId, mixed $comment): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');
    $comment = require_text($comment, 5, 500, 'Commentaire de refus');
    $now = time();

    [$teamId] = mutate_student_team($user, function (array &$team) use ($storyId, $comment, $now, $user): array {
        $index = team_story_index($team, $storyId);
        if ($index === null) {
            throw new AppError('User Story introuvable.');
        }
        if (($team['stories'][$index]['etat'] ?? '') !== 'a_valider') {
            throw new AppError('Seule une User Story en attente de validation peut être refusée.');
        }

        $feedback = $team['stories'][$index]['client_feedback'] ?? null;
        if (!is_array($feedback)) {
            throw new AppError('La réponse du client doit être générée avant toute décision.');
        }
        if (empty($feedback['consulted'])) {
            throw new AppError('Consultez d’abord la réponse du client avant de prendre une décision.');
        }
        if (($feedback['position'] ?? '') !== 'negative') {
            throw new AppError('Le client a une position positive : la User Story ne peut pas être refusée.');
        }

        $developer = (string) ($feedback['developer'] ?? '');
        if ($developer === '') {
            throw new AppError('Le développeur associé à la réponse client est introuvable.');
        }

        $team['stories'][$index]['client_feedback']['decision'] = 'refusee';
        $team['stories'][$index]['client_feedback']['decided_at'] = iso_now();
        $team['stories'][$index]['client_feedback']['decided_by'] = (string) $user['login'];
        $team['stories'][$index]['client_last_refusal_negative'] = true;
        $team['stories'][$index]['etat'] = 'refusee';
        $team['stories'][$index]['refusee_at'] = iso_now();
        $team['stories'][$index]['refus_commentaire'] = $comment;
        unset($team['stories'][$index]['moderation']['refus_commentaire']);
        client_decision_record($team, $team['stories'][$index], 'refusee', (string) $user['login']);

        $number = (int) ($team['next_message_number'] ?? 1);
        $message = [
            'id' => 'M' . $number,
            'author' => (string) $user['login'],
            'at' => iso_now(),
            'ts' => $now,
            'text' => 'Bonjour @' . $developer . ', le Product Owner a refusé la User Story ' . $storyId
                . ' à la suite du retour négatif du client. Consultez le motif du refus, puis cliquez de nouveau sur « Prête à valider » pour reprendre le cycle de validation technique et résoudre le problème signalé.',
        ];
        $team['messages'][] = $message;
        $team['next_message_number'] = $number + 1;
        if (count($team['messages']) > 300) {
            $team['messages'] = array_slice($team['messages'], -300);
        }

        return ['titre' => $team['stories'][$index]['titre'], 'message' => $message['id']];
    }, 'PO');

    history_add($teamId, $user['login'], 'story_refused', 'User Story refusée par le PO : ' . $storyId . ' — ' . $comment, [
        'story' => $storyId,
        'comment' => $comment,
    ]);
    return ['message' => 'User Story refusée ; l’équipe peut poursuivre le travail.'];
}

// ===========================================================================
// Tâches techniques et Kanban
// ===========================================================================

/** Fenêtre (en secondes) d'alerte pour une modification concurrente. */
const TASK_RECENT_EDIT_SECONDS = 30;

/**
 * Retourne le dernier Developer (autre que $login) ayant touché la tâche
 * récemment, ou null. Sert uniquement à avertir, jamais à bloquer.
 */
function task_recent_editor(array $team, array $task, string $login): ?array
{
    $editor = (string) ($task['updated_by'] ?? '');
    if ($editor === '' || $editor === $login) {
        return null;
    }
    $member = team_member($team, $editor);
    if ($member === null || ($member['role'] ?? '') !== 'DEVELOPER') {
        return null;
    }
    $timestamp = strtotime((string) ($task['updated_at'] ?? ''));
    if ($timestamp === false) {
        return null;
    }
    $age = time() - $timestamp;
    if ($age < 0 || $age > TASK_RECENT_EDIT_SECONDS) {
        return null;
    }
    return ['login' => $editor, 'age' => $age];
}

/**
 * Texte d'avertissement ajouté au message de succès, ou chaîne vide.
 */
function task_recent_editor_notice(?array $editor): string
{
    if (!is_array($editor)) {
        return '';
    }
    $when = (int) $editor['age'] <= 1 ? 'à l’instant' : 'il y a ' . (int) $editor['age'] . ' s';
    return ' Attention : ' . (string) $editor['login'] . ' vient de travailler sur cette tâche (' . $when . '). Envoyez-lui un message dans le chat pour vous coordonner.';
}

/**
 * Normalise un titre de tâche pour la détection des doublons.
 */
function task_title_key(string $title): string
{
    $title = mb_strtolower(trim($title));
    $title = preg_replace('/\s+/u', ' ', $title) ?? $title;
    return $title;
}

/**
 * Indique si une tâche portant ce titre existe déjà pour cette User Story.
 * Le contrôle porte sur toutes les tâches, y compris archivées.
 */
function task_title_exists(array $team, string $storyId, string $title): bool
{
    $key = task_title_key($title);
    foreach ($team['tasks'] as $task) {
        if ((string) ($task['us_id'] ?? '') !== $storyId) {
            continue;
        }
        // Le titre courant et la clé consommée à la création sont tous deux
        // pris en compte : une modification ultérieure du titre ne libère pas
        // l'ancien titre pour une nouvelle création.
        $consumedKey = (string) ($task['titre_key'] ?? '');
        if ($consumedKey === '') {
            $consumedKey = task_title_key((string) ($task['titre'] ?? ''));
        }
        if (task_title_key((string) ($task['titre'] ?? '')) === $key || $consumedKey === $key) {
            return true;
        }
    }
    return false;
}

/**
 * Indique si la User Story possède déjà une tâche, y compris archivée.
 * Règle métier : une seule création de tâche par User Story.
 */
function task_exists_for_story(array $team, string $storyId): bool
{
    foreach ($team['tasks'] as $task) {
        if ((string) ($task['us_id'] ?? '') === $storyId) {
            return true;
        }
    }
    return false;
}

/**
 * Retourne les logins des Developers ayant traité (placé en « Terminé »)
 * au moins une tâche rattachée à la User Story. Sert à réserver la
 * déclaration « Prête à valider » à ces Developers.
 */
function story_task_handlers(array $team, string $storyId): array
{
    $handlers = [];
    foreach ($team['tasks'] as $task) {
        if ((string) ($task['us_id'] ?? '') !== $storyId) {
            continue;
        }
        if ((string) ($task['etat'] ?? '') !== 'termine') {
            continue;
        }
        // Priorité : updated_by (qui a placé en Terminé), puis assigne, puis created_by.
        // On ne retient le handler que s'il est encore Developer actif : ainsi, si le
        // Developer original a changé de rôle, la tâche devient « sans propriétaire »
        // et tout Developer courant peut déclarer la User Story prête.
        $handler = null;
        foreach (['updated_by', 'assigne', 'created_by'] as $field) {
            $login = (string) ($task[$field] ?? '');
            if ($login !== '' && $login !== 'systeme') {
                $member = team_member($team, $login);
                if ($member !== null && ($member['role'] ?? '') === 'DEVELOPER') {
                    $handler = $login;
                    break;
                }
            }
        }
        if ($handler !== null) {
            $handlers[$handler] = true;
        }
    }
    return array_keys($handlers);
}

/**
 * Indique si un Developer a des tâches terminées non déclarées prêtes
 * (User Story non encore en a_valider/validee) pour lesquelles il est
 * le handler. Un tel Developer ne doit pas échanger de rôle.
 */
function developer_is_affected(array $team, string $login): bool
{
    foreach ($team['stories'] as $story) {
        if (in_array($story['etat'] ?? '', ['a_valider', 'validee'], true)) {
            continue;
        }
        $storyId = (string) ($story['id'] ?? '');
        $handlers = story_task_handlers($team, $storyId);
        if (in_array($login, $handlers, true)) {
            return true;
        }
    }
    return false;
}

/**
 * Valide un membre affecté à une tâche.
 * Une tâche ne peut être affectée qu'à un Developer actuel de l'équipe.
 */
function task_assignee(array $team, mixed $value): ?string
{
    $login = clean_text($value);
    if ($login === '') {
        throw new AppError('Un membre Developer doit être affecté à la tâche.');
    }
    $member = team_member($team, $login);
    if ($member === null) {
        throw new AppError('Le membre affecté n’appartient pas à l’équipe.');
    }
    if (($member['role'] ?? '') !== 'DEVELOPER') {
        throw new AppError('Une tâche ne peut être affectée qu’à un membre ayant le rôle Developer.');
    }
    return $login;
}

/**
 * Retire les affectations des tâches actives qui ne ciblent plus un Developer.
 * Les tâches sont conservées ; l'ancienne affectation est tracée dans l'historique.
 */
function task_clear_invalid_assignments(array &$team): array
{
    $cleared = [];
    foreach ($team['tasks'] as $index => $task) {
        if (!empty($task['archived']) || ($task['etat'] ?? '') === 'termine') {
            // Les tâches terminées conservent leur affectation (traçabilité) ;
            // seules les tâches actives sont désaffectées lors d'un changement de rôle.
            continue;
        }
        $login = trim((string) ($task['assigne'] ?? ''));
        if ($login === '') {
            continue;
        }
        $member = team_member($team, $login);
        if ($member !== null && ($member['role'] ?? '') === 'DEVELOPER') {
            continue;
        }
        $cleared[] = [
            'task' => (string) ($task['id'] ?? ''),
            'login' => $login,
            'role' => $member !== null ? (string) $member['role'] : null,
        ];
        $team['tasks'][$index]['assigne'] = null;
        $team['tasks'][$index]['updated_at'] = iso_now();
        $team['tasks'][$index]['updated_by'] = 'systeme';
    }
    return $cleared;
}

/**
 * Création d'une tâche technique par un Developer.
 */
function action_create_task(array $user, array $input): array
{
    $title = require_text($input['titre'] ?? '', 3, 120, 'Titre de la tâche');
    $description = clean_text($input['description'] ?? '');
    if (mb_strlen($description) < 10) {
        throw new AppError('Description de tâche : 10 caractères minimum.');
    }
    if (mb_strlen($description) > 500) {
        throw new AppError('Description de tâche : 500 caractères maximum.');
    }
    $storyId = require_text($input['us_id'] ?? '', 3, 10, 'User Story');
    $hours = require_decimal($input['heures'] ?? 0, 0.5, TASK_MAX_HOURS, 'Estimation de la tâche');
    $rawState = (string) ($input['etat'] ?? 'a_faire');
    if ($rawState === 'termine') {
        throw new AppError('Une tâche ne peut pas être créée directement au statut « Terminé ». Faites-la progresser depuis le Kanban.', 409);
    }
    $state = require_choice($rawState, ['a_faire', 'en_cours', 'bloque'], 'État Kanban');
    $blocking = clean_text($input['blocage'] ?? '');
    if ($state === 'bloque' && mb_strlen($blocking) < 3) {
        throw new AppError('Une tâche bloquée doit être accompagnée d’une explication.');
    }
    if ($state !== 'bloque') {
        $blocking = '';
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($title, $description, $storyId, $hours, $state, $blocking, $input, $user): array {
        $storyIndex = team_story_index($team, $storyId);
        if ($storyIndex === null) {
            throw new AppError('User Story introuvable.');
        }
        if (empty($team['stories'][$storyIndex]['selectionnee'])) {
            throw new AppError('Les tâches doivent être rattachées à une User Story sélectionnée dans le Sprint.');
        }
        if (($team['stories'][$storyIndex]['etat'] ?? '') === 'validee') {
            throw new AppError('Impossible d’ajouter une tâche à une User Story déjà validée.');
        }
        if (task_exists_for_story($team, $storyId)) {
            throw new AppError('Cette User Story est déjà associée à une tâche. Un seul développeur peut créer la tâche.', 409);
        }
        if (($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
            // Une nouvelle tâche signifie que la Definition of Done n'est plus
            // satisfaite : la User Story repasse en travail avant validation.
            $team['stories'][$storyIndex]['etat'] = 'a_faire';
        }
        if (task_title_exists($team, $storyId, $title)) {
            throw new AppError('Une tâche portant ce titre existe déjà pour cette User Story.', 409);
        }
        $assignee = task_assignee($team, $input['assigne'] ?? '');
        $number = (int) ($team['next_task_number'] ?? 1);
        $task = [
            'id' => 'T' . $number,
            'us_id' => $storyId,
            'titre' => $title,
            'titre_key' => task_title_key($title),
            'description' => $description,
            'heures' => $hours,
            'assigne' => $assignee,
            'etat' => $state,
            'blocage' => $blocking !== '' ? $blocking : null,
            'archived' => false,
            'created_at' => iso_now(),
            'created_by' => (string) $user['login'],
            'updated_at' => iso_now(),
            'updated_by' => (string) $user['login'],
            'blocage_by' => $state === 'bloque' ? (string) $user['login'] : null,
        ];
        $team['tasks'][] = $task;
        $team['next_task_number'] = $number + 1;

        if ($state === 'en_cours' && in_array($team['stories'][$storyIndex]['etat'] ?? '', ['a_faire', 'refusee'], true)) {
            $team['stories'][$storyIndex]['etat'] = 'en_cours';
        }
        return ['task' => $task];
    }, 'DEVELOPER');

    history_add($teamId, $user['login'], 'task_create', 'Création de la tâche ' . $result['task']['id'] . ' sur ' . $storyId . ' : ' . $title, [
        'task' => $result['task']['id'],
        'story' => $storyId,
        'hours' => $hours,
    ]);
    if ($state === 'bloque') {
        history_add($teamId, $user['login'], 'task_block', 'Blocage signalé sur ' . $result['task']['id'] . ' : ' . $blocking, [
            'task' => $result['task']['id'],
        ]);
    }
    return ['message' => 'Tâche ' . $result['task']['id'] . ' créée.'];
}

/**
 * Met à jour une tâche existante.
 */
function action_update_task(array $user, array $input): array
{
    $taskId = require_text($input['task_id'] ?? '', 1, 10, 'Tâche');
    $title = require_text($input['titre'] ?? '', 3, 120, 'Titre de la tâche');
    $description = clean_text($input['description'] ?? '');
    if (mb_strlen($description) < 10) {
        throw new AppError('Description de tâche : 10 caractères minimum.');
    }
    if (mb_strlen($description) > 500) {
        throw new AppError('Description de tâche : 500 caractères maximum.');
    }
    $hours = require_decimal($input['heures'] ?? 0, 0.5, TASK_MAX_HOURS, 'Estimation de la tâche');
    $state = require_choice($input['etat'] ?? 'a_faire', ['a_faire', 'en_cours', 'bloque', 'termine'], 'État Kanban');
    $blocking = clean_text($input['blocage'] ?? '');
    if ($state === 'bloque' && mb_strlen($blocking) < 3) {
        throw new AppError('Une tâche bloquée doit être accompagnée d’une explication.');
    }
    if ($state !== 'bloque') {
        $blocking = '';
    }

    $challengeDetails = null;
    if ($state === 'termine') {
        $team = load_team((int) $user['team']);
        $index = team_task_index($team, $taskId);
        if ($index === null || !empty($team['tasks'][$index]['archived']) || (string) ($team['tasks'][$index]['etat'] ?? '') === 'termine') {
            throw new AppError('Tâche introuvable, archivée ou déjà terminée.', 404);
        }
        if (!challenge_success_exists($user, $taskId)) {
            throw new AppError('Challenge technique requis.', 409);
        }
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($taskId, $title, $description, $hours, $state, $blocking, $input, $user): array {
        $index = team_task_index($team, $taskId);
        if ($index === null || !empty($team['tasks'][$index]['archived'])) {
            throw new AppError('Tâche introuvable ou archivée.');
        }
        $task = &$team['tasks'][$index];
        $oldState = (string) $task['etat'];
        if ($oldState === 'termine') {
            throw new AppError('Tâche terminée : elle est immuable. Conservez-la telle quelle ou archivez-la.');
        }
        $recentEditor = task_recent_editor($team, $task, (string) $user['login']);
        $oldTitle = (string) ($task['titre'] ?? '');
        $oldDescription = (string) ($task['description'] ?? '');
        $oldBlocking = $task['blocage'] ?? null;
        $oldAssignee = $task['assigne'] ?? null;
        $oldHours = (float) ($task['heures'] ?? 0);
        $assignee = task_assignee($team, $input['assigne'] ?? '');
        $task['titre'] = $title;
        $task['description'] = $description;
        $task['heures'] = $hours;
        $task['assigne'] = $assignee;
        $task['etat'] = $state;
        $task['blocage'] = $blocking !== '' ? $blocking : null;
        $task['blocage_by'] = $state === 'bloque' ? (string) $user['login'] : null;
        $task['updated_at'] = iso_now();
        $task['updated_by'] = (string) $user['login'];
        if ($oldTitle !== $title) {
            unset($task['moderation']['titre']);
        }
        if ($oldDescription !== $description) {
            unset($task['moderation']['description']);
        }
        if (($oldBlocking ?? '') !== ($task['blocage'] ?? '')) {
            unset($task['moderation']['blocage']);
        }

        $storyIndex = team_story_index($team, (string) $task['us_id']);
        if ($storyIndex !== null) {
            if ($oldState === 'termine' && $state !== 'termine' && ($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
                $team['stories'][$storyIndex]['etat'] = 'a_faire';
            }
            if ($state === 'en_cours' && in_array($team['stories'][$storyIndex]['etat'] ?? '', ['a_faire', 'refusee'], true)) {
                $team['stories'][$storyIndex]['etat'] = 'en_cours';
            }
        }
        return [
            'old_state' => $oldState,
            'old_title' => $oldTitle,
            'old_description' => $oldDescription,
            'old_blocking' => $oldBlocking,
            'old_assignee' => $oldAssignee,
            'old_hours' => $oldHours,
            'task' => $task,
            'recent_editor' => $recentEditor,
        ];
    }, 'DEVELOPER');

    if ($state === 'termine') {
        $challengeDetails = challenge_consume_success($user, $taskId);
    }
    $task = $result['task'];
    $message = 'Modification de la tâche ' . $taskId . ' : ' . $task['titre'] . ' (' . $result['old_state'] . ' → ' . $state . ').';
    history_add($teamId, $user['login'], 'task_update', $message, [
        'task' => $taskId,
        'old_state' => $result['old_state'],
        'new_state' => $state,
        'old_title' => $result['old_title'],
        'new_title' => $task['titre'],
        'old_description' => $result['old_description'],
        'new_description' => $task['description'],
        'old_hours' => $result['old_hours'],
        'new_hours' => $hours,
        'old_assignee' => $result['old_assignee'],
        'new_assignee' => $task['assigne'],
        'old_blocking' => $result['old_blocking'],
        'new_blocking' => $task['blocage'],
    ]);
    if ($state === 'termine' && is_array($challengeDetails)) {
        history_add($teamId, $user['login'], 'task_complete', 'Tâche terminée après challenge technique : ' . $taskId . ' - ' . $task['titre'], [
            'task' => $taskId,
            'story' => (string) ($task['us_id'] ?? ''),
            'attempts' => (int) ($challengeDetails['attempts'] ?? 0),
        ]);
    }
    if ($state === 'bloque' && $result['old_state'] !== 'bloque') {
        history_add($teamId, $user['login'], 'task_block', 'Blocage signalé sur ' . $taskId . ' : ' . $blocking, [
            'task' => $taskId,
        ]);
    } elseif ($state !== 'bloque' && $result['old_state'] === 'bloque') {
        history_add($teamId, $user['login'], 'task_unblock', 'Blocage résolu sur ' . $taskId . '.', [
            'task' => $taskId,
            'new_state' => $state,
        ]);
    }
    return ['message' => 'Tâche mise à jour.' . task_recent_editor_notice($result['recent_editor'] ?? null)];
}

/**
 * Déplacement rapide dans le Kanban.
 */
function action_move_task(array $user, mixed $taskId, mixed $state, mixed $reason = ''): array
{
    $taskId = require_text($taskId, 1, 10, 'Tâche');
    $state = require_choice($state, ['a_faire', 'en_cours', 'bloque', 'termine'], 'État Kanban');
    $challengeDetails = null;
    if ($state === 'termine') {
        $team = load_team((int) $user['team']);
        $idx = team_task_index($team, $taskId);
        if ($idx === null || !empty($team['tasks'][$idx]['archived']) || (string) ($team['tasks'][$idx]['etat'] ?? '') === 'termine') {
            throw new AppError('Tâche introuvable, archivée ou déjà terminée.', 404);
        }
        if (!challenge_success_exists($user, $taskId)) {
            throw new AppError('Challenge technique requis.', 409);
        }
    }
    $reason = clean_text($reason);
    if ($state === 'bloque' && mb_strlen($reason) < 3) {
        throw new AppError('Une tâche bloquée doit être accompagnée d’une explication.');
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($taskId, $state, $reason, $user): array {
        $index = team_task_index($team, $taskId);
        if ($index === null || !empty($team['tasks'][$index]['archived'])) {
            throw new AppError('Tâche introuvable ou archivée.');
        }
        $task = &$team['tasks'][$index];
        $oldState = (string) $task['etat'];
        if ($oldState === 'termine') {
            throw new AppError('Tâche terminée : elle est immuable. Conservez-la telle quelle ou archivez-la.');
        }
        $recentEditor = task_recent_editor($team, $task, (string) $user['login']);
        $oldBlocking = $task['blocage'] ?? null;
        $task['etat'] = $state;
        $task['blocage'] = $state === 'bloque' ? $reason : null;
        $task['blocage_by'] = $state === 'bloque' ? (string) $user['login'] : null;
        $task['updated_at'] = iso_now();
        $task['updated_by'] = (string) $user['login'];
        if ($state !== 'bloque' || ($oldBlocking ?? '') !== ($task['blocage'] ?? '')) {
            unset($task['moderation']['blocage']);
        }

        $storyIndex = team_story_index($team, (string) $task['us_id']);
        if ($storyIndex !== null) {
            if ($oldState === 'termine' && $state !== 'termine' && ($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
                $team['stories'][$storyIndex]['etat'] = 'a_faire';
            }
            if ($state === 'en_cours' && in_array($team['stories'][$storyIndex]['etat'] ?? '', ['a_faire', 'refusee'], true)) {
                $team['stories'][$storyIndex]['etat'] = 'en_cours';
            }
        }
        return ['old_state' => $oldState, 'titre' => $task['titre'], 'recent_editor' => $recentEditor];
    }, 'DEVELOPER');

    if ($state === 'termine') {
        $challengeDetails = challenge_consume_success($user, $taskId);
    }
    if ($state === 'bloque') {
        if ($result['old_state'] !== 'bloque') {
            history_add($teamId, $user['login'], 'task_block', 'Blocage signalé sur ' . $taskId . ' : ' . $reason, [
                'task' => $taskId,
            ]);
        }
    } else {
        history_add($teamId, $user['login'], 'task_move', 'Déplacement Kanban de ' . $taskId . ' : ' . $result['old_state'] . ' → ' . $state . '.', [
            'task' => $taskId,
            'old_state' => $result['old_state'],
            'new_state' => $state,
        ]);
        if ($result['old_state'] === 'bloque') {
            history_add($teamId, $user['login'], 'task_unblock', 'Blocage résolu sur ' . $taskId . '.', [
                'task' => $taskId,
                'new_state' => $state,
            ]);
        }
    }
    return ['message' => 'Tâche déplacée dans « ' . kanban_label($state) . ' ».' . task_recent_editor_notice($result['recent_editor'] ?? null)];
}

/**
 * Charge la banque de questions du challenge technique.
 */
function challenge_bank(): array
{
    global $CHALLENGE_QUESTIONS;
    return is_array($CHALLENGE_QUESTIONS ?? null) ? $CHALLENGE_QUESTIONS : [];
}

/**
 * Charge la banque des défis terminal GNU/Linux simulé.
 */
function challenge_terminal_bank(): array
{
    global $CHALLENGE_TERMINALS;
    return is_array($CHALLENGE_TERMINALS ?? null) ? $CHALLENGE_TERMINALS : [];
}

/**
 * Chemin du fichier de persistance des challenges.
 */
function challenge_file_path(): string
{
    return CHALLENGES_FILE;
}

/**
 * Démarre ou reprend un challenge pour une tâche.
 */
function action_challenge_start(array $user, mixed $taskId): array
{
    $taskId = require_text($taskId, 1, 10, 'Tâche');
    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $login = (string) $user['login'];
    $role = team_role(load_team($teamId), $login);
    if ($role !== 'DEVELOPER') {
        throw new AppError('Action réservée au rôle Developer.', 403);
    }

    $team = load_team($teamId);
    $index = team_task_index($team, $taskId);
    if ($index === null || !empty($team['tasks'][$index]['archived'])) {
        throw new AppError('Tâche introuvable ou archivée.', 404);
    }
    $storyId = (string) ($team['tasks'][$index]['us_id'] ?? '');
    $questions = challenge_bank()[$storyId] ?? [];
    if (count($questions) < 2) {
        throw new AppError('Aucune question technique disponible pour cette User Story.', 500);
    }

    $response = with_locked_json(challenge_file_path(), function (array &$data) use ($teamId, $login, $taskId, $storyId, $questions): array {
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        foreach ($entries as $entry) {
            if (($entry['status'] ?? '') === 'pending'
                && (int) ($entry['team'] ?? 0) === $teamId
                && (string) ($entry['user'] ?? '') === $login
                && (string) ($entry['task'] ?? '') === $taskId) {
                $duration = (int) ($entry['duration'] ?? 30);
                $remaining = max(0, (int) ($entry['started_at'] ?? 0) + $duration - time());
                $order = is_array($entry['order'] ?? null) ? $entry['order'] : range(0, 3);
                $options = [];
                foreach ($order as $originalIndex) {
                    $options[] = (string) ($questions[$entry['question_index']]['options'][$originalIndex] ?? '');
                }
                return [
                    'challenge_id' => (string) $entry['id'],
                    'task_id' => $taskId,
                    'story_id' => $storyId,
                    'question' => (string) $questions[$entry['question_index']]['question'],
                    'options' => $options,
                    'started_at' => (int) $entry['started_at'],
                    'duration' => $duration,
                    'attempts' => (int) ($entry['attempts'] ?? 0),
                    'remaining' => $remaining,
                ];
            }
        }

        $questionIndex = random_int(0, count($questions) - 1);
        $order = range(0, 3);
        shuffle($order);
        $entry = [
            'id' => 'C' . bin2hex(random_bytes(8)),
            'team' => $teamId,
            'user' => $login,
            'task' => $taskId,
            'story' => $storyId,
            'question_index' => $questionIndex,
            'order' => $order,
            'started_at' => time(),
            'duration' => 15,
            'attempts' => 0,
            'status' => 'pending',
            'success_at' => null,
        ];
        $entries[] = $entry;
        $data['entries'] = $entries;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();

        $options = [];
        foreach ($order as $originalIndex) {
            $options[] = (string) ($questions[$questionIndex]['options'][$originalIndex] ?? '');
        }
        return [
            'challenge_id' => (string) $entry['id'],
            'task_id' => $taskId,
            'story_id' => $storyId,
            'question' => (string) $questions[$questionIndex]['question'],
            'options' => $options,
            'started_at' => (int) $entry['started_at'],
            'duration' => 15,
            'attempts' => 0,
            'remaining' => 15,
        ];
    }, ['entries' => [], 'revision' => 0]);

    return array_merge(['ok' => true], $response);
}

/**
 * Valide une réponse au challenge technique.
 */
function action_challenge_answer(array $user, mixed $challengeId, mixed $option): array
{
    $challengeId = require_text($challengeId, 1, 40, 'Challenge');
    $option = require_int($option, 0, 3, 'Réponse');
    $login = (string) $user['login'];
    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $role = team_role(load_team($teamId), $login);
    if ($role !== 'DEVELOPER') {
        throw new AppError('Action réservée au rôle Developer.', 403);
    }

    $result = with_locked_json(challenge_file_path(), function (array &$data) use ($challengeId, $login, $teamId, $option, $user): array {
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        $found = null;
        $foundIndex = null;
        foreach ($entries as $i => $entry) {
            if ((string) ($entry['id'] ?? '') === $challengeId) {
                $found = $entry;
                $foundIndex = $i;
                break;
            }
        }
        if ($found === null) {
            throw new AppError('Challenge introuvable.', 404);
        }
        if ((int) ($found['team'] ?? 0) !== $teamId || (string) ($found['user'] ?? '') !== $login) {
            throw new AppError('Challenge non autorisé.', 403);
        }
        if (($found['status'] ?? '') === 'success') {
            return ['already' => true, 'task_id' => (string) ($found['task'] ?? ''), 'story_id' => (string) ($found['story'] ?? '')];
        }

        $now = time();
        $startedAt = (int) ($found['started_at'] ?? 0);
        $duration = (int) ($found['duration'] ?? 30);
        if ($now < $startedAt + $duration) {
            throw new AppError('Délai de réflexion non écoulé.', 409);
        }

        $questions = challenge_bank()[(string) ($found['story'] ?? '')] ?? [];
        $qIndex = (int) ($found['question_index'] ?? 0);
        if (!isset($questions[$qIndex])) {
            throw new AppError('Question introuvable.', 500);
        }
        $order = is_array($found['order'] ?? null) ? $found['order'] : range(0, 3);
        if (!isset($order[$option])) {
            throw new AppError('Réponse invalide.', 400);
        }
        $originalOption = (int) $order[$option];
        $correct = (int) ($questions[$qIndex]['correct'] ?? 0);
        $isCorrect = $originalOption === $correct;

        if (!$isCorrect) {
            $entries[$foundIndex]['attempts'] = (int) ($found['attempts'] ?? 0) + 1;
            $entries[$foundIndex]['started_at'] = $now;
            // Après un échec, le délai de validation reste de 30 secondes.
            $entries[$foundIndex]['duration'] = 30;
            $data['entries'] = $entries;
            $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
            $data['updated_at'] = iso_now();
            return [
                'correct' => false,
                'selected_explanation' => (string) ($questions[$qIndex]['explanations'][$originalOption] ?? ''),
                'correct_explanation' => (string) ($questions[$qIndex]['explanations'][$correct] ?? ''),
                'attempts' => (int) $entries[$foundIndex]['attempts'],
                'started_at' => $now,
                'duration' => 30,
            ];
        }

        $entries[$foundIndex]['status'] = 'success';
        $entries[$foundIndex]['success_at'] = iso_now();
        $entries[$foundIndex]['attempts'] = (int) ($found['attempts'] ?? 0);
        $data['entries'] = $entries;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();
        return [
            'correct' => true,
            'task_id' => (string) ($found['task'] ?? ''),
            'story_id' => (string) ($found['story'] ?? ''),
            'question' => (string) $questions[$qIndex]['question'],
            'correct_option' => (string) $questions[$qIndex]['options'][$correct],
            'attempts' => (int) $entries[$foundIndex]['attempts'],
        ];
    }, ['entries' => [], 'revision' => 0]);

    if (!empty($result['already'])) {
        return ['ok' => true, 'status' => 'success', 'task_id' => $result['task_id'], 'story_id' => $result['story_id']];
    }

    if (empty($result['correct'])) {
        return array_merge(['ok' => true, 'status' => 'wrong'], $result);
    }

    $taskId = (string) $result['task_id'];
    $storyId = (string) $result['story_id'];
    $team = load_team($teamId);
    $index = team_task_index($team, $taskId);
    if ($index === null || !empty($team['tasks'][$index]['archived'])) {
        throw new AppError('Tâche introuvable ou archivée.', 404);
    }
    $title = (string) $team['tasks'][$index]['titre'];

    history_add($teamId, $login, 'task_challenge_success', 'Challenge technique réussi pour la tâche ' . $taskId . ' - ' . $title, [
        'task' => $taskId,
        'story' => $storyId,
        'question' => $result['question'],
        'answer' => $result['correct_option'],
        'attempts' => $result['attempts'],
    ]);

    return ['ok' => true, 'status' => 'success', 'task_id' => $taskId, 'story_id' => $storyId];
}

/**
 * Retourne un challenge actif pour l'utilisateur courant.
 */
function challenge_active_for_user(array $user): ?array
{
    if (!empty($user['is_teacher'])) {
        return null;
    }
    $context = student_context($user, false);
    $teamId = $context['team_id'];
    $login = (string) $user['login'];
    $data = json_read(challenge_file_path(), ['entries' => []]);
    $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
    foreach ($entries as $entry) {
        if (($entry['status'] ?? '') !== 'pending') {
            continue;
        }
        if ((int) ($entry['team'] ?? 0) !== $teamId || (string) ($entry['user'] ?? '') !== $login) {
            continue;
        }
        if ((string) ($entry['type'] ?? 'quiz') === 'terminal') {
            $terminal = challenge_terminal_bank()[(string) ($entry['story'] ?? '')] ?? null;
            if ($terminal === null) {
                continue;
            }
            $level = max(0, min(3, (int) ($entry['index_level'] ?? 0)));
            return [
                'challenge_id' => (string) ($entry['id'] ?? ''),
                'task_id' => (string) ($entry['task'] ?? ''),
                'story_id' => (string) ($entry['story'] ?? ''),
                'type' => 'terminal',
                'instruction' => (string) $terminal['instruction'],
                'exploration' => (array) ($terminal['exploration'] ?? []),
                'started_at' => (int) ($entry['started_at'] ?? 0),
                'attempts' => (int) ($entry['attempts'] ?? 0),
                'index_level' => $level,
                'indices' => array_slice((array) ($terminal['indices'] ?? []), 0, $level),
            ];
        }
        $questions = challenge_bank()[(string) ($entry['story'] ?? '')] ?? [];
        $qIndex = (int) ($entry['question_index'] ?? 0);
        if (!isset($questions[$qIndex])) {
            continue;
        }
        $order = is_array($entry['order'] ?? null) ? $entry['order'] : range(0, 3);
        $options = [];
        foreach ($order as $originalIndex) {
            $options[] = (string) ($questions[$qIndex]['options'][$originalIndex] ?? '');
        }
        $duration = (int) ($entry['duration'] ?? 30);
        return [
            'challenge_id' => (string) ($entry['id'] ?? ''),
            'task_id' => (string) ($entry['task'] ?? ''),
            'story_id' => (string) ($entry['story'] ?? ''),
            'question' => (string) $questions[$qIndex]['question'],
            'options' => $options,
            'started_at' => (int) ($entry['started_at'] ?? 0),
            'duration' => $duration,
            'attempts' => (int) ($entry['attempts'] ?? 0),
            'remaining' => max(0, (int) ($entry['started_at'] ?? 0) + $duration - time()),
        ];
    }
    return null;
}

/**
 * Archivage d'une tâche (jamais de suppression définitive).
 */

/**
 * Consomme un challenge réussi pour autoriser le passage à « Terminé ».
 */
function challenge_consume_success(array $user, string $taskId): array
{
    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $login = (string) $user['login'];
    $role = team_role(load_team($teamId), $login);
    if ($role !== 'DEVELOPER') {
        throw new AppError('Action réservée au rôle Developer.', 403);
    }

    $result = with_locked_json(challenge_file_path(), function (array &$data) use ($teamId, $login, $taskId): array {
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        foreach ($entries as $i => $entry) {
            if (($entry['status'] ?? '') === 'success'
                && (int) ($entry['team'] ?? 0) === $teamId
                && (string) ($entry['user'] ?? '') === $login
                && (string) ($entry['task'] ?? '') === $taskId) {
                $entries[$i]['status'] = 'used';
                $entries[$i]['used_at'] = iso_now();
                $data['entries'] = $entries;
                $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
                $data['updated_at'] = iso_now();
                return [
                    'story_id' => (string) ($entry['story'] ?? ''),
                    'attempts' => (int) ($entry['attempts'] ?? 0),
                ];
            }
        }
        throw new AppError('Challenge technique requis.', 409);
    }, ['entries' => [], 'revision' => 0]);

    return $result;
}

/**
 * Vérifie qu'un challenge réussi existe pour une tâche, sans le consommer.
 */
function challenge_success_exists(array $user, string $taskId): bool
{
    $teamId = (int) $user['team'];
    $login = (string) $user['login'];
    $data = json_read(challenge_file_path(), ['entries' => []]);
    $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
    foreach ($entries as $entry) {
        if (($entry['status'] ?? '') === 'success'
            && (int) ($entry['team'] ?? 0) === $teamId
            && (string) ($entry['user'] ?? '') === $login
            && (string) ($entry['task'] ?? '') === $taskId) {
            return true;
        }
    }
    return false;
}

/**
 * Vérifie (et éventuellement consomme) le défi terminal réussi d'une User Story.
 */
function challenge_terminal_success(array $user, string $storyId, bool $consume = false): bool
{
    $teamId = (int) $user['team'];
    $login = (string) $user['login'];
    $matches = static function (array $entry) use ($teamId, $login, $storyId): bool {
        return ($entry['status'] ?? '') === 'success'
            && (string) ($entry['type'] ?? '') === 'terminal'
            && (int) ($entry['team'] ?? 0) === $teamId
            && (string) ($entry['user'] ?? '') === $login
            && (string) ($entry['story'] ?? '') === $storyId;
    };

    if (!$consume) {
        $data = json_read(challenge_file_path(), ['entries' => []]);
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        foreach ($entries as $entry) {
            if ($matches($entry)) {
                return true;
            }
        }
        return false;
    }

    return (bool) with_locked_json(challenge_file_path(), function (array &$data) use ($matches): bool {
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        foreach ($entries as $index => $entry) {
            if ($matches($entry)) {
                $entries[$index]['status'] = 'used';
                $entries[$index]['used_at'] = iso_now();
                $data['entries'] = $entries;
                $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
                $data['updated_at'] = iso_now();
                return true;
            }
        }
        return false;
    }, ['entries' => [], 'revision' => 0]);
}

/**
 * Démarre ou reprend un défi terminal GNU/Linux simulé pour une User Story.
 */
function action_challenge_terminal_start(array $user, mixed $storyId): array
{
    $storyId = require_text($storyId, 3, 10, 'User Story');
    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $login = (string) $user['login'];
    $role = team_role(load_team($teamId), $login);
    if ($role !== 'DEVELOPER') {
        throw new AppError('Action réservée au rôle Developer.', 403);
    }

    $team = load_team($teamId);
    $index = team_story_index($team, $storyId);
    if ($index === null) {
        throw new AppError('User Story introuvable.', 404);
    }
    $story = $team['stories'][$index];
    if (empty($story['selectionnee'])) {
        throw new AppError('La User Story doit être sélectionnée dans le Sprint.');
    }
    if (($story['etat'] ?? '') === 'validee') {
        throw new AppError('La User Story est déjà validée.');
    }
    if (($story['etat'] ?? '') === 'a_valider') {
        throw new AppError('La User Story est déjà en attente de validation.');
    }
    $handlers = story_task_handlers($team, $storyId);
    if ($handlers !== [] && !in_array($login, $handlers, true)) {
        throw new AppError('Vous n’êtes pas l’auteur de la tâche rattachée à cette User Story.', 403);
    }

    $terminal = challenge_terminal_bank()[$storyId] ?? null;
    if ($terminal === null) {
        throw new AppError('Aucun défi terminal disponible pour cette User Story.', 500);
    }

    $response = with_locked_json(challenge_file_path(), function (array &$data) use ($teamId, $login, $storyId, $terminal): array {
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        foreach ($entries as $entryIndex => $entry) {
            if (($entry['status'] ?? '') === 'pending'
                && (string) ($entry['type'] ?? 'quiz') === 'terminal'
                && (int) ($entry['team'] ?? 0) === $teamId
                && (string) ($entry['user'] ?? '') === $login
                && (string) ($entry['story'] ?? '') === $storyId) {
                // Un clic sur « Prête à valider » démarre une nouvelle
                // validation : les indices d'une tentative abandonnée ne
                // doivent pas être réaffichés automatiquement.
                if ((int) ($entry['index_level'] ?? 0) !== 0) {
                    $entries[$entryIndex]['index_level'] = 0;
                    $data['entries'] = $entries;
                    $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
                    $data['updated_at'] = iso_now();
                }
                return [
                    'challenge_id' => (string) ($entry['id'] ?? ''),
                    'task_id' => (string) ($entry['task'] ?? ''),
                    'story_id' => $storyId,
                    'type' => 'terminal',
                    'instruction' => (string) $terminal['instruction'],
                    'exploration' => $terminal['exploration'],
                    'started_at' => (int) ($entry['started_at'] ?? 0),
                    'attempts' => (int) ($entry['attempts'] ?? 0),
                    'index_level' => 0,
                    'indices' => [],
                ];
            }
        }

        $entry = [
            'id' => 'C' . bin2hex(random_bytes(8)),
            'team' => $teamId,
            'user' => $login,
            'task' => '',
            'story' => $storyId,
            'type' => 'terminal',
            'status' => 'pending',
            'started_at' => time(),
            'duration' => 0,
            'attempts' => 0,
            'index_level' => 0,
            'success_at' => null,
        ];
        $entries[] = $entry;
        $data['entries'] = $entries;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();
        return [
            'challenge_id' => (string) ($entry['id'] ?? ''),
            'task_id' => '',
            'story_id' => $storyId,
            'type' => 'terminal',
            'instruction' => (string) $terminal['instruction'],
            'exploration' => $terminal['exploration'],
            'started_at' => (int) ($entry['started_at'] ?? 0),
            'attempts' => 0,
            'index_level' => 0,
            'indices' => [],
        ];
    }, ['entries' => [], 'revision' => 0]);

    return array_merge(['ok' => true], $response);
}

/**
 * Valide une commande saisie dans le terminal GNU/Linux simulé.
 */
function action_challenge_terminal_answer(array $user, mixed $challengeId, mixed $command): array
{
    $challengeId = require_text($challengeId, 1, 40, 'Challenge');
    $command = require_text($command, 1, 200, 'Commande');

    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $login = (string) $user['login'];
    $role = team_role(load_team($teamId), $login);
    if ($role !== 'DEVELOPER') {
        throw new AppError('Action réservée au rôle Developer.', 403);
    }

    $result = with_locked_json(challenge_file_path(), function (array &$data) use ($challengeId, $login, $teamId, $command): array {
        $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
        $found = null;
        $foundIndex = null;
        foreach ($entries as $i => $entry) {
            if ((string) ($entry['id'] ?? '') === $challengeId) {
                $found = $entry;
                $foundIndex = $i;
                break;
            }
        }
        if ($found === null) {
            throw new AppError('Challenge introuvable.', 404);
        }
        if ((int) ($found['team'] ?? 0) !== $teamId || (string) ($found['user'] ?? '') !== $login) {
            throw new AppError('Challenge non autorisé.', 403);
        }
        if ((string) ($found['type'] ?? 'quiz') !== 'terminal') {
            throw new AppError('Challenge de type terminal attendu.', 400);
        }
        if (($found['status'] ?? '') === 'success') {
            return ['already' => true, 'task_id' => (string) ($found['task'] ?? ''), 'story_id' => (string) ($found['story'] ?? '')];
        }

        // Limitation serveur : deux secondes minimum entre deux commandes.
        $nowTs = microtime(true);
        $lastTs = (float) ($found['last_command_at'] ?? 0);
        if ($lastTs > 0 && ($nowTs - $lastTs) < 2.0) {
            throw new AppError('Veuillez patienter au moins 2 secondes entre deux commandes.', 429);
        }
        $entries[$foundIndex]['last_command_at'] = $nowTs;
        $data['entries'] = $entries;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();

        $terminal = challenge_terminal_bank()[(string) ($found['story'] ?? '')] ?? null;
        if ($terminal === null) {
            throw new AppError('Défi terminal introuvable.', 500);
        }

        $entered = str_replace("'", '"', trim($command));
        $accepted = (string) ($terminal['command'] ?? '');
        $partials = is_array($terminal['partials'] ?? null) ? $terminal['partials'] : [];
        $exploration = is_array($terminal['exploration'] ?? null) ? $terminal['exploration'] : [];
        $level = max(0, min(3, (int) ($found['index_level'] ?? 0)));
        $indices = (array) ($terminal['indices'] ?? []);

        // La commande finale reste prioritaire : sans cela, une commande
        // correcte qui commence par un extrait serait prise pour un indice.
        if ($entered === $accepted) {
            $entries[$foundIndex]['status'] = 'success';
            $entries[$foundIndex]['success_at'] = iso_now();
            $data['entries'] = $entries;
            $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
            $data['updated_at'] = iso_now();
            return [
                'status' => 'success',
                'task_id' => (string) ($found['task'] ?? ''),
                'story_id' => (string) ($found['story'] ?? ''),
                'attempts' => (int) ($found['attempts'] ?? 0),
            ];
        }

        // La solution n'est révélée qu'après le troisième indice et seulement
        // lorsque l'utilisateur exécute explicitement la commande « solution ».
        if ($entered === 'solution') {
            if ($level >= 3) {
                return [
                    'status' => 'solution',
                    'command' => $accepted,
                    'index_level' => $level,
                ];
            }
            return [
                'status' => 'locked',
                'index_level' => $level,
                'message' => 'La commande solution est disponible après le troisième indice.',
            ];
        }

        // Premier indice : toute commande du champ exploration de l'US courante.
        if (in_array($entered, $exploration, true)) {
            $newLevel = max($level, 1);
            if ($newLevel !== $level) {
                $entries[$foundIndex]['index_level'] = $newLevel;
                $data['entries'] = $entries;
                $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
                $data['updated_at'] = iso_now();
            }
            $response = [
                'status' => 'hint',
                'from_level' => $level,
                'index_level' => $newLevel,
                'indices' => array_slice($indices, 0, $newLevel),
            ];
            if ($newLevel === $level) {
                $response['message'] = $level >= 3
                    ? 'Tous les indices sont déjà débloqués. Saisissez la commande finale ou solution.'
                    : 'Cet indice est déjà débloqué. Saisissez help pour revoir les commandes disponibles.';
            }
            return $response;
        }

        // Indices suivants : uniquement à partir des extraits cachés du défi.
        $targetLevel = null;
        foreach ($partials as $partialIndex => $partial) {
            if (!is_string($partial) || $partial === '' || !str_contains($entered, $partial)) {
                continue;
            }
            $candidate = min(3, (int) $partialIndex + 2);
            if ($candidate <= $level + 1) {
                $targetLevel = max($targetLevel ?? 0, $candidate);
            }
        }

        if ($targetLevel !== null && $targetLevel > $level) {
            $entries[$foundIndex]['index_level'] = $targetLevel;
            $data['entries'] = $entries;
            $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
            $data['updated_at'] = iso_now();
            return [
                'status' => 'hint',
                'from_level' => $level,
                'index_level' => $targetLevel,
                'indices' => array_slice($indices, 0, $targetLevel),
            ];
        }

        if ($targetLevel !== null) {
            return [
                'status' => 'hint',
                'from_level' => $level,
                'index_level' => $level,
                'indices' => array_slice($indices, 0, $level),
                'message' => $level >= 3
                    ? 'Tous les indices sont déjà débloqués. Saisissez la commande finale ou solution.'
                    : 'Cet indice est déjà débloqué. Cherchez l’extrait suivant ou saisissez help.',
            ];
        }

        $entries[$foundIndex]['attempts'] = (int) ($found['attempts'] ?? 0) + 1;
        $data['entries'] = $entries;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();
        return [
            'status' => 'wrong',
            'attempts' => (int) $entries[$foundIndex]['attempts'],
        ];
    }, ['entries' => [], 'revision' => 0]);

    if (!empty($result['already'])) {
        return ['ok' => true, 'status' => 'success', 'task_id' => (string) ($result['task_id'] ?? ''), 'story_id' => (string) ($result['story_id'] ?? '')];
    }
    $status = (string) ($result['status'] ?? '');
    if (in_array($status, ['wrong', 'hint', 'solution', 'locked', 'perhaps'], true)) {
        return array_merge(['ok' => true], $result);
    }
    return array_merge(['ok' => true, 'status' => 'success'], $result);
}

/**
 * Archivage d'une tâche (jamais de suppression définitive).
 */
function action_archive_task(array $user, mixed $taskId, mixed $teamId = null, mixed $confirmation = null): array
{
    $taskId = require_text($taskId, 1, 10, 'Tâche');

    if (!empty($user['is_teacher'])) {
        if ($teamId === null || $teamId === '') {
            throw new AppError('Équipe cible requise pour l’archivage.', 400);
        }
        $teamId = (int) require_int($teamId, 1, 3, 'Équipe');
        if (trim((string) $confirmation) !== 'JE CONFIRME L’ARCHIVAGE DE CETTE TÂCHE') {
            throw new AppError('Confirmation d’archivage requise : saisissez la phrase exacte.', 409);
        }
        $result = with_locked_json(team_file_path($teamId), function (array &$team) use ($taskId, $user): array {
            assert_team_data($team);
            $index = team_task_index($team, $taskId);
            if ($index === null || !empty($team['tasks'][$index]['archived'])) {
                throw new AppError('Tâche introuvable ou déjà archivée.');
            }
            $task = &$team['tasks'][$index];
            $task['archived'] = true;
            $task['archived_at'] = iso_now();
            $task['archived_by'] = (string) $user['login'];
            $task['updated_at'] = iso_now();
            $task['updated_by'] = (string) $user['login'];

            $storyIndex = team_story_index($team, (string) $task['us_id']);
            if ($storyIndex !== null && ($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
                $team['stories'][$storyIndex]['etat'] = 'a_faire';
            }
            $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
            $team['updated_at'] = iso_now();
            return ['titre' => $task['titre']];
        }, ['members' => [], 'stories' => [], 'tasks' => [], 'messages' => [], 'swaps' => []]);

        history_add($teamId, $user['login'], 'task_archive', 'Archivage de la tâche ' . $taskId . ' : ' . $result['titre'], [
            'task' => $taskId,
        ]);
        return ['message' => 'Tâche archivée.'];
    }

    if (trim((string) $confirmation) !== 'JE CONFIRME L’ARCHIVAGE DE CETTE TÂCHE') {
        throw new AppError('Confirmation d’archivage requise : saisissez la phrase exacte.', 409);
    }
    [$resolvedTeamId, $result] = mutate_student_team($user, function (array &$team) use ($taskId, $user): array {
        $index = team_task_index($team, $taskId);
        if ($index === null || !empty($team['tasks'][$index]['archived'])) {
            throw new AppError('Tâche introuvable ou déjà archivée.');
        }
        $task = &$team['tasks'][$index];
        $task['archived'] = true;
        $task['archived_at'] = iso_now();
        $task['archived_by'] = (string) $user['login'];
        $task['updated_at'] = iso_now();
        $task['updated_by'] = (string) $user['login'];

        $storyIndex = team_story_index($team, (string) $task['us_id']);
        if ($storyIndex !== null && ($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
            $team['stories'][$storyIndex]['etat'] = 'a_faire';
        }
        return ['titre' => $task['titre']];
    }, 'DEVELOPER');

    history_add($resolvedTeamId, $user['login'], 'task_archive', 'Archivage de la tâche ' . $taskId . ' : ' . $result['titre'], [
        'task' => $taskId,
    ]);
    return ['message' => 'Tâche archivée.'];
}

/**
 * Signalement d'un blocage par n'importe quel membre de l'équipe.
 * Les Developers disposent aussi du déplacement Kanban complet ; cette
 * action garantit que le PO et le Scrum Master peuvent rendre un blocage
 * visible, comme prévu par le cahier des charges.
 */
function action_report_blockage(array $user, mixed $taskId, mixed $reason): array
{
    $taskId = require_text($taskId, 1, 10, 'Tâche');
    $reason = require_text($reason, 3, 300, 'Explication du blocage');
    $now = time();

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($taskId, $reason, $user, $now): array {
        $index = team_task_index($team, $taskId);
        if ($index === null || !empty($team['tasks'][$index]['archived'])) {
            throw new AppError('Tâche introuvable ou archivée.', 404);
        }
        $task = &$team['tasks'][$index];
        $oldState = (string) $task['etat'];
        if ($oldState === 'termine') {
            throw new AppError('Tâche terminée : elle est immuable. Conservez-la telle quelle ou archivez-la.', 409);
        }
        if ($oldState === 'bloque') {
            throw new AppError('Cette tâche est déjà signalée comme bloquée.', 409);
        }
        $task['etat'] = 'bloque';
        $task['blocage'] = $reason;
        $task['blocage_by'] = (string) $user['login'];
        $task['updated_at'] = iso_now();
        $task['updated_by'] = (string) $user['login'];
        unset($task['moderation']['blocage']);

        $storyIndex = team_story_index($team, (string) $task['us_id']);
        if ($storyIndex !== null && ($team['stories'][$storyIndex]['etat'] ?? '') === 'a_valider') {
            $team['stories'][$storyIndex]['etat'] = 'a_faire';
        }
        // Le signalement de blocage est publié dans le chat de l'équipe :
        // le Developer concerné y accède via le filtre du chat de son équipe.
        team_add_chat_message($team, (string) $user['login'], $now, 'Blocage signalé sur la tâche ' . $taskId . ' : ' . $reason);
        return ['titre' => $task['titre'], 'old_state' => $oldState];
    });

    history_add($teamId, $user['login'], 'task_block', 'Blocage signalé sur ' . $taskId . ' : ' . $reason, [
        'task' => $taskId,
        'old_state' => $result['old_state'],
    ]);
    return ['message' => 'Blocage signalé sur la tâche ' . $taskId . '.'];
}

/**
 * Libellé d'un état Kanban.
 */
function kanban_label(string $state): string
{
    return match ($state) {
        'a_faire' => 'À faire',
        'en_cours' => 'En cours',
        'bloque' => 'Bloqué',
        'termine' => 'Terminé',
        default => $state,
    };
}

// ===========================================================================
// Communication et échanges de rôles
// ===========================================================================

/**
 * Envoi d'un message dans le chat d'équipe.
 */
function action_chat_send(array $user, mixed $text): array
{
    $text = require_text($text, 1, CHAT_MAX_LENGTH, 'Message');
    $now = time();

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($text, $now, $user): array {
        foreach (array_reverse($team['messages']) as $message) {
            if (($message['author'] ?? '') === $user['login']) {
                if ($now - (int) ($message['ts'] ?? 0) < CHAT_MIN_INTERVAL_SECONDS) {
                    throw new AppError('Veuillez patienter avant d’envoyer un nouveau message.', 429);
                }
                break;
            }
        }
        $number = (int) ($team['next_message_number'] ?? 1);
        $message = [
            'id' => 'M' . $number,
            'author' => (string) $user['login'],
            'at' => iso_now(),
            'ts' => $now,
            'text' => $text,
        ];
        $team['messages'][] = $message;
        $team['next_message_number'] = $number + 1;
        if (count($team['messages']) > 300) {
            $team['messages'] = array_slice($team['messages'], -300);
        }
        return ['message' => $message];
    });

    history_add($teamId, $user['login'], 'chat_message', 'Message de chat envoyé : ' . $text, [
        'message' => $result['message']['id'],
    ]);
    return ['message' => 'Message envoyé.', 'chat' => $result['message']];
}

/**
 * Modification collective du Sprint Goal.
 */
function action_update_goal(array $user, mixed $goal, mixed $justification, mixed $participants): array
{
    $goal = require_text($goal, 10, 300, 'Sprint Goal');
    $justification = require_text($justification, 10, 500, 'Justification');
    if (!is_array($participants)) {
        throw new AppError('Participants invalides.');
    }
    $cleanParticipants = [];
    foreach ($participants as $participant) {
        if (!is_string($participant)) {
            throw new AppError('Participants invalides.');
        }
        $cleanParticipants[] = clean_text($participant);
    }
    $participants = array_values(array_unique($cleanParticipants));
    if (!in_array((string) $user['login'], $participants, true)) {
        $participants[] = (string) $user['login'];
        $participants = array_values(array_unique($participants));
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($goal, $justification, $participants, $user): array {
        if (count($participants) < 2) {
            throw new AppError('Une décision collective doit impliquer au moins deux membres.');
        }
        foreach ($participants as $participant) {
            if (team_member($team, $participant) === null) {
                throw new AppError('Un participant n’appartient pas à l’équipe.');
            }
        }
        $old = (string) $team['sprint_goal'];
        $oldJustification = (string) ($team['sprint_goal_justification'] ?? '');
        $team['sprint_goal'] = $goal;
        $team['sprint_goal_justification'] = $justification;
        $team['sprint_goal_updated_by'] = (string) $user['login'];
        $team['sprint_goal_updated_at'] = iso_now();
        if ($old !== $goal) {
            unset($team['sprint_goal_moderation']['sprint_goal']);
        }
        if ($oldJustification !== $justification) {
            unset($team['sprint_goal_moderation']['sprint_goal_justification']);
        }
        return ['old' => $old];
    });

    history_add($teamId, $user['login'], 'goal_update', 'Sprint Goal modifié collectivement.', [
        'old' => $result['old'],
        'new' => $goal,
        'justification' => $justification,
        'participants' => $participants,
    ]);
    return ['message' => 'Sprint Goal mis à jour.'];
}

/**
 * Enregistre la rétrospective collective de l'équipe.
 * Un seul texte partagé suffit : l'équipe le fait évoluer au fil de la séance.
 */
function action_update_retrospective(array $user, mixed $text): array
{
    $text = require_text($text, 10, 1000, 'Rétrospective');

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($text, $user): array {
        $old = (string) ($team['retrospective'] ?? '');
        $team['retrospective'] = $text;
        $team['retrospective_updated_by'] = (string) $user['login'];
        $team['retrospective_updated_at'] = iso_now();
        unset($team['retrospective_moderation']['retrospective']);
        $history = is_array($team['retrospective_history'] ?? null) ? $team['retrospective_history'] : [];
        $history[] = [
            'author' => (string) $user['login'],
            'at' => iso_now(),
            'text' => $text,
        ];
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
        $team['retrospective_history'] = $history;
        return ['old' => $old];
    });

    history_add($teamId, $user['login'], 'retrospective_update', 'Rétrospective collective mise à jour.', [
        'old' => $result['old'],
        'new' => $text,
    ]);
    return ['message' => 'Rétrospective enregistrée.'];
}

/**
 * Statut effectif d'une proposition d'échange (expiration à la lecture).
 */
function swap_effective_status(array $swap, ?int $now = null): string
{
    $now ??= time();
    $status = (string) ($swap['status'] ?? 'pending');
    if ($status === 'pending' && (int) ($swap['expires_at'] ?? 0) <= $now) {
        return 'expired';
    }
    return $status;
}

/**
 * Marque les propositions expirées, si nécessaire.
 */
function expire_swaps_if_needed(int $teamId): void
{
    $team = load_team($teamId);
    $now = time();
    $hasExpired = false;
    foreach ($team['swaps'] as $swap) {
        if (($swap['status'] ?? '') === 'pending' && (int) ($swap['expires_at'] ?? 0) <= $now) {
            $hasExpired = true;
            break;
        }
    }
    if (!$hasExpired) {
        return;
    }

    with_app_lock(static function () use ($teamId, $now): void {
        $expired = [];
        with_locked_json(team_file_path($teamId), function (array &$team) use ($now, &$expired): void {
            foreach ($team['swaps'] as $index => $swap) {
                if (($swap['status'] ?? '') === 'pending' && (int) ($swap['expires_at'] ?? 0) <= $now) {
                    $team['swaps'][$index]['status'] = 'expired';
                    $team['swaps'][$index]['responded_at'] = iso_now();
                    $expired[] = $team['swaps'][$index];
                }
            }
            if ($expired !== []) {
                $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
                $team['updated_at'] = iso_now();
            }
        }, ['members' => [], 'stories' => [], 'tasks' => [], 'messages' => [], 'swaps' => []]);

        foreach ($expired as $swap) {
            history_add($teamId, 'systeme', 'swap_expired', 'Proposition d’échange expirée entre ' . $swap['from'] . ' et ' . $swap['to'] . '.', [
                'proposal' => $swap['id'],
            ]);
        }
    });
}

/**
 * Propose un échange de rôles à un membre de la même équipe.
 */
function action_propose_swap(array $user, mixed $target): array
{
    $target = strtolower(require_text($target, 1, 30, 'Destinataire'));
    if ($target === (string) $user['login']) {
        throw new AppError('Vous ne pouvez pas vous proposer un échange à vous-même.');
    }

    [$teamId, $result] = mutate_student_team($user, function (array &$team) use ($target, $user): array {
        $now = time();
        $from = (string) $user['login'];
        $memberA = team_member($team, $from);
        $memberB = team_member($team, $target);
        if ($memberA === null || $memberB === null) {
            throw new AppError('Le destinataire doit appartenir à votre équipe.');
        }
        $roleA = (string) $memberA['role'];
        $roleB = (string) $memberB['role'];
        if ($roleA === $roleB) {
            throw new AppError('Un échange entre deux membres ayant le même rôle est inutile et refusé.');
        }
        if ($roleA === 'DEVELOPER' && $roleB === 'DEVELOPER') {
            throw new AppError('Un échange entre deux Developers est inutile et refusé.');
        }

        // Un Developer ayant des tâches terminées non déclarées prêtes ne peut
        // pas échanger de rôle : il perdrait la capacité de les valider.
        if ($roleA === 'DEVELOPER' && developer_is_affected($team, $from)) {
            throw new AppError('Vous avez des User Stories en attente de validation. Déclarez-les prêtes avant d’échanger de rôle.', 409);
        }
        if ($roleB === 'DEVELOPER' && developer_is_affected($team, $target)) {
            throw new AppError('Ce Developer a des User Stories en attente de validation. Il doit d’abord les déclarer prêtes.', 409);
        }

        foreach ($team['swaps'] as $swap) {
            if (swap_effective_status($swap, $now) !== 'pending') {
                continue;
            }
            $involves = in_array($from, [$swap['from'], $swap['to']], true)
                || in_array($target, [$swap['from'], $swap['to']], true);
            if ($involves) {
                throw new AppError('Un membre ne peut avoir qu’une seule proposition d’échange en attente.');
            }
        }

        $proposal = [
            'id' => 'S' . bin2hex(random_bytes(4)),
            'from' => $from,
            'to' => $target,
            'from_role' => $roleA,
            'to_role' => $roleB,
            'created_at' => iso_now(),
            'created_ts' => $now,
            'expires_at' => $now + SWAP_TTL_SECONDS,
            'status' => 'pending',
            'responded_at' => null,
            'responded_by' => null,
        ];
        $team['swaps'][] = $proposal;
        return ['proposal' => $proposal, 'role_a' => $roleA, 'role_b' => $roleB];
    });

    history_add($teamId, $user['login'], 'swap_propose', 'Proposition d’échange de rôles à ' . $target . ' (' . role_label($result['role_a']) . ' ↔ ' . role_label($result['role_b']) . ').', [
        'proposal' => $result['proposal']['id'],
        'from' => (string) $user['login'],
        'to' => $target,
    ]);
    return ['message' => 'Proposition envoyée à ' . $target . '.', 'proposal' => $result['proposal']];
}

/**
 * Accepte ou refuse une proposition d'échange.
 */
function action_respond_swap(array $user, mixed $proposalId, mixed $accept): array
{
    $proposalId = require_text($proposalId, 3, 20, 'Proposition');
    $accept = filter_var($accept, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($accept === null) {
        throw new AppError('Réponse invalide.');
    }

    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $now = time();
    $login = (string) $user['login'];

    $outcome = with_locked_json(team_file_path($teamId), function (array &$team) use ($proposalId, $accept, $login, $now): array {
        assert_simulation_running(session_phase(current_session()));
        $index = null;
        foreach ($team['swaps'] as $i => $swap) {
            if (($swap['id'] ?? '') === $proposalId) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            return ['error' => 'Proposition introuvable.', 'status' => 404];
        }
        $swap = $team['swaps'][$index];
        if (($swap['to'] ?? '') !== $login) {
            return ['error' => 'Cette proposition ne vous est pas destinée.', 'status' => 403];
        }
        if (($swap['status'] ?? '') !== 'pending') {
            return ['error' => 'Cette proposition n’est plus en attente.', 'status' => 409];
        }
        if ((int) ($swap['expires_at'] ?? 0) <= $now) {
            $team['swaps'][$index]['status'] = 'expired';
            $team['swaps'][$index]['responded_at'] = iso_now();
            $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
            return ['expired' => true, 'from' => $swap['from'], 'to' => $swap['to']];
        }

        if (!$accept) {
            $team['swaps'][$index]['status'] = 'refused';
            $team['swaps'][$index]['responded_at'] = iso_now();
            $team['swaps'][$index]['responded_by'] = $login;
            $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
            return ['accepted' => false, 'from' => $swap['from'], 'to' => $swap['to']];
        }

        // Nouvelle vérification de la composition avant permutation.
        $memberA = team_member($team, (string) $swap['from']);
        $memberB = team_member($team, (string) $swap['to']);
        if ($memberA === null || $memberB === null) {
            return ['error' => 'Un participant a quitté l’équipe.', 'status' => 409];
        }
        $roleA = (string) $memberA['role'];
        $roleB = (string) $memberB['role'];
        if ($roleA === $roleB || ($roleA === 'DEVELOPER' && $roleB === 'DEVELOPER')) {
            return ['error' => 'Cet échange ne respecte plus les règles de composition.', 'status' => 409];
        }
        // Le Developer qui accepte ne doit pas avoir de tâches en attente.
        if ($roleB === 'DEVELOPER' && developer_is_affected($team, $login)) {
            return ['error' => 'Vous avez des User Stories en attente de validation. Déclarez-les prêtes avant d’accepter cet échange.', 'status' => 409];
        }

        $newMembers = $team['members'];
        foreach ($newMembers as $i => $member) {
            if ($member['login'] === $swap['from']) {
                $newMembers[$i]['role'] = $roleB;
            } elseif ($member['login'] === $swap['to']) {
                $newMembers[$i]['role'] = $roleA;
            }
        }
        try {
            validate_team_composition($newMembers);
        } catch (RuntimeException $exception) {
            return ['error' => $exception->getMessage(), 'status' => 409];
        }
        $team['members'] = $newMembers;
        // Un ancien Developer peut devenir PO ou Scrum Master : les tâches qui
        // lui étaient affectées restent intactes mais redeviennent non affectées.
        $unassignedTasks = task_clear_invalid_assignments($team);

        assert_simulation_running(session_phase(current_session()));
        $team['swaps'][$index]['status'] = 'accepted';
        $team['swaps'][$index]['responded_at'] = iso_now();
        $team['swaps'][$index]['responded_by'] = $login;
        $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
        return [
            'accepted' => true,
            'from' => $swap['from'],
            'to' => $swap['to'],
            'role_from' => $roleB,
            'role_to' => $roleA,
            'unassigned_tasks' => $unassignedTasks,
        ];
    }, ['members' => [], 'stories' => [], 'tasks' => [], 'messages' => [], 'swaps' => []]);

    if (isset($outcome['error'])) {
        throw new AppError((string) $outcome['error'], (int) ($outcome['status'] ?? 400));
    }
    if (!empty($outcome['expired'])) {
        history_add($teamId, 'systeme', 'swap_expired', 'Proposition d’échange expirée entre ' . $outcome['from'] . ' et ' . $outcome['to'] . '.', [
            'proposal' => $proposalId,
        ]);
        throw new AppError('Cette proposition a expiré.', 409);
    }

    if (!$outcome['accepted']) {
        history_add($teamId, $login, 'swap_refuse', 'Proposition d’échange refusée entre ' . $outcome['from'] . ' et ' . $outcome['to'] . '.', [
            'proposal' => $proposalId,
        ]);
        return ['message' => 'Proposition refusée ; aucun rôle n’a changé.'];
    }

    history_add($teamId, $login, 'swap_accept', 'Échange de rôles accepté entre ' . $outcome['from'] . ' et ' . $outcome['to'] . ' : ' . role_label($outcome['role_from']) . ' ↔ ' . role_label($outcome['role_to']) . '.', [
        'proposal' => $proposalId,
        'from' => $outcome['from'],
        'to' => $outcome['to'],
    ]);
    foreach ($outcome['unassigned_tasks'] ?? [] as $unassigned) {
        $taskId = (string) ($unassigned['task'] ?? '');
        $formerLogin = (string) ($unassigned['login'] ?? '');
        if ($taskId === '' || $formerLogin === '') {
            continue;
        }
        history_add($teamId, 'systeme', 'task_unassign', 'Affectation retirée de la tâche ' . $taskId . ' : ' . $formerLogin . ' n’est plus Developer.', [
            'task' => $taskId,
            'login' => $formerLogin,
            'role' => $unassigned['role'] ?? null,
        ]);
    }
    return ['message' => 'Proposition acceptée ; les rôles ont été échangés.'];
}

/**
 * Annulation d'une proposition par son auteur.
 */
function action_cancel_swap(array $user, mixed $proposalId): array
{
    $proposalId = require_text($proposalId, 3, 20, 'Proposition');
    $login = (string) $user['login'];

    [$teamId] = mutate_student_team($user, function (array &$team) use ($proposalId, $login): array {
        $index = null;
        foreach ($team['swaps'] as $i => $swap) {
            if (($swap['id'] ?? '') === $proposalId) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            throw new AppError('Proposition introuvable.', 404);
        }
        if (($team['swaps'][$index]['from'] ?? '') !== $login) {
            throw new AppError('Seul l’auteur peut annuler sa proposition.', 403);
        }
        if (swap_effective_status($team['swaps'][$index]) !== 'pending') {
            throw new AppError('Cette proposition n’est plus en attente.', 409);
        }
        $team['swaps'][$index]['status'] = 'cancelled';
        $team['swaps'][$index]['responded_at'] = iso_now();
        return [];
    });

    history_add($teamId, $login, 'swap_cancel', 'Proposition d’échange annulée par son auteur.', [
        'proposal' => $proposalId,
    ]);
    return ['message' => 'Proposition annulée.'];
}

/**
 * Réponse à un événement automatique.
 */
function action_respond_event(array $user, mixed $eventId, mixed $optionIndex): array
{
    $eventId = require_text($eventId, 3, 30, 'Événement');
    $optionIndex = require_int($optionIndex, 0, 1, 'Option');

    $context = student_context($user, true);
    $teamId = $context['team_id'];
    $events = json_read(EVENTS_FILE, []);
    $teamEvents = $events['teams'][(string) $teamId] ?? null;
    if (!is_array($teamEvents) || empty($teamEvents['active'])) {
        throw new AppError('Aucun événement actif pour votre équipe.');
    }
    $active = $teamEvents['active'];
    if (($active['id'] ?? '') !== $eventId) {
        throw new AppError('Cet événement n’est plus actif.', 409);
    }

    $catalog = event_catalog();
    $catalogId = (string) ($active['event_id'] ?? '');
    if (!isset($catalog[$catalogId]['options'][$optionIndex])) {
        throw new AppError('Option inconnue.');
    }
    $option = $catalog[$catalogId]['options'][$optionIndex];

    $team = load_team($teamId);
    $notes = apply_event_effects($team, $teamId, $option['effets']);
    with_locked_json(team_file_path($teamId), function (array &$data) use ($team): void {
        assert_simulation_running(session_phase(current_session()));
        $data = $team;
    }, $team);

    $consequence = $option['resultat'] . ($notes !== [] ? ' ' . implode(' ', $notes) : '');
    $resolved = [];
    with_locked_json(EVENTS_FILE, function (array &$data) use ($teamId, $eventId, $option, $consequence, $user, &$resolved): void {
        assert_simulation_running(session_phase(current_session()));
        $key = (string) $teamId;
        $active = $data['teams'][$key]['active'] ?? null;
        if ($active === null || ($active['id'] ?? '') !== $eventId) {
            throw new AppError('Cet événement n’est plus actif.', 409);
        }
        $entry = [
            'event_id' => $active['event_id'],
            'titre' => $active['titre'],
            'description' => $active['description'],
            'triggered_at' => $active['triggered_at'],
            'choice' => $option['label'],
            'consequence' => $consequence,
            'responded_at' => iso_now(),
            'responded_by' => (string) $user['login'],
        ];
        $data['teams'][$key]['history'][] = $entry;
        $data['teams'][$key]['active'] = null;
        $data['revision'] = (int) ($data['revision'] ?? 0) + 1;
        $data['updated_at'] = iso_now();
        $resolved = $entry;
    }, ['teams' => [], 'revision' => 0]);

    history_add($teamId, $user['login'], 'event_response', 'Décision sur l’événement « ' . $resolved['titre'] . ' » : ' . $option['label'] . '.', [
        'event_id' => $catalogId,
        'choice' => $option['label'],
        'consequence' => $consequence,
    ]);
    return ['message' => 'Décision enregistrée. ' . $consequence];
}


// ===========================================================================
// Modération des contenus par le formateur
// ===========================================================================

/**
 * Règles de longueur des champs modérables.
 * Retourne [longueur mini, longueur maxi, libellé].
 */
function moderation_rules(string $type, string $field): array
{
    $rules = [
        'chat' => [
            'text' => [1, CHAT_MAX_LENGTH, 'Message'],
        ],
        'task' => [
            'titre' => [3, 120, 'Titre de la tâche'],
            'description' => [1, 500, 'Description de la tâche'],
            'blocage' => [3, 300, 'Motif de blocage'],
        ],
        'story' => [
            'refus_commentaire' => [5, 500, 'Commentaire de refus'],
        ],
        'sprint_goal' => [
            'sprint_goal' => [10, 300, 'Sprint Goal'],
            'sprint_goal_justification' => [10, 500, 'Justification du Sprint Goal'],
        ],
        'retrospective' => [
            'retrospective' => [10, 1000, 'Rétrospective collective'],
        ],
        'announcement' => [
            'text' => [3, 500, 'Annonce'],
        ],
    ];

    if (!isset($rules[$type][$field])) {
        throw new AppError('Type de contenu ou champ non modérable.', 400);
    }
    return $rules[$type][$field];
}

/**
 * Valide une demande de modération et retourne une cible normalisée.
 */
function moderation_target(array $input): array
{
    $type = require_choice($input['type'] ?? null, ['chat', 'task', 'story', 'sprint_goal', 'retrospective', 'announcement'], 'Type de contenu');
    $field = require_text($input['field'] ?? '', 1, 40, 'Champ');
    moderation_rules($type, $field);

    $teamId = require_int($input['team'] ?? null, 0, 3, 'Équipe');
    if ($type === 'announcement') {
        $teamId = 0;
    } elseif ($teamId < 1) {
        throw new AppError('Une équipe doit être précisée pour ce contenu.', 400);
    }

    $contentId = require_text($input['content_id'] ?? '', 1, 30, 'Contenu');
    $patterns = [
        'chat' => '/^M[0-9]{1,10}$/',
        'task' => '/^T[0-9]{1,10}$/',
        'story' => '/^US[0-9]{1,10}$/',
        'sprint_goal' => '/^goal$/',
        'retrospective' => '/^retro$/',
        'announcement' => '/^A[0-9]{1,10}$/',
    ];
    if (!preg_match($patterns[$type], $contentId)) {
        throw new AppError('Identifiant de contenu invalide.', 400);
    }

    $index = null;

    return [
        'type' => $type,
        'team' => $teamId,
        'content_id' => $contentId,
        'field' => $field,
        'index' => $index,
    ];
}

/**
 * Libellé lisible d'une cible de modération, pour l'historique.
 */
function moderation_label(array $target): string
{
    $id = $target['content_id'];
    return match ($target['type'] . ':' . $target['field']) {
        'chat:text' => 'message de chat ' . $id,
        'task:titre' => 'titre de la tâche ' . $id,
        'task:description' => 'description de la tâche ' . $id,
        'task:blocage' => 'blocage de la tâche ' . $id,
        'story:refus_commentaire' => 'commentaire de refus de la User Story ' . $id,
        'sprint_goal:sprint_goal' => 'Sprint Goal de l’équipe ' . $target['team'],
        'sprint_goal:sprint_goal_justification' => 'justification du Sprint Goal de l’équipe ' . $target['team'],
        'retrospective:retrospective' => 'rétrospective de l’équipe ' . $target['team'],
        'announcement:text' => 'annonce ' . $id,
        default => 'contenu ' . $id,
    };
}

/**
 * Applique une modification ou une suppression logique.
 */
function moderation_apply(array $user, array $input, bool $delete): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }

    $target = moderation_target($input);
    [$min, $max, $label] = moderation_rules($target['type'], $target['field']);
    $newText = $delete ? null : require_text($input['text'] ?? null, $min, $max, $label);
    $oldText = '';
    $announcementTarget = 0;
    $login = (string) $user['login'];

    if ($target['type'] === 'announcement') {
        with_locked_json(SESSION_FILE, function (array &$session) use ($target, $newText, $delete, $login, &$oldText, &$announcementTarget): void {
            $announcements = $session['announcements'] ?? [];
            if (!is_array($announcements)) {
                throw new RuntimeException('Données d’annonces invalides.');
            }
            $index = null;
            foreach ($announcements as $i => $announcement) {
                if (($announcement['id'] ?? '') === $target['content_id']) {
                    $index = $i;
                    break;
                }
            }
            if ($index === null) {
                throw new AppError('Annonce introuvable.', 404);
            }
            if (!empty($announcements[$index]['deleted'])) {
                throw new AppError('Cette annonce est déjà supprimée.', 409);
            }

            $oldText = (string) ($announcements[$index]['text'] ?? '');
            $announcementTarget = (int) ($announcements[$index]['target'] ?? 0);
            if ($delete) {
                $announcements[$index]['text'] = '';
                $announcements[$index]['deleted'] = true;
                $announcements[$index]['deleted_at'] = iso_now();
                $announcements[$index]['deleted_by'] = $login;
            } else {
                $announcements[$index]['text'] = $newText;
                $announcements[$index]['edited_at'] = iso_now();
                $announcements[$index]['edited_by'] = $login;
            }
            $session['announcements'] = $announcements;
            $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
            $session['updated_at'] = iso_now();
        }, ['announcements' => [], 'revision' => 0]);
    } else {
        with_locked_json(team_file_path((int) $target['team']), function (array &$team) use ($target, $newText, $delete, $login, &$oldText): void {
            assert_team_data($team);
            $index = null;

            if ($target['type'] === 'chat') {
                foreach ($team['messages'] as $i => $message) {
                    if (($message['id'] ?? '') === $target['content_id']) {
                        $index = $i;
                        break;
                    }
                }
                if ($index === null) {
                    throw new AppError('Message introuvable.', 404);
                }
                if (($team['messages'][$index]['author'] ?? '') === 'systeme') {
                    throw new AppError('Les messages du système ne peuvent pas être modérés.', 403);
                }
                if (!empty($team['messages'][$index]['deleted'])) {
                    throw new AppError('Ce message est déjà supprimé.', 409);
                }
                $oldText = (string) ($team['messages'][$index]['text'] ?? '');
                if ($delete) {
                    $team['messages'][$index]['text'] = '';
                    $team['messages'][$index]['deleted'] = true;
                    $team['messages'][$index]['deleted_at'] = iso_now();
                    $team['messages'][$index]['deleted_by'] = $login;
                } else {
                    $team['messages'][$index]['text'] = $newText;
                    $team['messages'][$index]['edited_at'] = iso_now();
                    $team['messages'][$index]['edited_by'] = $login;
                }
            } elseif ($target['type'] === 'task') {
                $index = team_task_index($team, $target['content_id']);
                if ($index === null) {
                    throw new AppError('Tâche introuvable.', 404);
                }
                $field = $target['field'];
                $moderation = $team['tasks'][$index]['moderation'][$field] ?? null;
                if (is_array($moderation) && ($moderation['action'] ?? '') === 'delete') {
                    throw new AppError('Ce contenu est déjà supprimé.', 409);
                }
                $oldText = (string) ($team['tasks'][$index][$field] ?? '');
                if ($delete && trim($oldText) === '') {
                    throw new AppError('Ce contenu est déjà vide ou supprimé.', 409);
                }
                if ($delete) {
                    $team['tasks'][$index][$field] = match ($field) {
                        'titre' => '[Titre supprimé par le formateur]',
                        'description' => '',
                        'blocage' => 'Blocage supprimé par le formateur.',
                        default => '',
                    };
                } else {
                    $team['tasks'][$index][$field] = $newText;
                }
                $team['tasks'][$index]['moderation'][$field] = [
                    'action' => $delete ? 'delete' : 'edit',
                    'by' => $login,
                    'at' => iso_now(),
                ];
                $team['tasks'][$index]['updated_at'] = iso_now();
            } elseif ($target['type'] === 'story') {
                $index = team_story_index($team, $target['content_id']);
                if ($index === null) {
                    throw new AppError('User Story introuvable.', 404);
                }
                $field = $target['field'];
                $moderation = $team['stories'][$index]['moderation'][$field] ?? null;
                if (is_array($moderation) && ($moderation['action'] ?? '') === 'delete') {
                    throw new AppError('Ce contenu est déjà supprimé.', 409);
                }
                $oldText = (string) ($team['stories'][$index][$field] ?? '');
                if ($delete && trim($oldText) === '') {
                    throw new AppError('Ce contenu est déjà vide ou supprimé.', 409);
                }
                $team['stories'][$index][$field] = $delete ? null : $newText;
                $team['stories'][$index]['moderation'][$field] = [
                    'action' => $delete ? 'delete' : 'edit',
                    'by' => $login,
                    'at' => iso_now(),
                ];
            } elseif ($target['type'] === 'sprint_goal') {
                $field = $target['field'];
                $moderation = $team['sprint_goal_moderation'][$field] ?? null;
                if (is_array($moderation) && ($moderation['action'] ?? '') === 'delete') {
                    throw new AppError('Ce contenu est déjà supprimé.', 409);
                }
                $oldText = (string) ($team[$field] ?? '');
                if ($delete && trim($oldText) === '') {
                    throw new AppError('Ce contenu est déjà vide ou supprimé.', 409);
                }
                if ($delete) {
                    $team[$field] = $field === 'sprint_goal'
                        ? '[Sprint Goal supprimé par le formateur]'
                        : '';
                } else {
                    $team[$field] = $newText;
                }
                $team['sprint_goal_moderation'][$field] = [
                    'action' => $delete ? 'delete' : 'edit',
                    'by' => $login,
                    'at' => iso_now(),
                ];
                if (in_array($field, ['sprint_goal', 'sprint_goal_justification'], true)) {
                    // La modération ne change pas l'auteur initial du Sprint Goal.
                    $team['sprint_goal_updated_at'] = iso_now();
                }
            } elseif ($target['type'] === 'retrospective') {
                $field = 'retrospective';
                $moderation = $team['retrospective_moderation'][$field] ?? null;
                if (is_array($moderation) && ($moderation['action'] ?? '') === 'delete') {
                    throw new AppError('Cette rétrospective est déjà supprimée.', 409);
                }
                $oldText = (string) ($team[$field] ?? '');
                if ($delete && trim($oldText) === '') {
                    throw new AppError('Cette rétrospective est déjà vide ou supprimée.', 409);
                }
                $team[$field] = $delete ? '[Rétrospective supprimée par le formateur]' : $newText;
                $team['retrospective_moderation'][$field] = [
                    'action' => $delete ? 'delete' : 'edit',
                    'by' => $login,
                    'at' => iso_now(),
                ];
                // La modération ne change pas l'auteur initial de la rétrospective.
                $team['retrospective_updated_at'] = iso_now();
            }

            $team['revision'] = (int) ($team['revision'] ?? 0) + 1;
            $team['updated_at'] = iso_now();
        }, ['members' => [], 'stories' => [], 'tasks' => [], 'messages' => [], 'swaps' => []]);
    }

    $labelText = moderation_label($target);
    $actionLabel = $delete ? 'Suppression' : 'Modification';
    $historyTeam = $target['type'] === 'announcement' ? $announcementTarget : (int) $target['team'];
    history_add(
        $historyTeam,
        $login,
        $delete ? 'moderation_delete' : 'moderation_edit',
        $actionLabel . ' par le formateur : ' . $labelText . '.',
        [
            'content_type' => $target['type'],
            'content_id' => $target['content_id'],
            'field' => $target['field'],
            'index' => $target['index'],
            'old' => $oldText,
            'new' => $delete ? null : $newText,
        ]
    );

    return [
        'message' => $actionLabel . ' enregistrée.',
        'moderation' => [
            'content_type' => $target['type'],
            'content_id' => $target['content_id'],
            'field' => $target['field'],
            'deleted' => $delete,
        ],
    ];
}

/**
 * Modification d'un contenu textuel par le formateur.
 */
function action_moderate_edit(array $user, array $input): array
{
    return moderation_apply($user, $input, false);
}

/**
 * Suppression logique d'un contenu textuel par le formateur.
 */
function action_moderate_delete(array $user, array $input): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    if (trim((string) ($input['confirmation'] ?? '')) !== 'JE CONFIRME LA SUPPRESSION DE CE CONTENU') {
        throw new AppError('Confirmation de suppression requise : saisissez la phrase exacte.', 409);
    }
    return moderation_apply($user, $input, true);
}

// ===========================================================================
// Actions du formateur
// ===========================================================================

/**
 * Suspend la simulation.
 */
function action_teacher_pause(array $user): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    $session = current_session();
    if (session_phase($session) !== 'running') {
        throw new AppError('La simulation ne peut être suspendue que pendant son déroulement.', 409);
    }
    with_locked_json(SESSION_FILE, function (array &$session) use ($user): void {
        if (session_phase($session) !== 'running') {
            throw new AppError('La simulation n’est plus dans un état permettant la pause.', 409);
        }
        $session['paused'] = true;
        $session['paused_at'] = iso_now();
        $session['paused_by'] = (string) $user['login'];
        $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
        $session['updated_at'] = iso_now();
    });
    history_add(0, $user['login'], 'pause', 'Simulation suspendue par le formateur.');
    return ['message' => 'Simulation suspendue.'];
}

/**
 * Reprend la simulation.
 */
function action_teacher_resume(array $user): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    $session = current_session();
    if (session_phase($session) !== 'paused') {
        throw new AppError('La simulation n’est pas en pause.', 409);
    }
    with_locked_json(SESSION_FILE, function (array &$session): void {
        if (session_phase($session) !== 'paused') {
            throw new AppError('La simulation ne peut plus être reprise.', 409);
        }
        $session['paused'] = false;
        $session['paused_at'] = null;
        $session['paused_by'] = null;
        $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
        $session['updated_at'] = iso_now();
    });
    history_add(0, $user['login'], 'resume', 'Simulation reprise par le formateur.');
    return ['message' => 'Simulation reprise.'];
}

/**
 * Envoi d'une annonce globale ou ciblée.
 */
function action_teacher_announce(array $user, mixed $target, mixed $text): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    $target = require_int($target, 0, 3, 'Destinataire');
    $text = require_text($text, 3, 500, 'Annonce');

    $announcement = [];
    with_locked_json(SESSION_FILE, function (array &$session) use ($target, $text, $user, &$announcement): void {
        $number = (int) ($session['next_announcement_id'] ?? 1);
        $announcement = [
            'id' => 'A' . $number,
            'target' => $target,
            'author' => (string) $user['login'],
            'at' => iso_now(),
            'ts' => time(),
            'text' => $text,
        ];
        $session['announcements'][] = $announcement;
        $session['next_announcement_id'] = $number + 1;
        $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
        $session['updated_at'] = iso_now();
    });

    $where = $target === 0 ? 'à toutes les équipes' : 'à l’équipe ' . $target;
    history_add($target, $user['login'], 'announcement', 'Annonce ' . $where . ' : ' . $text, [
        'announcement' => $announcement['id'],
        'target' => $target,
    ]);
    return ['message' => 'Annonce envoyée.', 'announcement' => $announcement];
}

/**
 * Arrête la session après son expiration validée par le formateur.
 */
function action_teacher_stop(array $user): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    $session = current_session();
    if (session_phase($session) !== 'finished') {
        throw new AppError('La simulation n’est pas terminée.', 409);
    }
    with_locked_json(SESSION_FILE, function (array &$session) use ($user): void {
        $session['stopped_at'] = iso_now();
        $session['stopped_by'] = (string) $user['login'];
        $session['revision'] = (int) ($session['revision'] ?? 0) + 1;
        $session['updated_at'] = iso_now();
    });
    history_add(0, (string) $user['login'], 'stop', 'Session arrêtée par le formateur.');
    return ['message' => 'Session arrêtée.'];
}

/**
 * Remise à zéro complète, protégée par un mot de confirmation.
 */
function action_teacher_reset(array $user, mixed $confirmation, mixed $date = null): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    if (!is_string($confirmation) || trim($confirmation) !== RESET_WORD) {
        throw new AppError('Saisissez ' . RESET_WORD . ' pour confirmer la remise à zéro.');
    }

    $session = current_session();
    $phase = session_phase($session);
    if (in_array($phase, ['running', 'paused'], true)) {
        throw new AppError('Impossible de réinitialiser pendant une simulation en cours.', 409);
    }

    $day = null;
    if (is_string($date)) {
        $day = trim($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $day = null;
        } else {
            [$y, $m, $d] = explode('-', $day);
            if (!checkdate((int) $m, (int) $d, (int) $y)) {
                $day = null;
            }
        }
    }
    reset_all((string) $user['login'], $day);
    $fresh = current_session();
    return [
        'message' => 'Nouvelle séance initialisée pour le ' . $fresh['date'] . '.',
        'session' => session_public($fresh, session_phase($fresh)),
    ];
}

/**
 * Historique complet pour le formateur.
 */
function teacher_history(array $user): array
{
    if (empty($user['is_teacher'])) {
        throw new AppError('Action réservée au formateur.', 403);
    }
    $data = json_read(HISTORY_FILE, ['entries' => []]);
    $entries = is_array($data['entries'] ?? null) ? $data['entries'] : [];
    return ['entries' => $entries, 'revision' => (int) ($data['revision'] ?? 0)];
}

// ===========================================================================
// Construction de l'état envoyé au navigateur
// ===========================================================================

/**
 * Version publique d'une équipe.
 */
function public_team(array $team, array $events, bool $includeAllMessages = false): array
{
    $teamId = (int) $team['team'];
    $members = [];
    foreach ($team['members'] as $member) {
        $members[] = [
            'login' => (string) $member['login'],
            'role' => (string) $member['role'],
            'role_label' => role_label((string) $member['role']),
        ];
    }

    $swaps = [];
    foreach ($team['swaps'] as $swap) {
        $swap['status'] = swap_effective_status($swap);
        $swaps[] = $swap;
    }

    $archived = 0;
    foreach ($team['tasks'] as $task) {
        if (!empty($task['archived'])) {
            $archived++;
        }
    }

    // Les contenus supprimés par le formateur ne sont jamais transmis dans
    // l'état normal, même s'ils restent tracés dans l'historique.
    $messages = array_values(array_filter(
        $team['messages'],
        static fn(array $message): bool => empty($message['deleted'])
    ));

    // La réponse détaillée du client n'est transmise qu'à la consultation
    // explicite du PO. Le tableau ne reçoit que l'état nécessaire au rendu.
    $publicStories = [];
    foreach ($team['stories'] as $story) {
        $feedback = is_array($story['client_feedback'] ?? null) ? $story['client_feedback'] : null;
        unset($story['client_feedback'], $story['client_feedback_history'], $story['client_last_refusal_negative']);
        $story['client_feedback_consulted'] = !empty($feedback['consulted']);
        $story['client_feedback_position'] = !empty($feedback['consulted'])
            ? (string) ($feedback['position'] ?? '')
            : null;
        $publicStories[] = $story;
    }

    return [
        'id' => $teamId,
        'name' => 'Équipe ' . $teamId,
        'sprint_goal' => (string) $team['sprint_goal'],
        'sprint_goal_justification' => (string) ($team['sprint_goal_justification'] ?? ''),
        'sprint_goal_updated_by' => $team['sprint_goal_updated_by'] ?? null,
        'sprint_goal_updated_at' => $team['sprint_goal_updated_at'] ?? null,
        'sprint_goal_moderation' => is_array($team['sprint_goal_moderation'] ?? null) ? $team['sprint_goal_moderation'] : [],
        'retrospective' => (string) ($team['retrospective'] ?? ''),
        'retrospective_updated_by' => $team['retrospective_updated_by'] ?? null,
        'retrospective_updated_at' => $team['retrospective_updated_at'] ?? null,
        'retrospective_history' => is_array($team['retrospective_history'] ?? null)
            ? array_slice($team['retrospective_history'], -20)
            : [],
        'retrospective_moderation' => is_array($team['retrospective_moderation'] ?? null)
            ? $team['retrospective_moderation']
            : [],
        'members' => $members,
        'stories' => $publicStories,
        'tasks' => array_values($team['tasks']),
        'messages' => $includeAllMessages ? $messages : array_slice($messages, -120),
        'swaps' => array_slice($swaps, -30),
        'capacity' => capacity_info($team),
        'stats' => team_stats($team),
        'client_decisions' => is_array($team['client_decisions'] ?? null) ? array_values($team['client_decisions']) : [],
        'client_decision_stats' => client_decision_stats($team),
        'events' => events_public($events, $teamId),
        'archived_tasks' => $archived,
    ];
}

/**
 * Annonce destinée à un étudiant.
 */
function announcement_visible(array $announcement, int $teamId): bool
{
    if (!empty($announcement['deleted'])) {
        return false;
    }
    $target = (int) ($announcement['target'] ?? 0);
    return $target === 0 || $target === $teamId;
}

/**
 * Calcule le bilan comparatif, sans classement ni note.
 *
 * $onlyTeamId limite le calcul à une seule équipe. Il est utilisé pour le
 * bilan étudiant : les règles et les indicateurs restent strictement les
 * mêmes que pour le bilan formateur, sans exposer les autres équipes.
 */
function build_bilan(array $teams, array $events, array $history, ?int $onlyTeamId = null): array
{
    $entries = is_array($history['entries'] ?? null) ? $history['entries'] : [];
    $result = [];

    foreach ($onlyTeamId === null ? [1, 2, 3] : [$onlyTeamId] as $teamId) {
        if (!isset($teams[$teamId])) {
            continue;
        }
        $team = $teams[$teamId];
        $stats = team_stats($team);
        $capacity = capacity_info($team);
        $teamEvents = $events['teams'][(string) $teamId] ?? ['used' => [], 'history' => [], 'active' => null];

        $priorityChanges = 0;
        $goalChanges = 0;
        $roleSwaps = 0;
        $blockages = 0;
        foreach ($entries as $entry) {
            if ((int) ($entry['team'] ?? 0) !== $teamId) {
                continue;
            }
            switch ($entry['type'] ?? '') {
                case 'priority':
                    $priorityChanges++;
                    break;
                case 'goal_update':
                    $goalChanges++;
                    break;
                case 'swap_accept':
                    $roleSwaps++;
                    break;
                case 'task_block':
                    $blockages++;
                    break;
            }
        }

        $tasksTotal = 0;
        $tasksDone = 0;
        $tasksDoing = 0;
        $tasksBlocked = 0;
        $tasksTodo = 0;
        foreach ($team['tasks'] as $task) {
            if (!empty($task['archived'])) {
                continue;
            }
            $tasksTotal++;
            switch ($task['etat'] ?? '') {
                case 'termine':
                    $tasksDone++;
                    break;
                case 'en_cours':
                    $tasksDoing++;
                    break;
                case 'bloque':
                    $tasksBlocked++;
                    break;
                default:
                    $tasksTodo++;
            }
        }

        $triggered = count($teamEvents['used'] ?? []);
        $resolved = count($teamEvents['history'] ?? []);
        $pending = !empty($teamEvents['active']) ? 1 : 0;
        $adaptation = $priorityChanges + $goalChanges + $resolved + $roleSwaps;

        $result[] = [
            'id' => $teamId,
            'name' => 'Équipe ' . $teamId,
            'sprint_goal' => $team['sprint_goal'],
            'retrospective' => (string) ($team['retrospective'] ?? ''),
            'retrospective_updated_by' => $team['retrospective_updated_by'] ?? null,
            'retrospective_updated_at' => $team['retrospective_updated_at'] ?? null,
            'members' => $team['members'],
            'us_total' => $stats['us_total'],
            'us_selectionnees' => $stats['us_selectionnees'],
            'us_terminees' => $stats['us_validees'] + $stats['us_pretes'],
            'us_validees' => $stats['us_validees'],
            'us_pretes' => $stats['us_pretes'],
            'us_refusees' => $stats['us_refusees'],
            'points_valides' => $stats['points_valides'],
            'valeur_validee' => $stats['valeur_validee'],
            'valeur_selectionnee' => array_sum(array_map(
                static fn(array $story): int => !empty($story['selectionnee']) ? (int) $story['valeur'] : 0,
                $team['stories']
            )),
            'taches_total' => $tasksTotal,
            'taches_terminees' => $tasksDone,
            'taches_en_cours' => $tasksDoing,
            'taches_bloquees' => $tasksBlocked,
            'taches_a_faire' => $tasksTodo,
            'heures_terminees' => $capacity['done_hours'],
            'heures_restantes' => $capacity['remaining_work'],
            'capacite_initiale' => $capacity['initial'],
            'capacite_reduite' => $capacity['reduction'],
            'capacite_restante' => $capacity['remaining_capacity'],
            'blocages_rencontres' => $blockages,
            'changements_priorite' => $priorityChanges,
            'evenements_declenches' => $triggered,
            'evenements_traites' => $resolved,
            'evenements_en_attente' => $pending,
            'decisions' => $resolved,
            'echanges_roles' => $roleSwaps,
            'changements_sprint_goal' => $goalChanges,
            'actions_adaptation' => $adaptation,
            'couverture_sprint' => $stats['us_selectionnees'] > 0
                ? (int) round($stats['us_validees'] / $stats['us_selectionnees'] * 100)
                : 0,
            'client_decisions' => is_array($team['client_decisions'] ?? null) ? array_values($team['client_decisions']) : [],
            'client_decision_stats' => client_decision_stats($team),
        ];
    }

    return ['generated_at' => iso_now(), 'teams' => $result];
}

/**
 * Construit la réponse complète de l'état.
 */
function build_state_payload(array $user, ?string $clientVersion = null): array
{
    bootstrap_storage();
    trigger_events_if_due();

    if (!empty($user['is_teacher'])) {
        foreach ([1, 2, 3] as $teamId) {
            expire_swaps_if_needed($teamId);
        }
    } else {
        expire_swaps_if_needed((int) $user['team']);
    }

    $session = current_session();
    $phase = session_phase($session);
    $events = json_read(EVENTS_FILE, []);
    $teams = [];
    foreach ([1, 2, 3] as $teamId) {
        $teams[$teamId] = load_team($teamId);
    }

    $history = null;
    $bilan = null;
    $studentHistoryPayload = null;
    $passwordRequests = ['revision' => 0, 'requests' => []];
    if (!empty($user['is_teacher'])) {
        $passwordRequests = password_requests_state();
    }
    if ($phase === 'finished' && !empty($user['is_teacher'])) {
        $history = json_read(HISTORY_FILE, ['entries' => [], 'revision' => 0]);
        $bilan = build_bilan($teams, $events, $history);
    }

    $signatureParts = [
        'generation' => (string) ($session['generation'] ?? $session['reset_at'] ?? ''),
        'phase' => $phase,
        'session_rev' => (int) ($session['revision'] ?? 0),
        'events_rev' => (int) ($events['revision'] ?? 0),
        'announcements' => count($session['announcements'] ?? []),
    ];
    if (!empty($user['is_teacher'])) {
        foreach ([1, 2, 3] as $teamId) {
            $signatureParts['team_' . $teamId] = (int) ($teams[$teamId]['revision'] ?? 0);
        }
        if ($history !== null) {
            $signatureParts['history_rev'] = (int) ($history['revision'] ?? 0);
        }
        $signatureParts['password_requests_rev'] = (int) $passwordRequests['revision'];
    } else {
        $teamId = (int) $user['team'];
        $signatureParts['team_' . $teamId] = (int) ($teams[$teamId]['revision'] ?? 0);
        $signatureParts['role'] = team_role($teams[$teamId], (string) $user['login']);
        $studentHistory = json_read(HISTORY_FILE, ['entries' => [], 'revision' => 0]);
        $signatureParts['history_rev'] = (int) ($studentHistory['revision'] ?? 0);
        $challengeData = json_read(CHALLENGES_FILE, ['revision' => 0]);
        $signatureParts['challenge_rev'] = (int) ($challengeData['revision'] ?? 0);
    }
    $version = md5(json_encode($signatureParts));

    $sessionPayload = session_public($session, $phase);
    if ($clientVersion !== null && hash_equals($version, $clientVersion)) {
        return [
            'ok' => true,
            'unchanged' => true,
            'v' => $version,
            'server_time_ms' => now_ms(),
            'session' => $sessionPayload,
        ];
    }

    if (empty($user['is_teacher'])) {
        // Bilan permanent de l'équipe étudiante. Il est calculé uniquement
        // lorsque l'état a changé, avec la même fonction que le formateur.
        $studentHistory = json_read(HISTORY_FILE, ['entries' => [], 'revision' => 0]);
        $bilan = build_bilan($teams, $events, $studentHistory, (int) $user['team']);
        $teamId = (int) $user['team'];
        $teamHistoryEntries = array_values(array_filter(
            is_array($studentHistory['entries'] ?? null) ? $studentHistory['entries'] : [],
            static fn(array $entry): bool => (int) ($entry['team'] ?? 0) === $teamId
        ));
        $studentHistoryPayload = [
            'entries' => $teamHistoryEntries,
            'revision' => (int) ($studentHistory['revision'] ?? 0),
        ];
        $activeChallenge = challenge_active_for_user($user);
    } else {
        $activeChallenge = null;
    }

    $publicAnnouncements = [];
    foreach ($session['announcements'] ?? [] as $announcement) {
        if (!empty($announcement['deleted'])) {
            continue;
        }
        if (empty($user['is_teacher']) && !announcement_visible($announcement, (int) $user['team'])) {
            continue;
        }
        $publicAnnouncements[] = $announcement;
    }

    $payload = [
        'ok' => true,
        'v' => $version,
        'server_time_ms' => now_ms(),
        'session' => $sessionPayload,
        'announcements' => $publicAnnouncements,
        'challenge' => $activeChallenge,
        'config' => [
            'poll_ms' => POLL_INTERVAL_MS,
            'chat_max' => CHAT_MAX_LENGTH,
            'reset_word' => !empty($user['is_teacher']) ? RESET_WORD : null,
            'swap_ttl' => SWAP_TTL_SECONDS,
        ],
        'me' => [
            'login' => (string) $user['login'],
            'team' => (int) $user['team'],
            'is_teacher' => !empty($user['is_teacher']),
            'role' => !empty($user['is_teacher'])
                ? 'FORMATEUR'
                : team_role($teams[(int) $user['team']], (string) $user['login']),
            'role_label' => !empty($user['is_teacher'])
                ? 'Formateur'
                : role_label((string) team_role($teams[(int) $user['team']], (string) $user['login'])),
            'initial_role' => !empty($user['is_teacher'])
                ? 'FORMATEUR'
                : (string) ($user['initial_role'] ?? ''),
            'initial_role_label' => !empty($user['is_teacher'])
                ? 'Formateur'
                : role_label((string) ($user['initial_role'] ?? '')),
        ],
    ];

    if (!empty($user['is_teacher'])) {
        $publicTeams = [];
        foreach ([1, 2, 3] as $teamId) {
            // Le formateur reçoit tous les messages pour pouvoir modérer
            // l'intégralité du chat, pas seulement les 120 derniers.
            $publicTeams[] = public_team($teams[$teamId], $events, true);
        }
        $payload['teams'] = $publicTeams;
        $payload['bilan'] = $bilan;
        $payload['password_requests'] = $passwordRequests['requests'];
        $payload['definition_of_done'] = definition_of_done();
    } else {
        $teamId = (int) $user['team'];
        $payload['team'] = public_team($teams[$teamId], $events);
        $payload['bilan'] = $bilan;
        if ($studentHistoryPayload !== null) {
            $payload['history'] = $studentHistoryPayload;
        }
        $payload['definition_of_done'] = definition_of_done();
    }

    return $payload;
}
