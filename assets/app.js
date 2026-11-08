/* ==========================================================================
   WorkFlow IT — application cliente
   JavaScript natif, sans framework, synchronisé par api.php.
   ========================================================================== */
'use strict';

(function () {
    const CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    const shell = document.getElementById('app-shell');
    const viewRoot = document.getElementById('view-root');
    const flashZone = document.getElementById('flash-zone');
    const networkStatus = document.getElementById('network-status');
    const isTeacher = document.body.dataset.teacher === '1';
    const clockFormatter = new Intl.DateTimeFormat('fr-FR', {
        timeZone: 'Europe/Paris',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });

    // Environnement commun à tous les défis terminaux. Il est volontairement
    // statique et émulé côté client : aucun accès au système de fichiers réel.
    const TERMINAL_DIRS = ['backend', 'frontend', 'api', 'logs', 'projects'];
    // Marge réseau au-dessus du contrôle serveur de 2 s entre deux commandes.
    const TERMINAL_COMMAND_DELAY_MS = 2100;

    const App = {
        state: null,
        v: null,
        activeChallenge: null,
        challengePassedTasks: new Set(),
        view: shell ? shell.dataset.view : (isTeacher ? 'global' : 'dashboard'),
        pollDelay: 2000,
        syncStatus: 'syncing',
        nextSyncAt: 0,
        syncStartedAt: 0,
        syncDuration: 2000,
        pollTimer: null,
        clockTimer: null,
        offset: 0,
        polling: false,
        actionBusy: false,
        lastNetworkError: '',
        terminalChallengeId: null,
        terminalLastAt: 0,
        terminalPrintedLevel: 0,
        terminalBusy: false,
        terminalHistory: [],
        terminalHistoryIndex: 0,
        terminalCooldownTimer: null,
        history: null,
        historyLoading: false,
        historyFilter: 'all',
        seenAnnouncements: 0,
        announcementKey: '',
        generation: null,
    };

    let uiDialogState = null;

    // Aide contextualisée : un contenu par outil étudiant, affiché dans une
    // seule modale réutilisable pour éviter de multiplier les fenêtres.
    const TOOL_HELP = {
        dashboard: {
            title: 'Tableau de bord',
            purpose: 'Il rassemble les signaux utiles pour piloter le Sprint : Sprint Goal, capacité restante, charge des tâches, reste à faire, valeur métier validée, Story Points validés, User Stories sélectionnées, événement actif, échanges de rôles et annonces. Il évite de travailler à l’aveugle et aide l’équipe à garder le même cap.',
            roles: 'Tous les membres de l’équipe le consultent. Les actions proposées dépendent du rôle : seul le PO modifie les priorités et valide les User Stories, seuls les Developers gèrent les tâches, et tout le monde peut participer aux décisions collectives.',
            byRole: {
                PO: 'Vous y vérifiez la valeur métier des User Stories, les User Stories à valider et la cohérence du Sprint Goal. La priorisation et la validation se font dans le Product Backlog.',
                SCRUM_MASTER: 'Vous y repérez les blocages, la surcharge, les événements non traités et les échanges en attente. Vous facilitez la discussion sans décider à la place du PO. Vous n’êtes ni chef d’équipe ni décideur : vous ne décidez pas seul du Sprint Backlog et vous ne validez pas les User Stories. Vous aidez à lever un obstacle sans faire le travail des Developers.',
                DEVELOPER: 'Vous y suivez la capacité restante, le reste à faire, les Story Points, les tâches et les blocages pour organiser le travail de réalisation.',
            },
            steps: [
                'Lire le Sprint Goal, les annonces et l’événement en cours.',
                'Comparer la capacité restante avec le reste à faire pour vérifier que l’équipe peut tenir le Sprint.',
                'Repérer les User Stories sélectionnées et leur état.',
                'Suivre la valeur métier validée et les Story Points validés : ils ne prennent en compte que les User Stories validées par le PO.',
                'Utiliser les liens vers la Review, le Backlog ou les Rôles pour agir.',
            ],
            rules: [
                'Les heures et les Story Points sont des unités de simulation, pas un planning réel.',
                'La capacité disponible est la capacité initiale diminuée par les événements.',
                'La capacité restante est le temps encore mobilisable : capacité disponible moins les heures des tâches terminées. La charge des tâches totalise les heures de toutes les tâches actives non archivées ; le reste à faire ne compte que les tâches non terminées.',
                'La valeur métier validée et les Story Points validés additionnent uniquement les User Stories validées par le PO.',
                'Le Sprint Goal se modifie collectivement, avec une justification et au moins deux participants.',
                'Les modifications des équipes sont bloquées pendant une pause ou après la fin de la simulation.',
            ],
            example: 'Si la capacité restante est de 12 h mais que le reste à faire atteint 18 h, l’équipe doit discuter de ce qu’elle retire ou termine plutôt que de tout commencer.',
        },
        review: {
            title: 'Sprint Review et rétrospective',
            purpose: 'La Sprint Review est l’événement où l’équipe inspecte l’incrément et la Definition of Done, puis adapte le Product Backlog avec les parties prenantes. Dans la simulation, cet écran rassemble les éléments nécessaires pour la préparer. La rétrospective, distincte de la Review, sert à décider ensemble ce qui a fonctionné, ce qui a bloqué et ce que l’équipe améliorera ensuite.',
            roles: 'Tous les membres consultent la Review et contribuent à la rétrospective. Le PO présente la valeur validée et les refus, le Scrum Master facilite la rétrospective, les Developers expliquent le travail réellement terminé et conforme à la Definition of Done.',
            byRole: {
                PO: 'Vous présentez la valeur validée, les User Stories acceptées et les refus avec leur motif. La validation elle-même se fait dans le Product Backlog, où l’aide détaille la vérification de la Definition of Done.',
                SCRUM_MASTER: 'Vous facilitez la rétrospective : faits observés, difficultés, causes et améliorations concrètes.',
                DEVELOPER: 'Vous vérifiez ce qui est réellement terminé et conforme à la Definition of Done, expliquez les blocages et proposez des améliorations réalisables.',
            },
            steps: [
                'Lire les indicateurs de Review, la valeur métier des User Stories validées et la Definition of Done.',
                'Examiner les User Stories validées, prêtes, refusées ou encore en travail.',
                'Discuter de l’incrément et des causes des refus.',
                'Rédiger ou compléter la rétrospective collective, puis l’enregistrer.',
            ],
            rules: [
                'Dans la simulation, seule une User Story validée par le PO compte dans l’incrément et dans les indicateurs de valeur métier validée et de Story Points validés.',
                'L’incrément doit être conforme à la Definition of Done ; une User Story « prête à valider » n’est pas encore acceptée.',
                'La Definition of Done est la règle de qualité commune à toutes les User Stories ; elle ne remplace pas leurs critères d’acceptation.',
                'La rétrospective est partagée et conservée dans l’historique de l’équipe.',
                'Aucune validation ne se fait depuis cet écran : utilisez le Product Backlog.',
            ],
            example: 'Une User Story est prête, mais un critère d’acceptation n’est pas démontré : le PO la refuse avec un motif précis, puis la rétrospective note la cause du problème. L’incrément et les indicateurs de valeur validée restent inchangés jusqu’à une validation ultérieure.',
        },
        backlog: {
            title: 'Product Backlog',
            purpose: 'Il contient, sous forme de liste ordonnée, toutes les User Stories candidates avec leur priorité, leur valeur métier, leur estimation en Story Points et leur état. C’est la source de référence pour choisir ce qui entre dans le Sprint et ce qui sera accepté par le PO.',
            roles: 'Le PO priorise et valide. Les Developers estiment et sélectionnent avec l’équipe. Le Scrum Master consulte et participe aux décisions. Tous les membres peuvent sélectionner ou retirer une User Story du Sprint.',
            byRole: {
                PO: 'Vous vous appuyez sur la valeur métier et la priorité pour ordonner le backlog, puis vous validez ou refusez les User Stories. Vous ne saisissez pas les Story Points et vous ne créez pas les tâches techniques.',
                SCRUM_MASTER: 'Vous consultez le backlog et participez aux choix du Sprint. Vous ne décidez pas seul du contenu du Sprint Backlog, ne priorisez pas, n’estimez pas et ne validez pas à la place du PO.',
                DEVELOPER: 'Vous estimez la complexité des User Stories avec la suite de Fibonacci et déclarez une User Story prête à valider lorsqu’elle respecte la Definition of Done. Seul le Developer qui a traité une tâche terminée de cette User Story peut la déclarer prête ; un challenge technique (terminal) est alors requis. Vous ne décidez pas de la priorité ni de l’acceptation finale.',
            },
            steps: [
                'Lire la description, la valeur métier et les critères d’acceptation.',
                'Pour le PO : choisir la priorité et réordonner le backlog selon la valeur et le besoin.',
                'Pour les Developers : estimer la complexité en 1, 2, 3, 5, 8 ou 13 Story Points, sans convertir en heures.',
                'Sélectionner les User Stories à traiter pendant le Sprint.',
                'Quand la User Story possède au moins une tâche, que toutes ses tâches sont terminées et que la Definition of Done est satisfaite, les Developers la déclarent prête (challenge technique requis) ; le PO valide ou refuse.',
            ],
            rules: [
                'La priorité et l’ordre relèvent du PO ; l’estimation relève des Developers.',
                'La valeur métier exprime l’importance pour le client ; les Story Points expriment la complexité. Une forte valeur ne signifie pas une estimation élevée, et inversement.',
                'Les Story Points ne se convertissent pas en heures : les heures servent uniquement à estimer les tâches techniques.',
                'La valeur métier validée et les Story Points validés ne prennent en compte que les User Stories validées par le PO.',
                'Une User Story validée ne peut plus être retirée du Sprint.',
                'Une User Story avec des tâches actives doit d’abord voir ses tâches archivées avant de quitter le Sprint.',
                'Une User Story ne peut recevoir qu’une seule tâche active : pour en ajouter une nouvelle, l’ancienne doit être archivée.',
                'Seul le Developer qui a traité une tâche terminée de la User Story peut la déclarer prête (challenge technique requis).',
                'Les actions de la colonne « Actions » (priorité, estimation, sélection, déclaration prête, validation, refus) sont confirmées par une modale avant exécution ; si elles ne sont pas effectuées, la User Story peut rester invisible pour les Developers dans le Kanban et le cycle Scrum s’interrompt.',
                'Un refus doit contenir un motif compréhensible et actionnable. Le motif du client (si disponible) est affiché en infobulle dans le champ du motif de refus.',
                'Dans le Kanban, le détail d’une tâche (description et critères d’acceptation de la User Story associée) est visible en infobulle sur le champ description du formulaire de modification.',
                'Les colonnes « Estimation » et « Valeur » disposent d’une bulle d’aide au survol.',
                'Le cycle complet est : choisir et prioriser les User Stories, les sélectionner dans le Sprint, créer et faire évoluer les tâches Kanban, déclarer la User Story prête, puis la valider ou la refuser. Chaque étape rend la suivante possible.',
            ],
            acceptance: {
                intro: 'Les Developers déclarent une User Story prête, mais c’est le PO qui décide de l’accepter. Une User Story n’est pas validée automatiquement parce que ses tâches sont marquées « Terminé » : il faut vérifier les critères d’acceptation, la Definition of Done et l’absence de défaut bloquant. En cas de refus, indiquez précisément le critère ou le point de DoD non satisfait.',
                questions: [
                    'Tous les critères d’acceptation de la User Story sont-ils satisfaits ?',
                    'Le travail demandé est-il effectivement réalisé dans le cadre de la simulation ?',
                    'Les tâches nécessaires sont-elles terminées ?',
                    'La User Story est-elle suffisamment cohérente et complète pour être présentée ?',
                    'Existe-t-il un défaut bloquant connu ?',
                    'Le résultat respecte-t-il les règles communes de la Definition of Done ?',
                ],
                showDod: true,
                examples: [
                    {
                        title: 'Acceptée — US03 Catégoriser une demande',
                        text: 'Les trois critères sont vérifiés : les catégories sont proposées, la catégorie choisie est enregistrée et elle reste visible. Les tâches sont terminées, aucun défaut bloquant ne subsiste et l’équipe peut le démontrer.',
                    },
                    {
                        title: 'Refusée pour un critère non respecté — US01 Se connecter',
                        text: 'Le message d’échec révèle que l’identifiant est inconnu, alors que le critère exige un message générique. Même si les tâches sont terminées, la User Story doit être refusée.',
                    },
                    {
                        title: 'Refusée pour un défaut bloquant — US02 Créer une demande',
                        text: 'Le formulaire semble fonctionner, mais la demande créée n’apparaît pas dans la liste de son auteur. Ce défaut empêche de démontrer le résultat : la User Story doit être refusée.',
                    },
                    {
                        title: 'Refusée malgré des tâches terminées — US04 Consulter l’état de ses demandes',
                        text: 'Toutes les tâches sont terminées, mais l’équipe n’a pas vérifié qu’un auteur ne voit que ses propres demandes. La Definition of Done n’est pas satisfaite : la User Story doit être refusée.',
                    },
                ],
                reminder: 'La Definition of Done est une règle commune à l’équipe : elle définit le niveau de qualité attendu pour considérer un travail comme terminé. Elle s’applique à toutes les User Stories et ne remplace pas leurs critères d’acceptation propres. La liste exacte est affichée ci-dessus et dans Sprint Review. Ne créez pas une seconde Definition of Done.',
            },
            example: 'Une User Story à 8 Story Points peut avoir une valeur moyenne : les Developers estiment sa complexité, tandis que le PO décide de sa priorité selon la valeur pour le client.',
        },
        kanban: {
            title: 'Kanban',
            purpose: 'Il montre le flux de travail des tâches techniques dans quatre colonnes : À faire, En cours, Bloqué et Terminé. Il rend visibles la charge, les blocages et ce qui reste à réaliser. La charge totale additionne les heures estimées des tâches actives non archivées ; le reste à faire ne compte que les tâches non terminées.',
            roles: 'Les Developers créent, modifient, affectent et déplacent les tâches. Le PO et le Scrum Master consultent et peuvent signaler un blocage, mais ne réalisent pas le travail technique.',
            byRole: {
                PO: 'Vous consultez l’avancement et pouvez signaler un blocage. Vous ne créez, ne modifiez, ne déplacez et n’archivez pas les tâches.',
                SCRUM_MASTER: 'Vous suivez le flux, les blocages et la surcharge. Vous pouvez signaler un blocage pour rendre une difficulté visible. Vous n’êtes pas chef d’équipe : vous aidez à identifier et à lever les obstacles sans déplacer les tâches à la place des Developers, et vous ne décidez pas seul du Sprint Backlog.',
                DEVELOPER: 'Vous créez les tâches, renseignez leur estimation en heures, les affectez, les déplacez et les archivez si nécessaire. Cette estimation de charge est distincte des Story Points de la User Story.',
            },
            simulation: {
                intro: 'Le Kanban de WorkFlow IT est un support pédagogique pour apprendre à suivre et à faire évoluer un flux de travail. L’application ne développe pas le produit à votre place : aucun code, fichier ou livrable technique n’est produit lorsqu’une carte change de colonne.',
                facts: [
                    'Le traitement des tâches est simulé dans le cadre de l’exercice.',
                    'Les Developers doivent se comporter comme s’ils réalisaient réellement le travail : analyser, progresser, décider et rendre compte.',
                    'Déplacer une carte ne remplace pas le travail : le changement d’état doit refléter une progression réelle dans la simulation.',
                    'Une carte « Terminé » signifie que le travail correspondant est considéré comme réalisé et démontrable, pas seulement que la carte a été déplacée.',
                ],
            },
            developerDuties: [
                'Prendre connaissance des tâches à réaliser et de la User Story associée.',
                'Comprendre le travail attendu, ses critères d’acceptation et son lien avec la Definition of Done.',
                'Estimer le travail nécessaire lorsque cela est demandé.',
                'Commencer le traitement d’une tâche en la faisant passer à « En cours ».',
                'Faire évoluer son état en fonction de la situation réelle simulée.',
                'Pour passer une tâche en « Terminé », utiliser le bouton « Modifier » : un challenge technique est alors requis.',
                'La passer à « Bloqué » lorsqu’un obstacle réel empêche de poursuivre le traitement.',
                'Faire correspondre chaque changement d’état à l’avancement du travail, sans déplacement arbitraire.',
            ],
            blocked: {
                intro: 'Une tâche est réellement bloquée lorsqu’un obstacle empêche de poursuivre son traitement. « Bloqué » n’est pas un synonyme de « difficile », « long » ou « désagréable ». Le serveur peut aussi engendrer automatiquement un blocage ponctuel sur une tâche en cours, avec un motif cohérent et une notification dans le chat ; le Developer résout alors le blocage comme pour un blocage manuel.',
                states: [
                    'À faire : la tâche est prévue, mais le travail n’a pas encore commencé.',
                    'En cours : le travail a commencé et peut avancer.',
                    'Bloqué : un obstacle réel empêche toute progression.',
                    'Terminé : le travail est considéré comme réalisé et démontrable.',
                ],
                examples: [
                    'Une information indispensable manque et personne ne peut la fournir immédiatement.',
                    'Une dépendance technique empêche de poursuivre le développement.',
                    'Un problème de sécurité nécessite une décision ou une intervention extérieure.',
                    'L’environnement de développement est indisponible.',
                    'Une autre tâche ou une décision préalable doit être réalisée avant de pouvoir continuer.',
                    'Un problème technique non résolu empêche toute progression.',
                ],
                notBlocked: 'Une tâche difficile, longue ou simplement désagréable n’est pas automatiquement bloquée : elle reste « En cours » tant que le travail peut avancer.',
                actions: [
                    'Identifier clairement la cause du blocage.',
                    'Renseigner une explication compréhensible dans le champ prévu.',
                    'Signaler le blocage à l’équipe, par exemple dans le chat.',
                    'Contribuer à sa résolution, avec l’aide éventuelle du Scrum Master ou d’un autre membre.',
                    'Reprendre le traitement et changer l’état dès que l’obstacle est levé.',
                ],
            },
            steps: [
                'Créer une tâche depuis une User Story sélectionnée dans le Sprint.',
                'Renseigner un titre, une description facultative, une estimation de 0,5 à 2 h, un Developer affecté et un état.',
                'Déplacer la tâche avec le sélecteur d’état ; pour « Bloqué », saisir une explication. Un changement d’état doit refléter l’avancement du travail, pas une simple manipulation de l’interface.',
                'Comparer la charge totale, le reste à faire et la capacité restante du tableau de bord pour anticiper une surcharge.',
                'Archiver une tâche devenue inutile : elle n’est jamais supprimée définitivement.',
            ],
            rules: [
                'Une tâche doit être rattachée à une User Story sélectionnée et non validée.',
                'Seuls les Developers peuvent créer, modifier, déplacer ou archiver une tâche.',
                'La charge totale est la somme des heures de toutes les tâches actives non archivées ; le reste à faire est la somme des heures des tâches non terminées.',
                'L’estimation d’une tâche est une charge en heures, distincte de la valeur métier et des Story Points de la User Story.',
                'Un blocage doit être expliqué en quelques mots concrets.',
                'Repasser une tâche terminée en travail remet la User Story en travail si elle était prête à valider.',
                'Un badge apparaît sur l’onglet Kanban lorsqu’une ou plusieurs User Stories sont sélectionnées et ne possèdent pas encore de tâche Kanban. Il indique le nombre de nouvelles User Stories disponibles dans l’outil « Nouvelle tâche », afin qu’aucun Developer ne passe à côté de la sélection effectuée dans le Product Backlog.',
            ],
            indicatorsIntro: 'La décision du client n’est pas purement aléatoire : elle tient compte d’indicateurs observés sur la tâche et sur la façon dont elle a été traitée. Ces points expliquent ce qui peut faire pencher la décision vers l’acceptation ou le refus, afin que l’équipe agisse en amont sur la qualité du travail. Un seul point déclenché suffit à augmenter le risque de refus ; les critères ne se cumulent pas.',
            indicators: [
                { label: 'Temps passé dans le terminal', text: 'Plus de 300 secondes entre le démarrage du défi et sa réussite : critère de risque déclenché pour la décision du client.' },
                { label: 'Erreur au mini-quiz', text: 'Au moins une tentative erronée au mini-quiz de la tâche : critère de risque déclenché.' },
                { label: 'Temps passé à « À faire »', text: 'Tâche restée plus de 90 secondes à l’état « À faire » : critère de risque déclenché.' },
                { label: 'Durée réelle de la tâche', text: 'Cycle de vie de la tâche (created_at → updated_at) inférieur à 30 secondes : critère de risque déclenché. Le champ « heures » n’est pas utilisé.' },
                { label: 'Description de la tâche', text: 'Description de moins de 15 caractères : critère de risque déclenché.' },
                { label: 'Effet sur la décision du client', text: 'Aucun critère déclenché : 70 % d’acceptation et 30 % de refus. Au moins un critère déclenché : 40 % d’acceptation et 60 % de refus. Un seul critère suffit : ils ne se cumulent pas.' },
                { label: 'Données absentes', text: 'Un critère dont la donnée est absente ou inexploitable est ignoré : il n’entraîne jamais à lui seul un risque de refus.' },
                { label: 'Reprise après un refus', text: 'Après un premier refus du client, la seconde présentation de la tâche est acceptée à 100 %.' },
            ],
            statistics: 'Le bouton « Statistiques » du Kanban ouvre les statistiques des décisions du client pour l’équipe : nombre de tâches décidées, acceptations, refus, décisions avec et sans risque, taux par développeur et critères de risque déclenchés. Ces indicateurs permettent de suivre la qualité du travail réalisé et d’identifier les axes d’amélioration avant la validation finale. Ils alimentent aussi le palmarès généré automatiquement à la fin de la simulation.',
            example: 'Une tâche « Créer le formulaire de demande » est estimée à 2 h, affectée à un Developer, puis déplacée de À faire vers En cours. Si une information indispensable manque, elle passe en Bloqué avec la raison précise. Si elle est seulement difficile ou longue, elle reste En cours.',
        },
        taskForm: {
            title: 'Nouvelle tâche',
            purpose: 'Une tâche technique est un morceau de travail concret nécessaire pour réaliser une User Story. Les Developers décomposent les User Stories en tâches afin d’organiser, d’estimer et de suivre le travail à réaliser.',
            roles: 'Ce formulaire est réservé aux Developers. Le PO et le Scrum Master consultent le Kanban et peuvent signaler un blocage, mais ne créent ni ne modifient les tâches techniques.',
            byRole: {
                DEVELOPER: 'Vous créez et renseignez les tâches techniques. Une tâche doit être assez claire pour être comprise par un autre Developer et refléter un travail réellement à réaliser.',
            },
            fields: [
                {
                    label: 'User Story',
                    text: 'Choisissez la User Story du Sprint à laquelle la tâche contribue. Une tâche doit rester rattachée au travail attendu.',
                },
                {
                    label: 'Titre',
                    text: 'Indiquez une action précise et compréhensible, par exemple « Créer le formulaire de demande de support ». Évitez les titres vagues comme « Faire le formulaire ».',
                },
                {
                    label: 'Description',
                    text: 'Précisez le travail à effectuer, les éléments attendus et les éventuelles contraintes. Elle est facultative, mais doit être assez claire pour qu’un autre Developer puisse comprendre ou reprendre la tâche.',
                },
                {
                    label: 'Estimation (heures)',
                    text: 'Estimez le temps de travail nécessaire, en heures. Maximum autorisé : 2 h. C’est une estimation de charge propre aux tâches techniques, distincte des Story Points utilisés pour estimer les User Stories.',
                },
                {
                    label: 'Membre affecté',
                    text: 'Désignez éventuellement le Developer qui prendra la tâche. Seuls les membres ayant actuellement le rôle Developer sont proposés. L’affectation est facultative : l’équipe peut s’organiser collectivement et « Non affecté » reste possible.',
                },
                {
                    label: 'État Kanban',
                    text: 'À faire : le travail n’a pas commencé. En cours : la tâche est en cours de traitement. Bloqué : un obstacle empêche de poursuivre. Terminé : le travail prévu est considéré comme réalisé.',
                },
                {
                    label: 'Explication du blocage',
                    text: 'Le champ apparaît lorsque l’état est « Bloqué ». Indiquez la cause concrète : information manquante, dépendance technique, environnement indisponible ou décision nécessaire. Une tâche difficile ou longue n’est pas automatiquement bloquée.',
                },
            ],
            steps: [
                'Choisissez la User Story concernée.',
                'Donnez un titre qui décrit une action précise et son résultat attendu.',
                'Décrivez le travail, estimez-le en heures et affectez-le si utile.',
                'Choisissez l’état initial, le plus souvent « À faire ».',
                'Si l’état est « Bloqué », renseignez immédiatement la cause.',
                'Enregistrez, puis faites évoluer l’état selon l’avancement réel du travail.',
            ],
            rules: [
                'L’application simule le suivi du travail : elle ne réalise pas les tâches techniques et ne produit aucun code ni livrable à la place des Developers.',
                'Même si le travail est simulé, comportez-vous comme si vous participiez réellement au développement : analysez, estimez, traitez et faites évoluer les tâches selon leur avancement réel.',
                'Créer une tâche ne signifie pas que le travail est réalisé.',
                'Une tâche « Terminé » doit correspondre à un travail considéré comme effectivement accompli.',
                'Une tâche doit être suffisamment claire pour être comprise et reprise par un autre Developer.',
                'L’explication du blocage est obligatoire lorsque l’état est « Bloqué ».',
                'Pour déclarer une User Story prête à valider, le Developer doit réussir un challenge technique (terminal) et être l’auteur d’une tâche terminée de cette User Story.',
                'Une User Story ne peut recevoir qu’une seule tâche active : pour en ajouter une nouvelle, l’ancienne doit être archivée.',
            ],
            example: 'Pour la User Story « Créer une demande informatique », un Developer crée la tâche « Créer le formulaire de demande de support », décrit les champs attendus, l’estime à 2 h, l’affecte à un membre et la laisse « À faire ». Il la passera à « En cours » au démarrage, puis à « Terminé » lorsque le travail sera considéré comme réalisé.',
        },
        terminalValidation: {
            title: 'Validation technique (terminal)',
            purpose: 'La validation technique est un petit challenge qui émule l’exécution d’un test à partir d’une commande donnée. Le Developer doit taper la commande attendue dans le terminal simulé pour valider la tâche et déclarer la User Story prête à valider. C’est un mécanisme allégé par rapport à la réalité, mais il permet de challenger le développeur.',
            roles: 'Réservé aux Developers. Le Product Owner et le Scrum Master ne réalisent pas cette validation.',
            byRole: {
                DEVELOPER: 'Vous tapez help pour lister les commandes, puis la commande contenant --help pour obtenir un premier indice. Les indices suivants se débloquent en tâtonnant ; solution apparaît au troisième indice débloqué. Vous pouvez aussi saisir directement la bonne commande.',
            },
            steps: [
                'Taper help pour afficher les commandes disponibles.',
                'Taper la dernière commande de la liste, celle qui contient --help, pour obtenir un premier indice.',
                'Débloquer les deux indices suivants en tâtonnant, ou retrouver la commande complète grâce au premier indice.',
                'Si vous êtes bloqué, débloquez le troisième indice : la commande solution devient alors disponible.',
                'Taper solution pour afficher la commande attendue, puis la copier-coller pour valider.',
                'Vous pouvez aussi saisir directement la bonne commande à tout moment et sauter ces étapes.',
            ],
            rules: [
                'Le terminal est simulé : aucune commande n’est exécutée sur le système réel.',
                'Les indices se débloquent un par un, à chaque tentative incorrecte.',
                'La commande solution n’est disponible qu’après le troisième indice.',
                'Les guillemets de la commande doivent être doubles (") ; la commande est comparée exactement, espaces et ponctuation compris.',
                'Au moins 2 secondes doivent séparer deux commandes.',
                'La validation technique est obligatoire avant de déclarer une User Story prête à valider.',
            ],
            example: 'L’instruction demande de vérifier le point d’entrée d’authentification. Le Developer tape help, puis la commande contenant --help pour obtenir un premier indice. Il débloque ensuite les deux indices suivants, ou retrouve la commande complète. S’il est bloqué, le troisième indice débloque solution, qu’il copie-colle pour valider. Il peut aussi saisir directement la bonne commande à tout moment.',
        },
        events: {
            title: 'Événements',
            purpose: 'Les événements simulent les aléas du projet : nouvelle demande, incident, contrainte RGPD, absence ou dette technique. Chaque événement propose des options et montre leurs conséquences possibles.',
            roles: 'Tous les membres peuvent participer à la décision. Le PO évalue la valeur et l’attente client, le Scrum Master évalue l’impact sur le flux et la capacité, les Developers évaluent la faisabilité technique.',
            byRole: {
                PO: 'Vous arbitrez en priorité selon la valeur client et l’objectif du Sprint.',
                SCRUM_MASTER: 'Vous vérifiez l’impact sur l’organisation, la capacité et les blocages de l’équipe.',
                DEVELOPER: 'Vous vérifiez la faisabilité technique et les conséquences sur le travail en cours.',
            },
            steps: [
                'Lire le titre, la description et les deux options.',
                'Discuter avec l’équipe dans le chat avant de choisir.',
                'Cliquer sur une option puis confirmer la décision.',
                'Consulter l’historique pour retrouver la décision et sa conséquence.',
            ],
            rules: [
                'Les événements sont déclenchés automatiquement par le serveur, pas par le formateur.',
                'Une décision est irréversible et s’applique à toute l’équipe.',
                'Un événement peut réduire la capacité ou modifier les priorités : adaptez le Sprint.',
                'Une seule décision est attendue pour l’événement actif.',
            ],
            example: 'Une absence imprévue réduit la capacité de 3 h : l’équipe peut retirer une User Story du Sprint ou réduire le périmètre des tâches, puis expliquer son choix dans le chat.',
        },
        chat: {
            title: 'Chat et annonces',
            purpose: 'Le chat permet à l’équipe de communiquer, de justifier ses décisions et de signaler une difficulté. Les annonces transmettent les consignes du formateur, globalement ou pour une équipe.',
            roles: 'Tous les membres peuvent écrire dans le chat et lire les annonces. Le formateur peut consulter et modérer les messages ; les étudiants ne modèrent pas.',
            byRole: {
                PO: 'Vous pouvez expliquer un arbitrage, reformuler un critère d’acceptation ou annoncer une décision de priorité.',
                SCRUM_MASTER: 'Vous pouvez reformuler les décisions, faciliter la coordination et rendre les blocages visibles.',
                DEVELOPER: 'Vous pouvez poser une question technique, signaler une dépendance ou proposer une solution.',
            },
            steps: [
                'Lire les annonces du formateur dans le panneau Annonces ou sur le tableau de bord.',
                'Écrire un message clair et court dans le champ prévu.',
                'Envoyer le message ; il apparaît pour l’équipe et pour le formateur.',
            ],
            rules: [
                'Pas de pièces jointes ; les messages sont limités à 500 caractères.',
                'Un court délai est imposé entre deux messages pour éviter le flood.',
                'Le chat est visible par l’équipe et par le formateur, qui peut modérer un contenu.',
                'L’envoi est bloqué pendant une pause et après la fin de la simulation.',
                'Une décision importante doit aussi être reflétée dans l’outil concerné : backlog, Kanban, événements ou rétrospective.',
            ],
            example: 'Avant de choisir une option d’événement, l’équipe échange dans le chat : « Cette option réduit la capacité, nous retirons US04 du Sprint. » Puis elle applique la décision.',
        },
        roles: {
            title: 'Rôles et échanges',
            purpose: 'Cet outil montre la composition de l’équipe et permet de proposer une permutation de rôles lorsque l’organisation du Sprint doit être rééquilibrée.',
            roles: 'Tous les membres peuvent proposer ou recevoir un échange. Le destinataire doit accepter, et le serveur vérifie que la composition Scrum reste valide.',
            byRole: {
                PO: 'Vous pouvez proposer un échange si un autre membre peut assumer le rôle de Product Owner, sans perdre la représentation du client.',
                SCRUM_MASTER: 'Vous pouvez proposer un échange pour rééquilibrer la facilitation et la coordination de l’équipe.',
                DEVELOPER: 'Vous pouvez proposer un échange avec un PO ou un Scrum Master ; un échange entre deux Developers est refusé.',
            },
            steps: [
                'Choisir un membre destinataire dans la liste.',
                'Envoyer la proposition : elle expire après cinq minutes.',
                'Le destinataire accepte ou refuse la proposition.',
                'En cas d’acceptation, les deux rôles permutent immédiatement.',
            ],
            rules: [
                'Chaque équipe conserve exactement un PO, un Scrum Master et au moins un Developer.',
                'Un membre ne peut avoir qu’une seule proposition en attente à la fois.',
                'Un échange entre deux membres ayant déjà le même rôle est refusé.',
                'Une proposition refusée ou expirée ne modifie aucun rôle.',
                'Un Developer ayant des User Stories en attente de validation (tâches terminées non déclarées prêtes) ne peut pas proposer ni accepter un échange : il doit d’abord déclarer ses User Stories prêtes.',
            ],
            example: 'Un Developer propose de prendre le rôle de Scrum Master : si le Scrum Master accepte et que la composition reste valide, les deux rôles permutent.',
        },
        bilan: {
            title: 'Bilan de l’équipe',
            purpose: 'Le bilan rassemble les indicateurs de votre équipe pour suivre les progrès pendant le Sprint, comparer le travail réalisé aux objectifs et préparer le débriefing. Il est consultable dès le début de la simulation et reste disponible après la fin. Il ne classe pas les équipes et ne produit aucune note.',
            roles: 'Tous les membres de l’équipe consultent le même bilan. Il ne donne accès qu’aux résultats de leur propre équipe et ne permet aucune modification.',
            byRole: {
                PO: 'Vous suivez la valeur métier validée, les User Stories acceptées ou refusées et les changements de priorité pour vérifier l’atteinte du Sprint Goal.',
                SCRUM_MASTER: 'Vous repérez les blocages, les événements et les actions d’adaptation pour animer la rétrospective et le débriefing.',
                DEVELOPER: 'Vous suivez les tâches, les heures réalisées, la capacité restante et les blocages pour comprendre l’avancement du travail.',
            },
            steps: [
                'Ouvrir l’onglet Bilan à tout moment du Sprint.',
                'Comparer les User Stories sélectionnées, terminées, validées ou refusées.',
                'Examiner les tâches, les heures, la capacité, les blocages et les événements.',
                'Utiliser les écarts constatés pour adapter les décisions encore possibles.',
                'Après le Sprint, relire le bilan pendant le débriefing et la rétrospective.',
            ],
            rules: [
                'Le bilan est actualisé automatiquement avec les données de l’équipe, sans action supplémentaire.',
                'Les indicateurs et les règles de calcul sont identiques à ceux du bilan du formateur.',
                'Seuls les résultats de votre équipe sont affichés.',
                'Le bilan est consultatif : il ne modifie aucune donnée de la simulation.',
                'Les User Stories prêtes à valider ne sont pas encore validées : seule la validation du PO compte dans la valeur métier et les Story Points validés.',
                'Une équipe ayant terminé beaucoup de tâches n’a pas nécessairement livré plus de valeur métier : la valeur dépend des User Stories validées par le PO.',
                'À la fin de la simulation, le formateur peut exporter le bilan et l’historique au format JSON depuis cet onglet.',
            ],
            indicators: [
                { label: 'User Stories sélectionnées', text: 'Nombre de User Stories cochées dans le Sprint. Calcul : comptage des stories avec sélectionnée = vrai. Utilité : mesurer le périmètre engagé par l’équipe.' },
                { label: 'User Stories terminées (prêtes + validées)', text: 'Nombre de User Stories déclarées prêtes par les Developers plus celles validées par le PO. Calcul : us_pretes + us_validees. Une story prête n’est pas encore acceptée.' },
                { label: 'User Stories validées', text: 'Nombre de User Stories acceptées par le PO selon les critères d’acceptation et la Definition of Done. Calcul : comptage des stories à l’état validée.' },
                { label: 'User Stories refusées', text: 'Nombre de User Stories refusées par le PO. Calcul : comptage des stories à l’état refusée. Le refus implique un commentaire et remet la story en travail.' },
                { label: 'Story Points validés', text: 'Somme des estimations en Story Points des User Stories validées. Calcul : somme des estimations des stories à l’état validée. Les Story Points mesurent la complexité relative, pas les heures.' },
                { label: 'Valeur métier validée', text: 'Somme de la valeur métier des User Stories validées par le PO. Calcul : somme des valeurs des stories à l’état validée. C’est l’indicateur principal de livraison.' },
                { label: 'Valeur métier sélectionnée', text: 'Somme de la valeur métier des User Stories sélectionnées dans le Sprint. Calcul : somme des valeurs des stories avec sélectionnée = vrai. Permet de comparer la valeur engagée à la valeur validée.' },
                { label: 'Tâches terminées', text: 'Nombre de tâches Kanban à l’état Terminé, hors tâches archivées, sur le total des tâches actives. Calcul : tâches terminées / tâches totales. Une tâche terminée est immuable.' },
                { label: 'Tâches en cours', text: 'Nombre de tâches Kanban à l’état En cours, hors tâches archivées. Calcul : comptage des tâches à l’état en_cours.' },
                { label: 'Tâches bloquées', text: 'Nombre de tâches Kanban à l’état Bloqué, hors tâches archivées. Calcul : comptage des tâches à l’état bloque. Un blocage doit être accompagné d’une explication.' },
                { label: 'Tâches à faire', text: 'Nombre de tâches Kanban à l’état À faire, hors tâches archivées. Calcul : comptage des tâches à l’état a_faire.' },
                { label: 'Heures réalisées', text: 'Somme des heures des tâches Kanban terminées et non archivées. Calcul : somme des heures des tâches à l’état termine. C’est du travail déclaré terminé, pas nécessairement validé par le PO.' },
                { label: 'Heures restantes', text: 'Somme des heures des tâches Kanban non terminées et non archivées. Calcul : somme des heures des tâches dont l’état n’est pas termine.' },
                { label: 'Capacité initiale', text: 'Capacité de travail disponible au début du Sprint. Calcul : nombre de Developers actuels × capacité horaire par Developer. Elle ne dépend pas des tâches créées.' },
                { label: 'Capacité réduite par événements', text: 'Heures de capacité retirées par les événements automatiques. Calcul : somme des réductions appliquées, limitée à la capacité initiale. Les événements peuvent modifier la capacité sans changer les heures des tâches.' },
                { label: 'Capacité restante', text: 'Capacité encore mobilisable. Calcul : max(0, capacité disponible ? heures réalisées). Elle indique le temps restant, tandis que les heures restantes mesurent le volume de travail à faire.' },
                { label: 'Blocages rencontrés', text: 'Nombre d’actions de blocage enregistrées dans l’historique de l’équipe. Calcul : comptage des entrées de type task_block. C’est un indicateur d’obstacles rencontrés.' },
                { label: 'Changements de priorité', text: 'Nombre de modifications de priorité de User Stories enregistrées dans l’historique. Calcul : comptage des entrées de type priority.' },
                { label: 'Événements déclenchés', text: 'Nombre d’événements automatiques déclenchés pour l’équipe. Calcul : nombre d’événements utilisés dans le calendrier de l’équipe.' },
                { label: 'Décisions prises', text: 'Nombre d’événements auxquels l’équipe a répondu. Calcul : nombre d’entrées dans l’historique des événements de l’équipe. Un événement non traité n’est pas compté.' },
                { label: 'Échanges de rôles', text: 'Nombre d’échanges de rôles acceptés au sein de l’équipe. Calcul : comptage des entrées de type swap_accept.' },
                { label: 'Changements de Sprint Goal', text: 'Nombre de modifications collectives du Sprint Goal. Calcul : comptage des entrées de type goal_update.' },
                { label: 'Actions d’adaptation', text: 'Indicateur composite des adaptations de l’équipe. Calcul : changements de priorité + changements de Sprint Goal + décisions d’événements + échanges de rôles.' },
                { label: 'Couverture du sprint (US validées / sélectionnées)', text: 'Pourcentage de User Stories validées parmi celles sélectionnées. Calcul : round(us_validees / us_selectionnees × 100), ou 0 si aucune story n’est sélectionnée. Une valeur élevée indique une bonne capacité à transformer le travail engagé en valeur acceptée.' },
            ],
            example: 'Exemple : une équipe sélectionne 4 User Stories dont 2 sont validées. Elle a donc une couverture de 50 %. Si ses tâches terminées totalisent 6 h et sa capacité disponible est de 16 h, la capacité restante est de 10 h. Ces résultats montrent du travail réalisé, mais la valeur métier dépend des 2 User Stories validées par le PO.',
        },
    };

    // -----------------------------------------------------------------------
    // Utilitaires
    // -----------------------------------------------------------------------
    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function num(value) {
        const n = Number(value);
        return Number.isFinite(n) ? n : 0;
    }

    function hours(value) {
        const n = num(value);
        return (Math.round(n * 100) / 100).toLocaleString('fr-FR') + ' h';
    }

    function fmtDateTime(value) {
        if (value === null || value === undefined || value === '') return '—';
        const date = typeof value === 'number' ? new Date(value) : new Date(value);
        if (Number.isNaN(date.getTime())) return '—';
        return date.toLocaleString('fr-FR', {
            timeZone: 'Europe/Paris',
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function fmtTime(value) {
        if (value === null || value === undefined || value === '') return '—';
        const date = typeof value === 'number' ? new Date(value < 1e12 ? value * 1000 : value) : new Date(value);
        if (Number.isNaN(date.getTime())) return '—';
        return date.toLocaleTimeString('fr-FR', {
            timeZone: 'Europe/Paris',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }

    function nowMs() {
        return Date.now() + App.offset;
    }

    function roleLabel(role) {
        return {
            PO: 'Product Owner',
            SCRUM_MASTER: 'Scrum Master',
            DEVELOPER: 'Developer',
            FORMATEUR: 'Formateur',
        }[role] || role || '—';
    }

    function roleBadge(role) {
        return { PO: 'po', SCRUM_MASTER: 'sm', DEVELOPER: 'dev', FORMATEUR: 'formateur' }[role] || 'dev';
    }

    function stateLabel(state) {
        return {
            a_faire: 'À faire',
            en_cours: 'En cours',
            a_valider: 'À valider',
            validee: 'Validée',
            refusee: 'Refusée',
            bloque: 'Bloqué',
            termine: 'Terminé',
        }[state] || state || '—';
    }

    function priorityLabel(priority) {
        return { haute: 'Haute', moyenne: 'Moyenne', basse: 'Basse' }[priority] || priority || '—';
    }

    function kanbanLabel(state) {
        return { a_faire: 'À faire', en_cours: 'En cours', bloque: 'Bloqué', termine: 'Terminé' }[state] || state;
    }

    function swapStatusLabel(status) {
        return {
            pending: 'En attente',
            accepted: 'Acceptée',
            refused: 'Refusée',
            cancelled: 'Annulée',
            expired: 'Expirée',
        }[status] || status || '—';
    }

    function historyTypeLabel(type) {
        return {
            login: 'Connexion',
            logout: 'Déconnexion',
            reset: 'Remise à zéro',
            priority: 'Priorité',
            order: 'Réordonnancement',
            estimation: 'Estimation',
            sprint_select: 'Ajout au Sprint',
            sprint_unselect: 'Retrait du Sprint',
            task_create: 'Création tâche',
            task_update: 'Modification tâche',
            task_move: 'Kanban',
            task_archive: 'Archivage',
            task_block: 'Blocage',
            task_unblock: 'Blocage résolu',
            task_unassign: 'Désaffectation',
            story_ready: 'Prête à valider',
            story_validated: 'Validation',
            story_refused: 'Refus',
            goal_update: 'Sprint Goal',
            retrospective_update: 'Rétrospective',
            event_trigger: 'Événement',
            event_response: 'Décision',
            swap_propose: 'Proposition rôle',
            swap_accept: 'Échange rôle',
            swap_refuse: 'Refus rôle',
            swap_cancel: 'Annulation rôle',
            swap_expired: 'Expiration rôle',
            pause: 'Pause',
            resume: 'Reprise',
            teacher_password: 'Mot de passe formateur',
            password_change: 'Mot de passe personnel',
            password_reset: 'Réinitialisation mot de passe',
            password_request: 'Demande mot de passe',
            announcement: 'Annonce',
            moderation_edit: 'Modération',
            moderation_delete: 'Suppression modérée',
            init: 'Initialisation',
        }[type] || type;
    }

    function phaseClass(phase) {
        return 'phase-' + String(phase || 'closed');
    }

    function userCanWrite() {
        return !!App.state && !App.state.me.is_teacher
            && App.state.session.phase === 'running'
            && !App.state.session.paused
            && nowMs() < num(App.state.session.sim_end_ms);
    }

    function myRole() {
        return App.state ? App.state.me.role : null;
    }

    function myTeam() {
        return App.state && App.state.team ? App.state.team : null;
    }

    function developerIsAffected(team, login) {
        if (!team || !team.stories || !team.tasks) return false;
        for (let i = 0; i < team.stories.length; i++) {
            const story = team.stories[i];
            if (story.etat === 'a_valider' || story.etat === 'validee') continue;
            const doneTasks = team.tasks.filter(function (t) {
                return t.us_id === story.id && t.etat === 'termine' && !t.archived;
            });
            const handlerSet = new Set();
            doneTasks.forEach(function (t) {
                let handler = null;
                ['updated_by', 'assigne', 'created_by'].forEach(function (field) {
                    if (handler) return;
                    const l = t[field];
                    if (l && l !== 'systeme') {
                        const member = (team.members || []).find(function (m) { return m.login === l; });
                        if (member && member.role === 'DEVELOPER') handler = l;
                    }
                });
                if (handler) handlerSet.add(handler);
            });
            if (handlerSet.has(login)) return true;
        }
        return false;
    }

    function teamById(id) {
        if (!App.state) return null;
        if (App.state.team && Number(App.state.team.id) === Number(id)) return App.state.team;
        return (App.state.teams || []).find(function (t) { return Number(t.id) === Number(id); }) || null;
    }

    function memberByLogin(team, login) {
        return (team.members || []).find(function (m) { return m.login === login; }) || null;
    }

    function storyById(team, id) {
        return (team.stories || []).find(function (s) { return s.id === id; }) || null;
    }

    function taskById(team, id) {
        return (team.tasks || []).find(function (t) { return t.id === id; }) || null;
    }

    function activeTasks(team) {
        return (team.tasks || []).filter(function (t) { return !t.archived; });
    }

    function formatRemaining(seconds) {
        const s = Math.max(0, Math.floor(seconds));
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        if (h > 0) {
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
        }
        return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
    }

    function announcementNumber(id) {
        const match = String(id || '').match(/(\d+)$/);
        return match ? Number(match[1]) : 0;
    }

    // -----------------------------------------------------------------------
    // Messages et réseau
    // -----------------------------------------------------------------------
    function flash(message, type) {
        if (!message) return;
        if (type === 'error' && document.getElementById('ui-modal')) {
            showAlert(message, 'Action impossible', 'error');
            return;
        }
        if (!flashZone) return;
        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type || 'info');
        alert.setAttribute('role', 'status');
        alert.textContent = String(message);
        flashZone.innerHTML = '';
        flashZone.appendChild(alert);
        window.setTimeout(function () {
            if (alert.parentNode) alert.parentNode.removeChild(alert);
        }, type === 'error' ? 7000 : 4500);
    }

    function showNetwork(message) {
        if (!networkStatus) return;
        networkStatus.hidden = false;
        networkStatus.textContent = message;
        App.lastNetworkError = message;
    }

    function hideNetwork() {
        if (!networkStatus) return;
        networkStatus.hidden = true;
        networkStatus.textContent = '';
        App.lastNetworkError = '';
    }

    async function parseResponse(response) {
        let data = null;
        try {
            data = await response.json();
        } catch (err) {
            data = null;
        }
        return data;
    }

    async function fetchState() {
        if (App.polling) return;
        App.polling = true;
        App.syncStatus = 'syncing';
        updateSyncStatus();
        try {
            const url = 'api.php?action=state' + (App.v ? '&v=' + encodeURIComponent(App.v) : '');
            const response = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });

            if (response.status === 401) {
                window.location.href = 'workflow.php';
                return;
            }
            if (response.status === 429) {
                showNetwork('Le serveur est très sollicité (limitation de requêtes). Nouvelle tentative dans quelques secondes.');
                App.pollDelay = Math.max(App.pollDelay, 9000);
                App.syncStatus = 'error';
                return;
            }

            const data = await parseResponse(response);
            if (!response.ok || !data || !data.ok) {
                throw new Error((data && data.error) || 'Réponse serveur invalide.');
            }

            hideNetwork();
            App.syncStatus = 'ok';
            if (data.unchanged) {
                App.offset = num(data.server_time_ms) - Date.now();
                App.state.session = data.session;
                updateClock();
            } else {
                applyState(data);
            }

            // Reprise progressive après une erreur réseau.
            App.pollDelay = Math.max(2000, Math.round(App.pollDelay * 0.72));
        } catch (error) {
            showNetwork('Connexion au serveur interrompue. Ralentissement automatique puis nouvelle tentative.');
            App.pollDelay = Math.min(15000, Math.max(2000, Math.round(App.pollDelay * 1.6)));
            App.syncStatus = 'error';
        } finally {
            App.polling = false;
            schedulePoll();
            updateSyncStatus();
        }
    }

    function schedulePoll() {
        if (App.pollTimer) window.clearTimeout(App.pollTimer);
        const hidden = document.hidden;
        const base = hidden ? Math.max(10000, App.pollDelay) : App.pollDelay;
        const jitter = Math.floor(Math.random() * 500);
        App.syncStartedAt = Date.now();
        App.syncDuration = base + jitter;
        App.nextSyncAt = App.syncStartedAt + App.syncDuration;
        App.pollTimer = window.setTimeout(fetchState, App.syncDuration);
        updateSyncStatus();
    }

    function pollNow() {
        if (App.pollTimer) window.clearTimeout(App.pollTimer);
        window.setTimeout(fetchState, 80);
    }

    async function apiAction(action, payload, options) {
        if (App.actionBusy) return null;
        const opts = options || {};
        App.actionBusy = true;
        try {
            const response = await fetch('api.php?action=' + encodeURIComponent(action), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF,
                },
                cache: 'no-store',
                credentials: 'same-origin',
                body: JSON.stringify(payload || {}),
            });

            if (response.status === 401) {
                window.location.href = 'workflow.php';
                return null;
            }

            const data = await parseResponse(response);
            if (!response.ok || !data || !data.ok) {
                const message = (data && data.error) || 'Action refusée par le serveur.';
                if (typeof opts.onError === 'function') opts.onError(message);
                else flash(message, 'error');
                if (!opts.noPoll) pollNow();
                return null;
            }

            if (data.message) flash(data.message, 'success');
            App.v = null;
            if (!opts.noPoll) pollNow();
            return data;
        } catch (error) {
            const message = 'Erreur réseau : l’action n’a peut-être pas été enregistrée.';
            if (typeof opts.onError === 'function') opts.onError(message);
            else flash(message, 'error');
            if (!opts.noPoll) pollNow();
            return null;
        } finally {
            App.actionBusy = false;
        }
    }

    async function loadHistory() {
        if (!isTeacher || App.historyLoading) return;
        App.historyLoading = true;
        try {
            const response = await fetch('api.php?action=history', {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            if (response.status === 401) {
                window.location.href = 'workflow.php';
                return;
            }
            const data = await parseResponse(response);
            if (!response.ok || !data || !data.ok) {
                throw new Error((data && data.error) || 'Historique indisponible.');
            }
            App.history = data;
        } catch (error) {
            flash('Impossible de charger l’historique : ' + error.message, 'error');
        } finally {
            App.historyLoading = false;
            if (App.view === 'history') render();
        }
    }

    // -----------------------------------------------------------------------
    // Horloge
    // -----------------------------------------------------------------------
    function updateSyncStatus() {
        const el = document.getElementById('sync-status');
        const bar = document.getElementById('sync-progress');
        if (!el) return;
        if (App.syncing) {
            el.textContent = 'Synchronisation…';
            el.className = 'sync-status is-syncing';
            if (bar) bar.style.width = '100%';
            return;
        }
        if (App.syncStatus === 'error') {
            el.textContent = 'Synchronisation en échec';
            el.className = 'sync-status is-error';
            if (bar) bar.style.width = '0%';
            return;
        }
        const remaining = Math.max(0, App.nextSyncAt - Date.now());
        const elapsed = Math.max(0, App.syncDuration - remaining);
        const percent = Math.min(100, Math.max(0, (elapsed / Math.max(1, App.syncDuration)) * 100));
        if (remaining <= 0) {
            el.textContent = 'Synchronisation…';
            el.className = 'sync-status is-syncing';
            if (bar) bar.style.width = '100%';
        } else {
            el.textContent = 'Synchronisation dans ' + Math.ceil(remaining / 1000) + ' s';
            el.className = 'sync-status';
            if (bar) bar.style.width = percent.toFixed(1) + '%';
        }
    }

    function updateClock() {
        updateSyncStatus();
        updateChallengeCountdown();
        const session = App.state && App.state.session ? App.state.session : null;
        if (!session) return;

        const current = nowMs();
        const remainingSeconds = Math.max(0, (num(session.sim_end_ms) - current) / 1000);
        const total = Math.max(1, (num(session.sim_end_ms) - num(session.sim_start_ms)) / 1000);
        const progress = Math.min(100, Math.max(0, ((total - remainingSeconds) / total) * 100));

        const timeEl = document.getElementById('clock-time');
        const remainingEl = document.getElementById('clock-remaining');
        const progressEl = document.getElementById('clock-progress');
        const badge = document.getElementById('session-badge');

        if (timeEl) timeEl.textContent = clockFormatter.format(new Date(current));
        if (remainingEl) remainingEl.textContent = formatRemaining(remainingSeconds);
        if (progressEl) progressEl.style.width = progress.toFixed(1) + '%';

        if (badge) {
            let phase = session.phase;
            // La fin serveur est autoritaire mais l'horloge peut l'afficher
            // immédiatement entre deux synchronisations.
            if (phase !== 'finished' && current >= num(session.sim_end_ms)) {
                phase = 'finished';
            }
            badge.className = 'badge badge-phase ' + phaseClass(phase);
            badge.textContent = phase === 'finished' && session.phase !== 'finished'
                ? 'Simulation terminée'
                : (session.phase_label || '—');
        }

        document.querySelectorAll('[data-expiry]').forEach(function (el) {
            const expires = Number(el.getAttribute('data-expiry')) * 1000;
            const left = Math.max(0, (expires - current) / 1000);
            if (left <= 0) {
                el.textContent = 'Proposition expirée (actualisation…)';
            } else {
                el.textContent = 'Expire dans ' + formatRemaining(left);
            }
        });
    }

    function startClock() {
        updateClock();
        if (App.clockTimer) window.clearInterval(App.clockTimer);
        App.clockTimer = window.setInterval(updateClock, 500);
    }

    // -----------------------------------------------------------------------
    // Conservation de l'état des formulaires et du défilement
    // -----------------------------------------------------------------------
    function captureViewState() {
        const state = { formId: null, values: {}, focusId: null, selection: null, details: {}, chatScroll: null };
        const active = document.activeElement;
        if (active && active.id) state.focusId = active.id;

        const form = active && active.closest ? active.closest('#view-root form') : null;
        if (form && form.id) {
            state.formId = form.id;
            form.querySelectorAll('input, textarea, select').forEach(function (el) {
                if (!el.name) return;
                if (el.type === 'checkbox') {
                    state.values[el.name + '__checked__' + el.value] = el.checked;
                } else {
                    state.values[el.name] = el.value;
                }
                if (el === active && typeof el.selectionStart === 'number') {
                    state.selection = [el.selectionStart, el.selectionEnd];
                }
            });
        }

        document.querySelectorAll('#view-root details[id]').forEach(function (details) {
            state.details[details.id] = details.open;
        });

        const chat = document.querySelector('#view-root .chat-box');
        if (chat) {
            state.chatScroll = {
                top: chat.scrollTop,
                atBottom: chat.scrollTop + chat.clientHeight >= chat.scrollHeight - 8,
            };
        }
        return state;
    }

    function restoreViewState(state) {
        if (!state) return;
        Object.keys(state.details || {}).forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.open = !!state.details[id];
        });

        if (state.formId) {
            const form = document.getElementById(state.formId);
            if (form) {
                form.querySelectorAll('input, textarea, select').forEach(function (el) {
                    if (!el.name) return;
                    if (el.type === 'checkbox') {
                        const key = el.name + '__checked__' + el.value;
                        if (key in state.values) el.checked = !!state.values[key];
                    } else if (el.name in state.values) {
                        el.value = state.values[el.name];
                    }
                });
            }
        }

        if (state.focusId) {
            const focus = document.getElementById(state.focusId);
            if (focus && typeof focus.focus === 'function') {
                focus.focus({ preventScroll: true });
                if (state.selection && typeof focus.setSelectionRange === 'function') {
                    try {
                        focus.setSelectionRange(state.selection[0], state.selection[1]);
                    } catch (err) {
                        // champ non compatible (select, checkbox…)
                    }
                }
            }
        }

        const chat = document.querySelector('#view-root .chat-box');
        if (chat && state.chatScroll) {
            if (state.chatScroll.atBottom) {
                chat.scrollTop = chat.scrollHeight;
            } else {
                chat.scrollTop = state.chatScroll.top;
            }
        }
    }

    // -----------------------------------------------------------------------
    // Application de l'état et rendu
    // -----------------------------------------------------------------------
    function applyState(data) {
        App.offset = num(data.server_time_ms) - Date.now();
        App.state = data;
        App.v = data.v;
        if (!isTeacher && data.history) {
            App.history = data.history;
        }
        document.body.classList.toggle('is-teacher', !!data.me.is_teacher);
        updateIdentity();

        const generation = (data.session && data.session.generation) || '';
        const generationChanged = App.generation !== null && generation !== App.generation;
        App.generation = generation;
        if (generationChanged) {
            // Une remise à zéro réinitialise les compteurs du serveur : les
            // repères locaux (annonces lues, historique) doivent aussi l'être.
            App.history = null;
            App.announcementKey = '';
            if (App.activeChallenge) closeChallengeModal();
        }

        if (!App.announcementKey && data.me) {
            App.announcementKey = 'wf_seen_announcements_' + data.me.login + '_' + generation;
            try {
                App.seenAnnouncements = Number(window.localStorage.getItem(App.announcementKey) || '0') || 0;
            } catch (err) {
                App.seenAnnouncements = 0;
            }
        }

        if (generationChanged && isTeacher && App.view === 'history') {
            loadHistory();
        }
        if (App.view === 'chat') markAnnouncementsSeen();
        updateClock();
        updateNavBadges();
        // La modale de validation technique ne s'ouvre jamais depuis l'état
        // synchronisé : seul un clic explicite sur « Prête à valider » le fait.
        render();
    }

    function updateIdentity() {
        if (!App.state || !App.state.me || App.state.me.is_teacher) return;
        const me = App.state.me;
        const initialLabel = me.initial_role_label || roleLabel(me.initial_role || me.role);
        const currentLabel = me.role_label || roleLabel(me.role);
        const initialEl = document.getElementById('user-role-initial');
        const currentEl = document.getElementById('user-role-current');

        if (initialEl) initialEl.textContent = initialLabel;
        if (currentEl) {
            currentEl.textContent = currentLabel;
            currentEl.className = 'badge badge-role-' + roleBadge(me.role);
        }
    }

    function updateNavBadges() {
        if (!App.state) return;
        const eventsBadge = document.getElementById('events-badge');
        const chatBadge = document.getElementById('chat-badge');

        if (eventsBadge) {
            const team = myTeam();
            const active = team && team.events && team.events.active;
            eventsBadge.hidden = !active;
            eventsBadge.textContent = '1';
        }

        if (chatBadge) {
            const unseen = Math.min(9, unseenChatCount());
            const overflow = unseenChatCount() > 9;
            chatBadge.hidden = unseen <= 0;
            chatBadge.textContent = unseen > 0 ? (overflow ? '9+' : String(unseen)) : '';
        }

        const swapsBadge = document.getElementById('swaps-badge');
        if (swapsBadge) {
            const me = App.state.me.login;
            const incoming = (myTeam() && myTeam().swaps ? myTeam().swaps : []).filter(function (swap) {
                return swap.status === 'pending' && swap.to === me;
            }).length;
            swapsBadge.hidden = incoming <= 0;
            swapsBadge.textContent = String(incoming);
        }

        const passwordRequestsBadge = document.getElementById('password-requests-badge');
        if (passwordRequestsBadge) {
            const count = isTeacher ? (App.state.password_requests || []).length : 0;
            passwordRequestsBadge.hidden = count <= 0;
            passwordRequestsBadge.textContent = String(count);
        }

        const backlogBadge = document.getElementById('backlog-badge');
        if (backlogBadge) {
            const team = myTeam();
            const tasks = team ? activeTasks(team) : [];
            const completedStories = team ? (team.stories || []).filter(function (story) {
                if (!story.selectionnee || story.etat === 'a_valider' || story.etat === 'validee') return false;
                const storyTasks = tasks.filter(function (task) { return task.us_id === story.id; });
                return storyTasks.length > 0 && storyTasks.every(function (task) { return task.etat === 'termine'; });
            }).length : 0;
            backlogBadge.hidden = completedStories <= 0;
            backlogBadge.textContent = String(completedStories);
        }

        const kanbanBadge = document.getElementById('kanban-badge');
        if (kanbanBadge) {
            const team = myTeam();
            const availableStories = team ? (team.stories || []).filter(function (story) {
                if (!story.selectionnee || story.etat === 'validee') return false;
                return !(team.tasks || []).some(function (task) {
                    return task.us_id === story.id;
                });
            }).length : 0;
            kanbanBadge.hidden = availableStories <= 0;
            kanbanBadge.textContent = String(availableStories);
        }
    }

    function markAnnouncementsSeen() {
        if (!App.state || !App.announcementKey) return;
        const max = (App.state.announcements || []).reduce(function (value, a) {
            return Math.max(value, announcementNumber(a.id));
        }, 0);
        if (max > App.seenAnnouncements) {
            App.seenAnnouncements = max;
            try {
                window.localStorage.setItem(App.announcementKey, String(max));
            } catch (err) {
                // stockage local indisponible : le badge reste fonctionnel en mémoire
            }
            updateNavBadges();
        }
    }

    function chatSeenKey() {
        const gen = (App.state && App.state.session && App.state.session.generation)
            ? App.state.session.generation : 'default';
        return App.state && App.state.me ? 'chat_seen_' + App.state.me.login + '_' + gen : null;
    }

    function chatSeenSet() {
        const key = chatSeenKey();
        if (!key) return new Set();
        try {
            const raw = window.localStorage.getItem(key);
            return new Set(raw ? JSON.parse(raw) : []);
        } catch (err) {
            return new Set();
        }
    }

    function saveChatSeenSet(set) {
        const key = chatSeenKey();
        if (!key) return;
        try {
            window.localStorage.setItem(key, JSON.stringify(Array.from(set)));
        } catch (err) {
            // stockage local indisponible : le badge reste fonctionnel en mémoire
        }
    }

    function visibleChatItems() {
        if (!App.state) return [];
        const team = myTeam();
        if (!team) return [];
        const teamId = team.id;
        const items = [];
        (team.messages || []).forEach(function (message) {
            if (message.author !== App.state.me.login) {
                items.push('M:' + message.id);
            }
        });
        (App.state.announcements || []).forEach(function (announcement) {
            if (announcement.deleted) return;
            const target = Number(announcement.target || 0);
            if (target !== 0 && target !== Number(teamId)) return;
            items.push('A:' + announcement.id);
        });
        return items;
    }

    function unseenChatCount() {
        const seen = chatSeenSet();
        return visibleChatItems().filter(function (id) {
            return !seen.has(id);
        }).length;
    }

    function markChatSeen() {
        if (!App.state) return;
        const seen = chatSeenSet();
        visibleChatItems().forEach(function (id) {
            seen.add(id);
        });
        saveChatSeenSet(seen);
        updateNavBadges();
    }

    function setActiveNav() {
        document.querySelectorAll('.nav-item[data-view]').forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.view === App.view);
        });
    }

    function switchView(view) {
        const allowed = isTeacher
            ? ['global', 'team-1', 'team-2', 'team-3', 'announcements', 'moderation', 'history', 'bilan']
            : ['dashboard', 'review', 'backlog', 'kanban', 'events', 'chat', 'roles', 'history', 'bilan'];
        if (!allowed.includes(view)) return;
        App.view = view;
        setActiveNav();
        if (view === 'chat') {
            markAnnouncementsSeen();
            markChatSeen();
        }
        if (view === 'history' && isTeacher && !App.history && !App.historyLoading) loadHistory();
        if (view === 'history' && !isTeacher && App.state && App.state.history) App.history = App.state.history;
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function render() {
        if (!viewRoot) return;
        if (!App.state) {
            viewRoot.innerHTML = '<div class="loading-card"><span class="spinner" aria-hidden="true"></span>Chargement…</div>';
            return;
        }
        const previous = captureViewState();
        const team = myTeam();
        let html = '';

        if (isTeacher) {
            if (App.view === 'global') html = renderTeacherGlobal();
            else if (App.view.startsWith('team-')) html = renderTeacherTeam(Number(App.view.split('-')[1]));
            else if (App.view === 'announcements') html = renderTeacherAnnouncements();
            else if (App.view === 'moderation') html = renderTeacherModeration();
            else if (App.view === 'history') html = renderTeacherHistory();
            else if (App.view === 'bilan') html = renderTeacherBilan();
            else html = renderTeacherGlobal();
        } else {
            if (!team) {
                html = '<div class="alert alert-error">Données d’équipe indisponibles.</div>';
            } else if (App.view === 'dashboard') html = renderDashboard(team);
            else if (App.view === 'review') html = renderReviewView(team);
            else if (App.view === 'backlog') html = renderBacklogView(team);
            else if (App.view === 'kanban') html = renderKanbanView(team);
            else if (App.view === 'events') html = renderEventsView(team);
            else if (App.view === 'chat') html = renderChatView(team);
            else if (App.view === 'roles') html = renderRolesView(team);
            else if (App.view === 'history') html = renderStudentHistory();
            else if (App.view === 'bilan') html = renderStudentBilan();
            else html = renderDashboard(team);
        }

        viewRoot.innerHTML = html;
        setActiveNav();
        restoreViewState(previous);

        // La modale reste ouverte pendant les synchronisations : son contenu
        // est recalculé uniquement lorsque l'état de l'équipe a changé.
        const statsModal = document.getElementById('kanban-stats-modal');
        if (statsModal && !statsModal.hidden) {
            const statsTeam = teamById(statsModal.dataset.team || '');
            const statsBody = document.getElementById('kanban-stats-body');
            if (statsTeam && statsBody) statsBody.innerHTML = renderKanbanStats(statsTeam);
        }
    }

    // -----------------------------------------------------------------------
    // Fragments communs
    // -----------------------------------------------------------------------
    function pageHeading(title, subtitle, actions) {
        return '<div class="page-heading"><div><h1>' + esc(title) + '</h1>'
            + (subtitle ? '<p>' + esc(subtitle) + '</p>' : '')
            + '</div><div class="heading-actions">' + (actions || '') + '</div></div>';
    }

    function toolHelpButton(tool) {
        return '<button type="button" class="btn btn-help btn-small" data-action="tool-help" data-tool="' + esc(tool)
            + '" aria-haspopup="dialog" title="Aide sur cet outil">Aide de l’outil</button>';
    }

    function kpi(label, value, note, modifier) {
        return '<div class="kpi ' + esc(modifier || '') + '">'
            + '<div class="kpi-label">' + esc(label) + '</div>'
            + '<div class="kpi-value">' + esc(value) + '</div>'
            + (note ? '<div class="kpi-note">' + esc(note) + '</div>' : '')
            + '</div>';
    }

    function statBadge(state) {
        return '<span class="badge badge-state-' + esc(state) + '">' + esc(stateLabel(state)) + '</span>';
    }

    function priorityBadge(priority) {
        return '<span class="badge badge-' + esc(priority === 'haute' ? 'high' : priority === 'moyenne' ? 'medium' : 'low') + '">'
            + esc(priorityLabel(priority)) + '</span>';
    }

    function roleBadgeHtml(role) {
        return '<span class="badge badge-role-' + roleBadge(role) + '">' + esc(roleLabel(role)) + '</span>';
    }

    function moderationMetaBadge(meta, text) {
        if (!meta || typeof meta !== 'object') return '';
        const action = meta.action === 'delete' ? 'Supprimé par le formateur' : 'Modifié par le formateur';
        const by = meta.by ? ' (' + meta.by + ')' : '';
        const at = meta.at ? ' le ' + fmtDateTime(meta.at) : '';
        return '<span class="badge badge-moderation" title="Intervention du formateur">'
            + esc((text || action) + by + at) + '</span>';
    }

    function taskModerationBadge(task) {
        return task && task.moderation ? moderationMetaBadge(task.moderation.titre) : '';
    }

    function storyModerationBadge(story) {
        return story && story.moderation ? moderationMetaBadge(story.moderation.refus_commentaire) : '';
    }

    function goalModerationBadge(team) {
        return team && team.sprint_goal_moderation ? moderationMetaBadge(team.sprint_goal_moderation.sprint_goal) : '';
    }

    function messageModerationBadge(message) {
        if (!message || !message.edited_by) return '';
        return moderationMetaBadge({ action: 'edit', by: message.edited_by, at: message.edited_at }, 'Modifié par le formateur');
    }

    function membersHtml(team) {
        return '<div class="member-list">' + (team.members || []).map(function (member) {
            return '<span class="member-chip"><strong>' + esc(member.login) + '</strong>' + roleBadgeHtml(member.role) + '</span>';
        }).join('') + '</div>';
    }

    function capacityMetrics(team) {
        const c = team.capacity || {};
        return '<dl class="metric-list">'
            + metricRow('Capacité initiale', hours(c.initial))
            + metricRow('Réduction événements', hours(c.reduction))
            + metricRow('Capacité disponible', hours(c.available))
            + metricRow('Heures réalisées', hours(c.done_hours))
            + metricRow('Capacité restante', hours(c.remaining_capacity))
            + metricRow('Charge des tâches', hours(c.charge))
            + metricRow('Reste à faire', hours(c.remaining_work))
            + '</dl>';
    }

    function metricRow(label, value) {
        return '<div class="metric-row"><dt>' + esc(label) + '</dt><dd>' + esc(value) + '</dd></div>';
    }

    function canManageTasks(team) {
        return userCanWrite() && myRole() === 'DEVELOPER';
    }

    function canManageBacklog(team) {
        return userCanWrite() && myRole() === 'PO';
    }

    function activeEventHtml(team, readonly) {
        const active = team.events && team.events.active;
        if (!active) {
            return '<div class="empty-state"><strong>Aucun événement en cours</strong>Le serveur déclenchera automatiquement les événements prévus pour votre équipe.</div>';
        }
        const disabled = readonly || !userCanWrite();
        return '<div class="card event-banner">'
            + '<div class="card-header"><div><h2>' + esc(active.titre) + '</h2>'
            + '<p class="card-subtitle">Déclenché à ' + esc(fmtTime(active.triggered_at)) + ' · décision collective de l’équipe</p></div>'
            + '<span class="badge badge-high">Événement actif</span></div>'
            + '<p>' + esc(active.description) + '</p>'
            + '<div class="event-options">'
            + (active.options || []).map(function (option, index) {
                return '<button type="button" class="event-option" data-action="respond-event" data-event="' + esc(active.id) + '" data-option="' + index + '"'
                    + (disabled ? ' disabled' : '') + '>'
                    + '<strong>' + esc(option.label) + '</strong>'
                    + '<span>' + esc(option.resultat) + '</span></button>';
            }).join('')
            + '</div>'
            + (!readonly && !userCanWrite() ? '<p class="small muted" style="margin-top:10px">Les décisions sont bloquées pendant la pause ou après la fin.</p>' : '')
            + '</div>';
    }

    function announcementsHtml(list, limit, moderate) {
        const items = (list || []).slice().sort(function (a, b) {
            return announcementNumber(b.id) - announcementNumber(a.id);
        });
        const shown = limit ? items.slice(0, limit) : items;
        if (!shown.length) {
            return '<div class="empty-state"><strong>Aucune annonce</strong>Les annonces du formateur apparaîtront ici.</div>';
        }
        return shown.map(function (a) {
            const target = Number(a.target) === 0 ? 'Toutes les équipes' : 'Équipe ' + Number(a.target);
            const badge = a.edited_by
                ? moderationMetaBadge({ action: 'edit', by: a.edited_by, at: a.edited_at }, 'Modifiée par le formateur')
                : '';
            const actions = moderate
                ? moderationButtons('announcement', 0, a.id, 'text', a.text, null, 'annonce ' + a.id)
                : '';
            return '<div class="announcement-item">'
                + '<div class="announcement-meta"><span>' + esc(fmtDateTime(a.at)) + ' · ' + esc(target) + '</span><span>' + esc(a.author) + '</span></div>'
                + '<div class="announcement-text">' + esc(a.text) + '</div>'
                + '<div class="announcement-footer">' + badge + actions + '</div>'
                + '</div>';
        }).join('');
    }

    function swapsHtml(team, readonly) {
        const swaps = (team.swaps || []).filter(function (s) { return s.status === 'pending'; });
        if (!swaps.length) {
            return '<div class="empty-state"><strong>Aucun échange en attente</strong>Les propositions actives apparaîtront ici.</div>';
        }
        const me = App.state.me.login;
        return swaps.map(function (swap) {
            const incoming = swap.to === me;
            const outgoing = swap.from === me;
            let actions = '';
            if (!readonly && incoming) {
                actions = '<div class="swap-actions">'
                    + '<button type="button" class="btn btn-success btn-small" data-action="swap-respond" data-proposal="' + esc(swap.id) + '" data-accept="1">Accepter</button>'
                    + '<button type="button" class="btn btn-danger btn-small" data-action="swap-respond" data-proposal="' + esc(swap.id) + '" data-accept="0">Refuser</button>'
                    + '</div>';
            } else if (!readonly && outgoing) {
                actions = '<div class="swap-actions"><button type="button" class="btn btn-ghost btn-small" data-action="swap-cancel" data-proposal="' + esc(swap.id) + '">Annuler</button></div>';
            }
            return '<div class="swap-item ' + (incoming ? 'is-incoming' : outgoing ? 'is-outgoing' : '') + '">'
                + '<strong>' + esc(swap.from) + '</strong> (' + esc(roleLabel(swap.from_role)) + ') ? <strong>' + esc(swap.to) + '</strong> (' + esc(roleLabel(swap.to_role)) + ')'
                + '<div class="swap-meta"><span>' + (incoming ? 'Reçue' : outgoing ? 'Envoyée' : 'En attente') + '</span>'
                + '<span data-expiry="' + esc(swap.expires_at) + '">Expire dans ' + esc(formatRemaining(swap.expires_at - (Date.now() + App.offset) / 1000)) + '</span></div>'
                + actions
                + '</div>';
        }).join('');
    }

    // -----------------------------------------------------------------------
    // Interface étudiant — tableau de bord
    // -----------------------------------------------------------------------
    function renderDashboard(team) {
        const stats = team.stats || {};
        const active = team.events ? team.events.active : null;
        const pendingSwaps = (team.swaps || []).filter(function (s) { return s.status === 'pending'; });
        const selected = team.stories.filter(function (s) { return s.selectionnee; });
        const roleHint = myRole() === 'PO'
            ? 'Vous priorisez le backlog et validez les User Stories terminées.'
            : myRole() === 'SCRUM_MASTER'
                ? 'Vous facilitez le travail, suivez les blocages et rendez les difficultés visibles.'
                : 'Vous estimez, décomposez en tâches et faites progresser le Kanban.';

        let html = pageHeading(
            'Tableau de bord — Équipe ' + team.id,
            'Sprint Goal, capacité, rôles et signaux utiles pour piloter la simulation.',
            toolHelpButton('dashboard')
            + '<button type="button" class="btn btn-ghost btn-small" data-view="review">Review & rétro</button>'
        );

        html += '<div class="kpi-grid">'
            + kpi('Capacité restante', hours(team.capacity.remaining_capacity), hours(team.capacity.available) + ' disponibles', 'kpi-primary')
            + kpi('Charge des tâches', hours(team.capacity.charge), hours(team.capacity.remaining_work) + ' restantes', '')
            + kpi('Valeur métier validée', String(stats.valeur_validee || 0), (stats.us_validees || 0) + ' US validée(s)', 'kpi-success')
            + kpi('Story Points validés', String(stats.points_valides || 0), (stats.points_selectionnes || 0) + ' SP sélectionnés', '')
            + '</div>';

        html += '<div class="split-main" style="margin-top:16px">';

        html += '<div class="section-stack">';
        html += '<section class="card card-accent">'
            + '<div class="card-header"><div><h2>Sprint Goal</h2><p class="card-subtitle">Objectif collectif de l’équipe</p></div></div>'
            + '<p class="goal-text">' + esc(team.sprint_goal) + goalModerationBadge(team) + '</p>';
        if (userCanWrite()) {
            html += '<details id="goal-details" class="inline-details">'
                + '<summary>Modifier le Sprint Goal (décision collective)</summary>'
                + '<form id="goal-form" data-form="goal" class="form-grid goal-edit-form">'
                + '<div class="field field-full"><label for="goal-text">Nouveau Sprint Goal</label>'
                + '<textarea id="goal-text" name="goal" rows="3" minlength="10" maxlength="300" required>' + esc(team.sprint_goal) + '</textarea></div>'
                + '<div class="field field-full"><label for="goal-justification">Justification de la décision</label>'
                + '<textarea id="goal-justification" name="justification" rows="2" minlength="10" maxlength="500" required></textarea></div>'
                + '<div class="field field-full"><label>Membres ayant participé</label><div class="check-list">'
                + team.members.map(function (m) {
                    return '<label><input type="checkbox" name="participants" value="' + esc(m.login) + '" checked> ' + esc(m.login) + ' (' + esc(roleLabel(m.role)) + ')</label>';
                }).join('')
                + '</div></div>'
                + '<div class="modal-actions field-full"><button type="submit" class="btn btn-primary">Enregistrer la décision</button></div>'
                + '</form></details>';
        }
        html += '</section>';

        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Événement en cours</h2><p class="card-subtitle">Le client est représenté par des événements automatiques.</p></div>'
            + (active ? '<span class="badge badge-high">1 actif</span>' : '') + '</div>'
            + activeEventHtml(team, false)
            + '</section>';

        html += '<section class="card">'
            + '<div class="card-header"><div><h2>User Stories sélectionnées</h2><p class="card-subtitle">' + selected.length + ' US · ' + (stats.points_selectionnes || 0) + ' Story Points</p></div>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="backlog">Voir le backlog</button></div>';
        if (selected.length === 0) {
            html += '<div class="empty-state"><strong>Aucune User Story sélectionnée</strong>L’équipe choisit librement les éléments à traiter dans le Sprint.</div>';
        } else {
            html += '<ul class="selected-story-list">' + selected.map(function (story) {
                return '<li><span>' + esc(story.id) + ' — ' + esc(story.titre) + '</span><span>'
                    + statBadge(story.etat) + ' <span class="badge badge-count">' + esc(story.estimation) + ' SP</span> '
                    + '<span class="badge badge-count">' + esc(story.valeur) + ' valeur</span></span></li>';
            }).join('') + '</ul>';
        }
        html += '</section>';
        html += '</div>';

        html += '<div class="section-stack">';
        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Capacité et charge</h2><p class="card-subtitle">Les heures sont des unités de simulation.</p></div></div>'
            + capacityMetrics(team) + '</section>';

        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Rôles de l’équipe</h2><p class="card-subtitle">' + esc(roleHint) + '</p></div>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="roles">Gérer</button></div>'
            + membersHtml(team) + '</section>';

        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Échanges en attente</h2><p class="card-subtitle">' + pendingSwaps.length + ' proposition(s)</p></div></div>'
            + (pendingSwaps.length ? swapsHtml(team, false) : '<div class="empty-state"><strong>Aucune proposition</strong>Les échanges de rôles se font avec l’accord des deux membres.</div>')
            + '</section>';

        const mine = (App.state.announcements || []).slice(0, 3);
        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Annonces</h2><p class="card-subtitle">Messages du formateur</p></div>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="chat">Voir le chat</button></div>'
            + announcementsHtml(mine, 3) + '</section>';
        html += '</div></div>';

        return html;
    }

    // -----------------------------------------------------------------------
    // Sprint Review et rétrospective
    // -----------------------------------------------------------------------
    function reviewSummaryHtml(team) {
        const selected = (team.stories || []).filter(function (s) { return s.selectionnee; });
        const validated = selected.filter(function (s) { return s.etat === 'validee'; });
        const ready = selected.filter(function (s) { return s.etat === 'a_valider'; });
        const refused = selected.filter(function (s) { return s.etat === 'refusee'; });
        const remaining = selected.filter(function (s) { return s.etat !== 'validee' && s.etat !== 'refusee'; });

        function storyList(items, emptyText, withComment) {
            if (!items.length) {
                return '<div class="empty-state"><strong>' + esc(emptyText) + '</strong></div>';
            }
            return '<ul class="selected-story-list">' + items.map(function (story) {
                return '<li><span><strong>' + esc(story.id) + '</strong> — ' + esc(story.titre) + '</span><span>'
                    + statBadge(story.etat) + ' <span class="badge badge-count">' + esc(story.estimation) + ' SP</span> '
                    + '<span class="badge badge-count">' + esc(story.valeur) + ' valeur</span></span>'
                    + (withComment && story.refus_commentaire
                        ? '<span class="small muted" style="flex-basis:100%">Motif du refus : ' + esc(story.refus_commentaire) + '</span>'
                        : '')
                    + '</li>';
            }).join('') + '</ul>';
        }

        let html = '<div class="kpi-grid">'
            + kpi('US validées', String(validated.length), (team.stats ? team.stats.points_valides : 0) + ' SP validés', 'kpi-success')
            + kpi('Prêtes à valider', String(ready.length), 'À vérifier avec la DoD', '')
            + kpi('US refusées', String(refused.length), 'À corriger ou à expliciter', '')
            + kpi('US sélectionnées', String(selected.length), remaining.length + ' encore en travail', 'kpi-primary')
            + '</div>';

        html += '<div class="grid-2" style="margin-top:16px">'
            + '<section class="card"><h2>Incrément simulé</h2>'
            + '<p class="card-subtitle">User Stories validées par le PO, avec leur valeur métier.</p>'
            + storyList(validated, 'Aucune User Story validée pour le moment.', false)
            + '</section>'
            + '<section class="card"><h2>Éléments à examiner</h2>'
            + '<p class="card-subtitle">Prêtes à valider, refusées ou encore en travail.</p>'
            + storyList(ready, 'Aucune User Story en attente de validation.', false)
            + storyList(refused, 'Aucune User Story refusée.', true)
            + storyList(remaining, 'Aucune User Story sélectionnée restante.', false)
            + '</section></div>';

        html += '<section class="card" style="margin-top:16px"><h2>Definition of Done</h2>'
            + '<p class="card-subtitle">À vérifier avant de déclarer une User Story prête.</p><ul>'
            + (App.state.definition_of_done || []).map(function (item) { return '<li>' + esc(item) + '</li>'; }).join('')
            + '</ul></section>';

        return html;
    }

    function retrospectiveBodyHtml(team, readonly) {
        const history = (team.retrospective_history || []).slice().reverse();
        let html = '<p class="goal-text" style="white-space:pre-line">'
            + (team.retrospective ? esc(team.retrospective) : 'Aucune rétrospective enregistrée pour le moment.')
            + '</p>';
        if (team.retrospective_updated_by) {
            html += '<p class="small muted">Dernière mise à jour : ' + esc(team.retrospective_updated_by)
                + (team.retrospective_updated_at ? ' · ' + esc(fmtDateTime(team.retrospective_updated_at)) : '')
                + '</p>';
        }
        if (!readonly && userCanWrite()) {
            html += '<details class="inline-details"><summary>Contribuer à la rétrospective</summary>'
                + '<form id="retrospective-form" data-form="retrospective" class="form-grid">'
                + '<div class="field field-full"><label for="retrospective-text">Points forts, difficultés et améliorations</label>'
                + '<textarea id="retrospective-text" name="text" rows="4" minlength="10" maxlength="1000" required>'
                + esc(team.retrospective) + '</textarea>'
                + '<p class="hint">10 à 1000 caractères. Le texte est partagé avec l’équipe et conservé dans l’historique.</p></div>'
                + '<div class="modal-actions field-full"><button type="submit" class="btn btn-primary">Enregistrer la rétrospective</button></div>'
                + '</form></details>';
        }
        if (history.length) {
            html += '<details class="inline-details"><summary>Historique des contributions (' + history.length + ')</summary><ul class="small">'
                + history.map(function (entry) {
                    return '<li><strong>' + esc(entry.author) + '</strong> · ' + esc(fmtDateTime(entry.at))
                        + '<br><span class="muted">' + esc(entry.text) + '</span></li>';
                }).join('') + '</ul></details>';
        }
        return html;
    }

    function renderReviewView(team) {
        let html = pageHeading(
            'Sprint Review et rétrospective — Équipe ' + team.id,
            'Examiner l’incrément simulé, vérifier la Definition of Done et préparer l’amélioration continue.',
            toolHelpButton('review')
            + '<button type="button" class="btn btn-ghost btn-small" data-view="backlog">Voir le backlog</button>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="kanban">Voir le Kanban</button>'
        );
        html += '<section class="card"><div class="card-header"><div><h2>Sprint Review</h2>'
            + '<p class="card-subtitle">La Review se prépare pendant la simulation, à partir des User Stories et de la DoD.</p></div></div>'
            + reviewSummaryHtml(team) + '</section>';
        html += '<section class="card" style="margin-top:16px"><div class="card-header"><div><h2>Rétrospective collective</h2>'
            + '<p class="card-subtitle">Un espace partagé pour décider ce qui a fonctionné, ce qui a bloqué et ce que l’équipe améliore.</p></div></div>'
            + retrospectiveBodyHtml(team, false) + '</section>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Backlog
    // -----------------------------------------------------------------------
    function renderBacklogView(team) {
        const role = myRole();
        const canPriority = canManageBacklog(team);
        const canEstimate = userCanWrite() && role === 'DEVELOPER';
        const canSelect = userCanWrite();
        let html = pageHeading(
            'Product Backlog — Équipe ' + team.id,
            'Les Story Points mesurent la complexité ; la valeur métier est une notion distincte.',
            toolHelpButton('backlog')
            + '<button type="button" class="btn btn-ghost btn-small" data-view="kanban">Ouvrir le Kanban</button>'
        );
        html += '<div class="card">' + backlogTable(team, {
            canPriority: canPriority,
            canEstimate: canEstimate,
            canSelect: canSelect,
            readonly: false,
        }) + '</div>';
        return html;
    }

    function backlogTable(team, options) {
        const opts = options || {};
        const role = myRole();
        const tasksByStory = {};
        activeTasks(team).forEach(function (task) {
            if (!tasksByStory[task.us_id]) tasksByStory[task.us_id] = [];
            tasksByStory[task.us_id].push(task);
        });

        let html = '<div class="table-wrap"><table><thead><tr>'
            + '<th>Priorité / ordre</th><th>User Story</th><th class="number" title="Estimation en Story Points (suite de Fibonacci : 1, 2, 3, 5, 8, 13). Les Developers estiment la complexité relative de la User Story. Les Story Points ne se convertissent pas en heures.">Estimation</th><th class="number" title="Valeur métier de la User Story, fixée par le Product Owner. Elle mesure l’importance pour le client et sert à prioriser le backlog et à calculer le bilan.">Valeur</th>'
            + '<th>Sprint</th><th>État</th><th>Actions</th></tr></thead><tbody>';

        team.stories.forEach(function (story, index) {
            const tasks = tasksByStory[story.id] || [];
            const tasksComplete = tasks.length > 0 && tasks.every(function (task) { return task.etat === 'termine'; });
            const canValidate = !opts.readonly && userCanWrite() && role === 'PO' && story.etat === 'a_valider';
            const canReview = !opts.readonly && userCanWrite() && role === 'PO';
            const canDeclare = !opts.readonly && userCanWrite() && role === 'DEVELOPER'
                && story.selectionnee && story.etat !== 'a_valider' && story.etat !== 'validee'
                && tasksComplete;
            const canToggle = !opts.readonly && opts.canSelect && !['validee', 'a_valider'].includes(story.etat);
            const feedbackConsulted = !!story.client_feedback_consulted;
            const feedbackPosition = story.client_feedback_position;
            const validateDisabled = feedbackConsulted && feedbackPosition && feedbackPosition !== 'positive';
            const refuseDisabled = feedbackConsulted && feedbackPosition && feedbackPosition !== 'negative';

            let priorityCell = priorityBadge(story.priorite);
            if (opts.canPriority) {
                priorityCell += '<div style="margin-top:5px"><select class="priority-select" data-change="story-priority" data-story="' + esc(story.id) + '" aria-label="Priorité de ' + esc(story.id) + '">'
                    + ['haute', 'moyenne', 'basse'].map(function (p) {
                        return '<option value="' + p + '"' + (story.priorite === p ? ' selected' : '') + '>' + priorityLabel(p) + '</option>';
                    }).join('') + '</select></div>';
            }
            if (opts.canPriority) {
                priorityCell += '<div class="order-buttons">'
                    + '<button type="button" class="btn btn-ghost btn-icon" data-action="story-move" data-story="' + esc(story.id) + '" data-dir="up" title="Monter"' + (index === 0 ? ' disabled' : '') + '>↑</button>'
                    + '<button type="button" class="btn btn-ghost btn-icon" data-action="story-move" data-story="' + esc(story.id) + '" data-dir="down" title="Descendre"' + (index === team.stories.length - 1 ? ' disabled' : '') + '>↓</button>'
                    + '</div>';
            }

            let estimationCell = '<span class="badge badge-count">' + esc(story.estimation) + ' SP</span>';
            if (opts.canEstimate) {
                estimationCell = '<select class="estimate-select" data-change="story-estimate" data-story="' + esc(story.id) + '" aria-label="Estimation de ' + esc(story.id) + '">'
                    + [1, 2, 3, 5, 8, 13].map(function (point) {
                        return '<option value="' + point + '"' + (Number(story.estimation) === point ? ' selected' : '') + '>' + point + '</option>';
                    }).join('') + '</select>';
            }

            const tasksDetails = tasks.length
                ? '<details class="story-task-details"><summary>' + tasks.length + ' tâche(s) associée(s)</summary><ul>'
                    + tasks.map(function (task) {
                        return '<li>' + esc(task.id) + ' — ' + esc(task.titre) + ' · ' + hours(task.heures) + ' · ' + statBadge(task.etat) + '</li>';
                    }).join('') + '</ul></details>'
                : '<p class="tiny muted" style="margin:5px 0 0">Aucune tâche associée.</p>';

            html += '<tr>'
                + '<td>' + priorityCell + '</td>'
                + '<td class="backlog-story"><div class="story-title">' + esc(story.id) + ' — ' + esc(story.titre) + storyModerationBadge(story) + '</div>'
                + '<details class="story-task-details"><summary>Description et critères d’acceptation</summary>'
                + '<p class="story-description">' + esc(story.description) + moderationMetaBadge(story.moderation && story.moderation.description) + '</p>'
                + '<ul class="criteria-list">' + (story.criteres || []).map(function (c) { return '<li>' + esc(c) + '</li>'; }).join('') + '</ul>'
                + '<p class="tiny muted" style="margin:6px 0 0"><strong>Risques :</strong> ' + esc(story.risques || 'Aucun risque majeur identifié à ce stade.') + '</p>'
                + (story.moderation && story.moderation.criteres ? moderationMetaBadge(story.moderation.criteres, 'Critères modérés') : '')
                + '</details>'
                + tasksDetails
                + (story.refus_commentaire ? '<div class="refusal"><strong>Refus du PO :</strong> ' + esc(story.refus_commentaire)
                    + moderationMetaBadge(story.moderation && story.moderation.refus_commentaire) + '</div>' : '')
                + '</td>'
                + '<td class="number">' + estimationCell + '</td>'
                + '<td class="number"><strong>' + esc(story.valeur) + '</strong></td>'
                + '<td>' + (story.selectionnee ? '<span class="badge badge-role-sm">Sélectionnée</span>' : '<span class="badge badge-low">Hors Sprint</span>')
                + (canToggle ? '<div style="margin-top:5px"><button type="button" class="btn btn-ghost btn-small" data-action="toggle-sprint" data-story="' + esc(story.id) + '" data-selected="' + (story.selectionnee ? '0' : '1') + '">'
                    + (story.selectionnee ? 'Retirer' : 'Sélectionner') + '</button></div>' : '')
                + '</td>'
                + '<td>' + (story.etat === 'a_valider'
                    ? '<button type="button" class="btn btn-review btn-small" data-action="client-feedback" data-story="' + esc(story.id) + '"' + (canReview ? '' : ' disabled') + '>À valider</button>'
                    : statBadge(story.etat)) + '</td>'
                + '<td class="actions">'
                + (canDeclare ? '<button type="button" class="btn btn-secondary btn-small" data-action="declare-ready" data-story="' + esc(story.id) + '">Prête à valider</button> ' : '')
                + (canValidate ? '<button type="button" class="btn btn-success btn-small" data-action="validate-story" data-story="' + esc(story.id) + '"' + (validateDisabled ? ' disabled' : '') + '>Valider</button> '
                    + '<button type="button" class="btn btn-danger btn-small" data-action="refuse-story" data-story="' + esc(story.id) + '"' + (refuseDisabled ? ' disabled' : '') + '>Refuser</button>' : '')
                + (!canDeclare && !canValidate ? '<span class="tiny muted">—</span>' : '')
                + '</td></tr>';
        });

        html += '</tbody></table></div>';
        html += '<p class="table-note">' + esc(team.stories.length) + ' User Stories · '
            + esc(team.stats.us_selectionnees) + ' sélectionnée(s) · '
            + esc(team.stats.us_validees) + ' validée(s) · valeur validée : ' + esc(team.stats.valeur_validee) + '</p>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Kanban
    // -----------------------------------------------------------------------
    function renderKanbanView(team) {
        let html = pageHeading(
            'Kanban — Équipe ' + team.id,
            'Déplacez les tâches entre les quatre colonnes. Une tâche bloquée doit être expliquée.',
            toolHelpButton('kanban')
            + '<button type="button" class="btn btn-ghost btn-small" data-action="kanban-stats">Statistiques</button>'
            + (canManageTasks(team)
                ? '<button type="button" class="btn btn-primary btn-small" data-action="task-new">Nouvelle tâche</button>'
                : '')
        );
        html += '<div class="kpi-grid" style="margin-bottom:16px">'
            + kpi('Charge totale', hours(team.capacity.charge), team.capacity.active_tasks + ' tâche(s)', '')
            + kpi('Terminé', hours(team.capacity.done_hours), team.capacity.done_tasks + ' tâche(s)', 'kpi-success')
            + kpi('Reste à faire', hours(team.capacity.remaining_work), '', 'kpi-warning')
            + kpi('Tâches bloquées', String(team.capacity.blocked_tasks), '', team.capacity.blocked_tasks ? 'kpi-danger' : '')
            + '</div>';
        const canReport = userCanWrite() && myRole() !== 'DEVELOPER';
        html += '<div class="card">' + kanbanBoard(team, !canManageTasks(team), canReport) + '</div>';
        return html;
    }

    function kanbanBoard(team, readonly, canReport, canArchive) {
        canArchive = !!canArchive;
        const tasks = activeTasks(team);
        const columns = [
            { id: 'a_faire', label: 'À faire' },
            { id: 'en_cours', label: 'En cours' },
            { id: 'bloque', label: 'Bloqué' },
            { id: 'termine', label: 'Terminé' },
        ];

        let html = '<div class="kanban">';
        columns.forEach(function (column) {
            const list = tasks.filter(function (t) { return t.etat === column.id; });
            html += '<section class="kanban-column" data-state="' + column.id + '">'
                + '<div class="kanban-column-header"><h3>' + esc(column.label) + '</h3><span class="badge badge-count">' + list.length + '</span></div>'
                + '<div class="kanban-cards">';
            if (!list.length) {
                html += '<div class="empty-state tiny">Aucune tâche</div>';
            }
            list.forEach(function (task) {
                const story = storyById(team, task.us_id);
                const member = task.assigne ? memberByLogin(team, task.assigne) : null;
                const assignee = member && member.role === 'DEVELOPER' ? member : null;
                html += '<article class="task-card" data-state="' + esc(task.etat) + '">'
                    + '<div class="task-card-title">' + esc(task.titre) + taskModerationBadge(task) + '</div>'
                    + '<div class="task-card-meta"><span>' + esc(task.id) + ' · ' + esc(task.us_id) + '</span>'
                    + '<span>' + hours(task.heures) + '</span>'
                    + '<span>' + (assignee ? esc(assignee.login) + ' · ' + esc(roleLabel(assignee.role)) : 'Non affectée') + '</span></div>';
                if (story) html += '<div class="tiny muted">' + esc(story.titre) + '</div>';
                if (task.description) html += '<p class="tiny muted" style="margin:5px 0 0">' + esc(task.description)
                    + moderationMetaBadge(task.moderation && task.moderation.description) + '</p>';
                if (task.blocage) html += '<div class="task-blockage"><strong>Blocage :</strong> ' + esc(task.blocage)
                    + moderationMetaBadge(task.moderation && task.moderation.blocage) + '</div>';
                const locked = task.etat === 'termine';
                const moveOptions = locked ? columns : columns.filter(function (c) { return c.id !== 'termine'; });
                html += '<div class="task-card-actions">'
                    + '<select data-change="task-move" data-task="' + esc(task.id) + '" aria-label="État de ' + esc(task.id) + '"' + (readonly || locked ? ' disabled' : '') + '>'
                    + moveOptions.map(function (c) {
                        return '<option value="' + c.id + '"' + (task.etat === c.id ? ' selected' : '') + '>' + c.label + '</option>';
                    }).join('')
                    + '</select>'
                    + (!readonly ? '<button type="button" class="btn btn-ghost btn-small" data-action="task-edit" data-task="' + esc(task.id) + '"' + (locked ? ' disabled' : '') + '>Modifier</button>' : '')
                    + (!readonly ? '<button type="button" class="btn btn-ghost btn-small" data-action="task-archive" data-task="' + esc(task.id) + '">Archiver</button>' : '')
                    + (canArchive ? '<button type="button" class="btn btn-danger btn-small" data-action="task-archive" data-task="' + esc(task.id) + '" data-team="' + esc(team.id) + '">Archiver</button>' : '')
                    + (readonly && canReport && task.etat !== 'bloque' && task.etat !== 'termine'
                        ? '<button type="button" class="btn btn-ghost btn-small" data-action="report-blockage" data-task="' + esc(task.id) + '">Signaler un blocage</button>'
                        : '')
                    + '</div></article>';
            });
            html += '</div></section>';
        });
        html += '</div>';

        if (readonly) {
            const archived = (team.tasks || []).filter(function (t) { return t.archived; });
            if (archived.length) {
                html += '<details class="inline-details"><summary>Tâches archivées (' + archived.length + ')</summary><ul>'
                    + archived.map(function (task) {
                        return '<li>' + esc(task.id) + ' — ' + esc(task.titre) + ' · ' + esc(task.us_id) + ' · ' + hours(task.heures) + '</li>';
                    }).join('') + '</ul></details>';
            }
        }
        return html;
    }

    // -----------------------------------------------------------------------
    // Statistiques des décisions client
    // -----------------------------------------------------------------------
    function statsPct(value) {
        return value === null || value === undefined ? 'N/A' : value + ' %';
    }

    function renderKanbanStats(team) {
        const stats = team && team.client_decision_stats ? team.client_decision_stats : null;
        if (!stats) {
            return '<div class="empty-state"><strong>Statistiques indisponibles</strong>'
                + 'Les données de cette équipe n’ont pas encore été synchronisées.</div>';
        }
        if (!num(stats.total)) {
            return '<div class="empty-state"><strong>Aucune décision client</strong>'
                + 'Aucune tâche n’a encore reçu de décision du client. Les statistiques apparaîtront '
                + 'après la première validation ou le premier refus.</div>';
        }

        let html = '<section><h3>Vue globale</h3><div class="kpi-grid">'
            + kpi('Décisions client', String(num(stats.total)), 'tâches décidées', '')
            + kpi('Sans risque', String(num(stats.sans_risque)), statsPct(stats.pct_sans_risque) + ' des décisions', 'kpi-success')
            + kpi('Avec risque', String(num(stats.avec_risque)), statsPct(stats.pct_avec_risque) + ' des décisions', 'kpi-warning')
            + kpi('Acceptations', String(num(stats.acceptations)), statsPct(stats.taux_acceptation), 'kpi-success')
            + kpi('Refus', String(num(stats.refus)), statsPct(stats.taux_refus), 'kpi-danger')
            + '</div></section>';

        const developers = Array.isArray(stats.developers) ? stats.developers : [];
        html += '<section style="margin-top:18px"><h3>Statistiques par développeur</h3>'
            + '<div class="table-wrap"><table><thead><tr>'
            + '<th>Développeur</th><th>Tâches traitées</th><th>Sans risque</th><th>Avec risque</th>'
            + '<th>Acceptations</th><th>Refus</th><th>Refus sans risque</th><th>Refus avec risque</th><th>Refus global</th>'
            + '</tr></thead><tbody>'
            + developers.map(function (row) {
                return '<tr>'
                    + '<td><strong>' + esc(row.login) + '</strong><br><span class="tiny muted">' + esc(row.role_label) + '</span></td>'
                    + '<td>' + num(row.total) + '</td>'
                    + '<td>' + num(row.sans_risque) + '</td>'
                    + '<td>' + num(row.avec_risque) + '</td>'
                    + '<td>' + num(row.acceptations) + '</td>'
                    + '<td>' + num(row.refus) + '</td>'
                    + '<td>' + statsPct(row.taux_refus_sans_risque) + '</td>'
                    + '<td>' + statsPct(row.taux_refus_avec_risque) + '</td>'
                    + '<td>' + statsPct(row.taux_refus) + '</td>'
                    + '</tr>';
            }).join('')
            + '</tbody></table></div>'
            + '<p class="table-note">Un pourcentage est calculé uniquement lorsque son dénominateur est non nul ; sinon il est affiché « N/A ».</p></section>';

        const risks = Array.isArray(stats.risques) ? stats.risques : [];
        html += '<section style="margin-top:18px"><h3>Tâches à risque</h3>';
        if (!risks.length) {
            html += '<p class="muted small">Aucune tâche à risque n’a été enregistrée.</p>';
        } else {
            const groups = {};
            risks.forEach(function (item) {
                const key = item.developer || 'non_identifie';
                if (!groups[key]) groups[key] = [];
                groups[key].push(item);
            });
            Object.keys(groups).forEach(function (key) {
                const items = groups[key];
                html += '<h4 style="margin-top:14px">' + esc(items[0].developer_label || key) + ' <span class="muted small">(' + items.length + ')</span></h4>'
                    + '<div class="table-wrap"><table><thead><tr>'
                    + '<th>User Story</th><th>Tâche</th><th>Critères de risque</th><th>Décision</th><th>Date</th>'
                    + '</tr></thead><tbody>'
                    + items.map(function (item) {
                        const labels = (item.risk_labels || []).join(' · ');
                        const decision = item.decision === 'refusee'
                            ? '<span class="badge badge-state-refusee">Refus</span>'
                            : '<span class="badge badge-state-validee">Acceptation</span>';
                        return '<tr>'
                            + '<td>' + esc(item.story_id || '—')
                            + (item.story_titre ? '<br><span class="tiny muted">' + esc(item.story_titre) + '</span>' : '') + '</td>'
                            + '<td>' + (item.task_id ? esc(item.task_id) : '—')
                            + (item.task_titre ? '<br><span class="tiny muted">' + esc(item.task_titre) + '</span>' : '')
                            + (num(item.attempt) > 1 ? '<br><span class="tiny muted">Tentative ' + num(item.attempt) + '</span>' : '') + '</td>'
                            + '<td>' + esc(labels || '—') + '</td>'
                            + '<td>' + decision + '</td>'
                            + '<td class="tiny">' + esc(item.decided_at ? fmtDateTime(item.decided_at) : '—') + '</td>'
                            + '</tr>';
                    }).join('')
                    + '</tbody></table></div>';
            });
        }
        html += '</section>';
        return html;
    }

    function openKanbanStats(teamId) {
        const body = document.getElementById('kanban-stats-body');
        const modal = document.getElementById('kanban-stats-modal');
        if (!body || !modal) return;
        const team = teamId ? teamById(teamId) : myTeam();
        modal.dataset.team = teamId ? String(teamId) : (team ? String(team.id) : '');
        body.innerHTML = renderKanbanStats(team);
        openModal('kanban-stats-modal');
    }

    // -----------------------------------------------------------------------
    // Événements
    // -----------------------------------------------------------------------
    function renderEventsView(team) {
        let html = pageHeading(
            'Événements — Équipe ' + team.id,
            'Le formateur ne déclenche pas les événements : ils arrivent automatiquement et indépendamment pour chaque équipe.',
            toolHelpButton('events')
            + '<span class="badge badge-count">' + esc(team.events.triggered) + ' déclenché(s)</span>'
        );
        html += activeEventHtml(team, false);
        html += '<section class="card" style="margin-top:16px">'
            + '<div class="card-header"><div><h2>Historique des événements</h2><p class="card-subtitle">Décisions et conséquences</p></div></div>';
        const history = team.events.history || [];
        if (!history.length) {
            html += '<div class="empty-state"><strong>Aucun événement traité</strong>Les événements résolus apparaîtront ici.</div>';
        } else {
            html += history.map(function (event) {
                return '<div class="event-history-item">'
                    + '<strong>' + esc(event.titre) + '</strong>'
                    + '<p>' + esc(event.description) + '</p>'
                    + '<p><strong>Décision :</strong> ' + esc(event.choice) + '</p>'
                    + '<p><strong>Conséquence :</strong> ' + esc(event.consequence) + '</p>'
                    + '<p class="tiny muted">' + esc(fmtDateTime(event.responded_at)) + ' · ' + esc(event.responded_by) + '</p>'
                    + '</div>';
            }).join('');
        }
        html += '</section>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Chat
    // -----------------------------------------------------------------------
    function renderChatView(team) {
        markChatSeen();
        const messages = (team.messages || []).slice().sort(function (a, b) { return num(a.ts) - num(b.ts); });
        const canChat = userCanWrite();
        let html = pageHeading(
            'Chat — Équipe ' + team.id,
            'Messages textuels uniquement. Le formateur peut consulter et modérer les échanges.',
            toolHelpButton('chat')
            + '<span class="badge badge-count">' + messages.length + ' message(s)</span>'
        );
        html += '<div class="chat-layout">';
        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Salon d’équipe</h2><p class="card-subtitle">Visible par l’équipe et par le formateur.</p></div></div>'
            + '<div class="chat-box" role="log" aria-label="Messages de l’équipe">'
            + (messages.length ? messages.map(function (message) {
                const mine = message.author === App.state.me.login;
                return '<div class="chat-message' + (mine ? ' is-me' : '') + '">'
                    + '<div class="chat-message-head"><span>' + esc(message.author) + '</span><span>' + esc(fmtDateTime(message.at)) + messageModerationBadge(message) + '</span></div>'
                    + '<div class="chat-bubble">' + esc(message.text) + '</div></div>';
            }).join('') : '<div class="empty-state"><strong>Aucun message</strong>L’équipe peut commencer à échanger.</div>')
            + '</div>';
        if (canChat) {
            html += '<form id="chat-form" data-form="chat" class="chat-form">'
                + '<label class="sr-only" for="chat-text">Message</label>'
                + '<textarea id="chat-text" name="text" maxlength="' + esc((App.state.config && App.state.config.chat_max) || 500) + '" required placeholder="Écrire un message à l’équipe…"></textarea>'
                + '<div class="chat-form-footer"><span class="muted">Pas de pièces jointes · ' + esc((App.state.config && App.state.config.chat_max) || 500) + ' caractères maximum</span>'
                + '<button type="submit" class="btn btn-primary btn-small">Envoyer</button></div>'
                + '</form>';
        } else {
            html += '<p class="table-note" style="margin-top:10px">L’envoi de messages est bloqué pendant la pause et après la fin de la simulation.</p>';
        }
        html += '</section>';

        html += '<section class="card">'
            + '<div class="card-header"><div><h2>Annonces du formateur</h2><p class="card-subtitle">Globales ou ciblées</p></div></div>'
            + announcementsHtml(App.state.announcements || [], 0)
            + '</section></div>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Rôles et échanges
    // -----------------------------------------------------------------------
    function renderRolesView(team) {
        let html = pageHeading(
            'Rôles et échanges — Équipe ' + team.id,
            'Un échange est une permutation entre deux membres. Il faut l’accord des deux et les règles de composition sont revérifiées par le serveur.',
            toolHelpButton('roles')
        );
        html += '<div class="roles-grid" style="margin-bottom:16px">'
            + team.members.map(function (member) {
                return '<div class="role-card"><span class="role-avatar">' + esc(member.login.slice(0, 2).toUpperCase()) + '</span>'
                    + '<div><strong>' + esc(member.login) + '</strong><span class="small">' + roleBadgeHtml(member.role) + '</span></div></div>';
            }).join('') + '</div>';

        if (userCanWrite()) {
            const candidates = team.members.filter(function (m) {
                if (m.login === App.state.me.login) return false;
                if (m.role === myRole()) return false;
                if (m.role === 'DEVELOPER' && myRole() === 'DEVELOPER') return false;
                return true;
            });
            html += '<section class="card" style="margin-bottom:16px">'
                + '<div class="card-header"><div><h2>Proposer un échange</h2><p class="card-subtitle">La proposition expire après cinq minutes.</p></div></div>';
            if (!candidates.length) {
                html += '<div class="empty-state"><strong>Aucun échange utile disponible</strong>Les échanges entre deux Developers sont refusés.</div>';
            } else {
                html += '<form id="swap-form" data-form="swap" class="form-row">'
                    + '<div class="field"><label for="swap-target">Membre destinataire</label><select id="swap-target" name="target" required>'
                    + candidates.map(function (m) {
                        return '<option value="' + esc(m.login) + '">' + esc(m.login) + ' — ' + esc(roleLabel(m.role)) + '</option>';
                    }).join('') + '</select></div>'
                    + '<button type="submit" class="btn btn-primary">Envoyer la proposition</button></form>';
            }
            html += '</section>';
        }

        html += '<div class="grid-2">'
            + '<section class="card"><div class="card-header"><div><h2>Propositions en attente</h2><p class="card-subtitle">Accord requis des deux membres</p></div></div>'
            + swapsHtml(team, !userCanWrite()) + '</section>';

        const other = (team.swaps || []).filter(function (s) { return s.status !== 'pending'; }).slice(-8).reverse();
        html += '<section class="card"><div class="card-header"><div><h2>Propositions passées</h2><p class="card-subtitle">Les huit plus récentes</p></div></div>'
            + (other.length ? '<div class="section-stack">' + other.map(function (swap) {
                return '<div class="swap-item"><strong>' + esc(swap.from) + '</strong> (' + esc(roleLabel(swap.from_role)) + ') ? <strong>' + esc(swap.to) + '</strong> ('
                    + esc(roleLabel(swap.to_role)) + ') <span class="badge badge-state-' + (swap.status === 'accepted' ? 'validee' : swap.status === 'refused' ? 'refusee' : 'a_faire') + '">'
                    + esc(swapStatusLabel(swap.status)) + '</span>'
                    + '<div class="swap-meta"><span>Réponse : ' + esc(fmtDateTime(swap.responded_at)) + '</span></div></div>';
            }).join('') + '</div>' : '<div class="empty-state"><strong>Aucune proposition passée</strong></div>')
            + '</section></div>';

        html += '<section class="card" style="margin-top:16px"><div class="card-header"><div><h2>Règles à respecter</h2></div></div>'
            + '<ul class="muted small"><li>Chaque équipe conserve exactement un PO, un Scrum Master et au moins un Developer.</li>'
            + '<li>Un membre ne peut avoir qu’une seule proposition en attente à la fois.</li>'
            + '<li>Une proposition expirée ou refusée ne modifie aucun rôle.</li>'
            + '<li>Le serveur applique la permutation de façon atomique après une nouvelle vérification.</li></ul></section>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Interface formateur — vue globale
    // -----------------------------------------------------------------------
    function passwordRequestsHtml() {
        const requests = App.state.password_requests || [];
        if (!requests.length) return '';
        return '<section class="card" style="margin-bottom:16px">'
            + '<div class="card-header"><div><h2>Demandes de réinitialisation</h2>'
            + '<p class="card-subtitle">Vérifiez l’identité de l’étudiant avant de réinitialiser son mot de passe.</p></div>'
            + '<span class="badge badge-high">' + requests.length + '</span></div>'
            + '<div class="section-stack">' + requests.map(function (request) {
                return '<div class="swap-item"><div><strong>' + esc(request.login) + '</strong>'
                    + '<div class="swap-meta"><span>Demande reçue le ' + esc(fmtDateTime(request.requested_at)) + '</span></div></div>'
                    + '<button type="button" class="btn btn-technical btn-small" data-action="teacher-reset-password"'
                    + ' data-login="' + esc(request.login) + '">Réinitialiser</button></div>';
            }).join('') + '</div></section>';
    }

    function renderTeacherGlobal() {
        const session = App.state.session;
        const teams = App.state.teams || [];
        let html = pageHeading(
            'Supervision de la séance',
            'Vue globale des trois équipes. Aucun classement ni note globale n’est produit.',
            '<button type="button" class="btn btn-technical btn-small" data-action="teacher-reset-password">Réinitialiser un mot de passe</button>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="announcements">Envoyer une annonce</button>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="bilan">Bilan</button>'
        );

        const canPause = session.phase === 'running';
        const canResume = session.phase === 'paused';
        const canReset = !['running', 'paused'].includes(session.phase);
        html += '<section class="card session-controls" style="margin-bottom:16px">'
            + '<div><h2 style="margin-bottom:3px">Séance du ' + esc(session.date) + '</h2>'
            + '<p class="muted small" style="margin:0">Ouverture ' + esc(session.schedule.open) + ' · simulation ' + esc(session.schedule.start)
            + ' ? ' + esc(session.schedule.end) + ' · temps restant <strong>' + esc(formatRemaining((num(session.sim_end_ms) - nowMs()) / 1000))
            + '</strong></p></div>'
            + '<div class="actions">'
            + (canPause ? '<button type="button" class="btn btn-warning" data-action="teacher-pause">Suspendre</button>' : '')
            + (canResume ? '<button type="button" class="btn btn-success" data-action="teacher-resume">Reprendre</button>' : '')
            + (session.phase === 'finished' ? '<button type="button" class="btn btn-danger" data-action="teacher-stop">Arrêter la session</button>' : '')
            + '<button type="button" class="btn btn-ghost" data-action="teacher-reset"' + (canReset ? '' : ' disabled') + '>Nouvelle séance</button>'
            + '</div></section>';

        if (canReset) {
            html += '<p class="table-note" style="margin:-8px 0 16px">La remise à zéro est possible hors simulation en cours. Elle réinitialise backlog, tâches, événements, chats, annonces, historique et chronomètre.</p>';
        }

        html += passwordRequestsHtml();

        html += '<div class="teacher-grid">';
        teams.forEach(function (team) {
            const stats = team.stats || {};
            const cap = team.capacity || {};
            const active = team.events && team.events.active;
            const pendingSwaps = (team.swaps || []).filter(function (s) { return s.status === 'pending'; }).length;
            const blocked = cap.blocked_tasks || 0;
            const lastMessage = (team.messages || []).slice(-1)[0];
            html += '<article class="card teacher-team-card">'
                + '<div class="teacher-team-head"><div><h2>' + esc(team.name) + '</h2>'
                + '<p class="card-subtitle">' + esc((team.members || []).map(function (m) { return m.login + ' (' + roleLabel(m.role) + ')'; }).join(' · ')) + '</p></div>'
                + '<span class="badge badge-phase ' + phaseClass(session.phase) + '">' + esc(session.phase_label) + '</span></div>'
                + '<div class="teacher-goal"><strong>Sprint Goal :</strong> ' + esc(team.sprint_goal) + '</div>'
                + '<div class="teacher-stats">'
                + '<div class="teacher-stat"><span>US validées</span><strong>' + esc(stats.us_validees) + '</strong></div>'
                + '<div class="teacher-stat"><span>Valeur livrée</span><strong>' + esc(stats.valeur_validee) + '</strong></div>'
                + '<div class="teacher-stat"><span>SP validés</span><strong>' + esc(stats.points_valides) + '</strong></div>'
                + '<div class="teacher-stat"><span>Capacité restante</span><strong>' + esc(hours(cap.remaining_capacity)) + '</strong></div>'
                + '<div class="teacher-stat"><span>Tâches terminées</span><strong>' + esc(cap.done_tasks || 0) + ' / ' + esc(cap.active_tasks || 0) + '</strong></div>'
                + '<div class="teacher-stat"><span>Blocages actuels</span><strong>' + esc(blocked) + '</strong></div>'
                + '</div>'
                + '<div class="pill-row">'
                + (active ? '<span class="badge badge-high">Événement : ' + esc(active.titre) + '</span>' : '<span class="badge badge-count">Aucun événement actif</span>')
                + (pendingSwaps ? '<span class="badge badge-role-sm">' + pendingSwaps + ' échange(s)</span>' : '')
                + '</div>'
                + (lastMessage ? '<p class="tiny muted" style="margin:0">Dernier message de ' + esc(lastMessage.author) + ' : ' + esc(lastMessage.text.slice(0, 100)) + (lastMessage.text.length > 100 ? '…' : '') + '</p>' : '')
                + '<div class="teacher-team-footer">'
                + '<button type="button" class="btn btn-secondary btn-small" data-view="team-' + team.id + '">Ouvrir l’équipe</button>'
                + '<button type="button" class="btn btn-ghost btn-small" data-view="announcements">Annonce</button>'
                + '</div></article>';
        });
        html += '</div>';

        const announcements = (App.state.announcements || []).slice(-3).reverse();
        html += '<section class="card" style="margin-top:16px"><div class="card-header"><div><h2>Dernières annonces</h2></div>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="announcements">Tout voir</button></div>'
            + announcementsHtml(announcements, 3) + '</section>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Interface formateur — équipe détaillée
    // -----------------------------------------------------------------------
    function renderTeacherTeam(teamId) {
        const team = teamById(teamId);
        if (!team) {
            return '<div class="alert alert-error">Équipe introuvable.</div>';
        }
        const stats = team.stats || {};
        let html = '<div class="team-detail-header"><div><h1>Équipe ' + esc(team.id) + '</h1>'
            + '<p>' + esc((team.members || []).map(function (m) { return m.login + ' (' + roleLabel(m.role) + ')'; }).join(' · ')) + '</p></div>'
            + '<div class="pill-row"><span class="badge">' + esc(App.state.session.phase_label) + '</span>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="global">Retour vue globale</button></div></div>';

        html += '<div class="kpi-grid" style="margin-bottom:16px">'
            + kpi('US validées', String(stats.us_validees), stats.us_pretes + ' prête(s) à valider', 'kpi-success')
            + kpi('Valeur métier', String(stats.valeur_validee), 'Valeur des US validées', '')
            + kpi('Story Points', String(stats.points_valides), stats.points_selectionnes + ' sélectionnés', '')
            + kpi('Capacité restante', hours(team.capacity.remaining_capacity), hours(team.capacity.charge) + ' de charge', 'kpi-primary')
            + '</div>';

        html += '<div class="grid-2" style="margin-bottom:16px">'
            + '<section class="card card-accent"><h2>Sprint Goal</h2><p class="goal-text">' + esc(team.sprint_goal) + goalModerationBadge(team) + '</p>'
            + (team.sprint_goal_justification ? '<p class="small muted"><strong>Justification :</strong> ' + esc(team.sprint_goal_justification) + '</p>' : '')
            + '<h3 style="margin-top:12px">Rôles</h3>' + membersHtml(team) + '</section>'
            + '<section class="card"><h2>Capacité et charge</h2>' + capacityMetrics(team) + '</section></div>';

        html += '<section class="card" style="margin-bottom:16px">'
            + '<div class="card-header"><div><h2>Sprint Review et rétrospective</h2>'
            + '<p class="card-subtitle">Éléments observables pendant la simulation, en lecture seule.</p></div></div>'
            + reviewSummaryHtml(team)
            + '<h3 style="margin-top:16px">Rétrospective collective</h3>'
            + retrospectiveBodyHtml(team, true) + '</section>';

        html += '<section class="card" style="margin-bottom:16px">'
            + '<div class="card-header"><div><h2>Événement en cours</h2><p class="card-subtitle">Les décisions sont prises par l’équipe.</p></div></div>'
            + activeEventHtml(team, true) + '</section>';

        html += '<section class="card" style="margin-bottom:16px"><div class="card-header"><div><h2>Product Backlog</h2>'
            + '<p class="card-subtitle">Priorités, estimations, valeur et état</p></div></div>'
            + backlogTable(team, { canPriority: false, canEstimate: false, canSelect: false, readonly: true }) + '</section>';

        html += '<section class="card" style="margin-bottom:16px"><div class="card-header"><div><h2>Kanban et blocages</h2>'
            + '<p class="card-subtitle">' + esc(team.capacity.active_tasks) + ' tâche(s) active(s)</p></div>'
            + '<button type="button" class="btn btn-ghost btn-small" data-action="kanban-stats" data-team="' + esc(team.id) + '">Statistiques</button></div>'
            + kanbanBoard(team, true, false, true) + '</section>';

        html += '<div class="grid-2" style="margin-bottom:16px">'
            + '<section class="card"><h2>Rôles et échanges</h2>' + swapsHtml(team, true)
            + '<h3 style="margin-top:14px">Propositions passées</h3>'
            + ((team.swaps || []).filter(function (s) { return s.status !== 'pending'; }).length
                ? '<ul class="small">' + (team.swaps || []).filter(function (s) { return s.status !== 'pending'; }).slice(-8).reverse().map(function (s) {
                    return '<li>' + esc(s.from) + ' ? ' + esc(s.to) + ' — ' + esc(swapStatusLabel(s.status)) + '</li>';
                }).join('') + '</ul>'
                : '<p class="muted small">Aucune proposition passée.</p>')
            + '</section>'
            + '<section class="card"><h2>Chat de l’équipe</h2><p class="card-subtitle">Lecture seule</p>'
            + chatReadOnly(team) + '</section></div>';

        html += '<section class="card"><div class="card-header"><div><h2>Historique des événements</h2></div></div>'
            + ((team.events.history || []).length ? (team.events.history || []).map(function (event) {
                return '<div class="event-history-item"><strong>' + esc(event.titre) + '</strong>'
                    + '<p><strong>Décision :</strong> ' + esc(event.choice) + '</p>'
                    + '<p><strong>Conséquence :</strong> ' + esc(event.consequence) + '</p>'
                    + '<p class="tiny muted">' + esc(fmtDateTime(event.responded_at)) + ' · ' + esc(event.responded_by) + '</p></div>';
            }).join('') : '<div class="empty-state"><strong>Aucun événement traité</strong></div>')
            + '</section>';

        return html;
    }

    function chatReadOnly(team) {
        const messages = (team.messages || []).slice().sort(function (a, b) { return num(a.ts) - num(b.ts); });
        if (!messages.length) return '<div class="empty-state"><strong>Aucun message</strong></div>';
        return '<div class="chat-box">' + messages.map(function (message) {
            return '<div class="chat-message"><div class="chat-message-head"><span>' + esc(message.author) + '</span><span>' + esc(fmtDateTime(message.at)) + messageModerationBadge(message) + '</span></div>'
                + '<div class="chat-bubble">' + esc(message.text) + '</div></div>';
        }).join('') + '</div>';
    }

    // -----------------------------------------------------------------------
    // Interface formateur — annonces, historique, bilan
    // -----------------------------------------------------------------------
    function renderTeacherAnnouncements() {
        let html = pageHeading(
            'Annonces du formateur',
            'Message global ou ciblé vers une équipe. Les étudiants voient les annonces qui les concernent.',
            ''
        );
        html += '<div class="grid-2"><section class="card">'
            + '<div class="card-header"><div><h2>Nouvelle annonce</h2><p class="card-subtitle">Texte simple, 500 caractères maximum.</p></div></div>'
            + '<form id="announce-form" data-form="announce">'
            + '<div class="field"><label for="announce-target">Destinataires</label><select id="announce-target" name="target">'
            + '<option value="0">Toutes les équipes</option><option value="1">Équipe 1</option><option value="2">Équipe 2</option><option value="3">Équipe 3</option>'
            + '</select></div>'
            + '<div class="field" style="margin-top:10px"><label for="announce-text">Message</label>'
            + '<textarea id="announce-text" name="text" rows="5" minlength="3" maxlength="500" required placeholder="Votre annonce…"></textarea></div>'
            + '<div class="modal-actions"><button type="submit" class="btn btn-primary">Envoyer l’annonce</button></div></form>'
            + '</section>'
            + '<section class="card"><div class="card-header"><div><h2>Annonces envoyées</h2><p class="card-subtitle">'
            + (App.state.announcements || []).length + ' annonce(s)</p></div></div>'
            + announcementsHtml(App.state.announcements || [], 0, true) + '</section></div>';
        return html;
    }

    // -----------------------------------------------------------------------
    // Interface formateur — modération des contenus libres
    // -----------------------------------------------------------------------
    function moderationButtons(type, teamId, contentId, field, text, index, label) {
        const value = text === null || text === undefined ? '' : String(text);
        if (!value.trim()) return '';
        const attrs = ' data-type="' + esc(type) + '"'
            + ' data-team="' + esc(teamId) + '"'
            + ' data-content="' + esc(contentId) + '"'
            + ' data-field="' + esc(field) + '"'
            + ' data-current="' + esc(value) + '"'
            + ' data-label="' + esc(label) + '"'
            + (index !== null && index !== undefined && index !== '' ? ' data-index="' + esc(index) + '"' : '');
        return '<div class="moderation-actions">'
            + '<button type="button" class="btn btn-ghost btn-small" data-action="moderate-edit"' + attrs + '>Modifier</button>'
            + '<button type="button" class="btn btn-danger btn-small" data-action="moderate-delete"' + attrs + '>Supprimer</button>'
            + '</div>';
    }

    function moderationField(label, type, teamId, contentId, field, text, meta, index, author) {
        const value = text === null || text === undefined ? '' : String(text);
        return '<div class="moderation-field">'
            + '<div class="moderation-field-head"><span class="moderation-label">' + esc(label) + '</span>'
            + moderationMetaBadge(meta) + '</div>'
            + (author ? '<div class="moderation-author">' + esc(author) + '</div>' : '')
            + (value.trim()
                ? '<div class="moderation-text">' + esc(value) + '</div>'
                    + (meta && meta.action === 'delete'
                        ? ''
                        : moderationButtons(type, teamId, contentId, field, value, index, label))
                : '<div class="moderation-text muted">Aucun contenu.</div>')
            + '</div>';
    }

    function renderTeacherModeration() {
        const teams = App.state.teams || [];
        let html = pageHeading(
            'Modération des contenus',
            'Corriger ou supprimer les textes libres produits par les étudiants. Chaque intervention est tracée dans l’historique.',
            '<button type="button" class="btn btn-ghost btn-small" data-view="announcements">Gérer les annonces</button>'
            + '<button type="button" class="btn btn-ghost btn-small" data-view="history">Historique des interventions</button>'
        );
        html += '<div class="alert alert-info">La correction remplace le texte visible par tous. La suppression retire le contenu de l’affichage normal sans supprimer la tâche ou la User Story concernée. L’historique conserve l’ancien et le nouveau texte.</div>';

        teams.forEach(function (team) {
            const messages = (team.messages || []).slice().sort(function (a, b) { return num(a.ts) - num(b.ts); });
            const moderableMessages = messages.filter(function (message) { return message.author !== 'systeme'; });
            const tasks = (team.tasks || []).slice().sort(function (a, b) { return String(a.id).localeCompare(String(b.id), 'fr', { numeric: true }); });
            const stories = (team.stories || []).slice().sort(function (a, b) { return String(a.id).localeCompare(String(b.id), 'fr', { numeric: true }); });
            const backlogDate = team.initialized_at ? fmtDateTime(team.initialized_at) : '';
            const storyAuthor = 'Backlog de l’équipe' + (backlogDate ? ' · version du ' + backlogDate : '');
            const criterionAuthor = 'Critère d’acceptation' + (backlogDate ? ' · version du ' + backlogDate : '');
            const goalAuthor = team.sprint_goal_updated_by
                ? 'Dernier auteur : ' + team.sprint_goal_updated_by
                    + (team.sprint_goal_updated_at ? ' · ' + fmtDateTime(team.sprint_goal_updated_at) : '')
                : 'Contenu collectif de l’équipe';

            html += '<section class="card moderation-team"><div class="card-header"><div><h2>Équipe ' + esc(team.id) + '</h2>'
                + '<p class="card-subtitle">' + esc((team.members || []).map(function (m) { return m.login; }).join(' · ')) + '</p></div>'
                + '<span class="badge badge-count">' + messages.length + ' message(s)</span></div>';

            html += '<div class="moderation-block"><h3>Sprint Goal</h3>'
                + moderationField('Sprint Goal', 'sprint_goal', team.id, 'goal', 'sprint_goal', team.sprint_goal,
                    team.sprint_goal_moderation && team.sprint_goal_moderation.sprint_goal, null, goalAuthor);
            if (team.sprint_goal_justification) {
                html += moderationField('Justification de la décision', 'sprint_goal', team.id, 'goal', 'sprint_goal_justification',
                    team.sprint_goal_justification,
                    team.sprint_goal_moderation && team.sprint_goal_moderation.sprint_goal_justification,
                    null, 'Décision collective de l’équipe');
            }
            html += '</div>';

            if (team.retrospective) {
                const retroAuthor = team.retrospective_updated_by
                    ? 'Dernière contribution : ' + team.retrospective_updated_by
                        + (team.retrospective_updated_at ? ' · ' + fmtDateTime(team.retrospective_updated_at) : '')
                    : 'Contenu collectif de l’équipe';
                html += '<div class="moderation-block"><h3>Rétrospective</h3>'
                    + moderationField('Rétrospective', 'retrospective', team.id, 'retro', 'retrospective', team.retrospective,
                        team.retrospective_moderation && team.retrospective_moderation.retrospective, null, retroAuthor)
                    + '</div>';
            }

            html += '<div class="moderation-block"><h3>Chat de l’équipe</h3>';
            if (!moderableMessages.length) {
                html += '<div class="empty-state"><strong>Aucun message à modérer</strong></div>';
            } else {
                html += '<details class="moderation-details"><summary>' + moderableMessages.length + ' message(s) à modérer</summary>'
                    + moderableMessages.map(function (message) {
                        const meta = message.edited_by ? { action: 'edit', by: message.edited_by } : null;
                        return '<div class="moderation-item"><div class="moderation-meta"><span>' + esc(message.author)
                            + ' · ' + esc(fmtDateTime(message.at)) + ' · ' + esc(message.id) + '</span>'
                            + moderationMetaBadge(meta) + '</div>'
                            + '<div class="moderation-text">' + esc(message.text) + '</div>'
                            + moderationButtons('chat', team.id, message.id, 'text', message.text, null, 'message de chat ' + message.id)
                            + '</div>';
                    }).join('') + '</details>';
            }
            html += '</div>';

            html += '<div class="moderation-block"><h3>Tâches</h3>';
            if (!tasks.length) {
                html += '<div class="empty-state"><strong>Aucune tâche</strong></div>';
            } else {
                html += '<details class="moderation-details"><summary>' + tasks.length + ' tâche(s) à modérer</summary>'
                    + tasks.map(function (task) {
                        const moderation = task.moderation || {};
                        let author = 'Auteur non renseigné';
                        if (task.created_by) {
                            author = 'Créée par : ' + task.created_by
                                + (task.updated_at ? ' · dernière modification le ' + fmtDateTime(task.updated_at) : '');
                        } else if (task.updated_by) {
                            author = 'Dernière modification : ' + task.updated_by
                                + (task.updated_at ? ' le ' + fmtDateTime(task.updated_at) : '');
                        }
                        let fields = moderationField('Titre', 'task', team.id, task.id, 'titre', task.titre, moderation.titre, null, author);
                        fields += moderationField('Description', 'task', team.id, task.id, 'description', task.description || '', moderation.description, null, author);
                        if (task.blocage) {
                            fields += moderationField('Blocage', 'task', team.id, task.id, 'blocage', task.blocage, moderation.blocage, null,
                                task.blocage_by
                                    ? 'Blocage déclaré par : ' + task.blocage_by
                                        + (task.updated_at ? ' le ' + fmtDateTime(task.updated_at) : '')
                                    : author);
                        }
                        return '<details class="moderation-subitem"><summary><strong>' + esc(task.id) + '</strong> — ' + esc(task.titre)
                            + taskModerationBadge(task) + (task.archived ? ' <span class="badge badge-count">Archivée</span>' : '') + '</summary>'
                            + fields + '</details>';
                    }).join('') + '</details>';
            }
            html += '</div>';

            const moderableStories = stories.filter(function (story) {
                const refusModeration = story.moderation && story.moderation.refus_commentaire;
                return story.refus_commentaire && !(refusModeration && refusModeration.action === 'delete');
            });
            if (moderableStories.length) {
                html += '<div class="moderation-block"><h3>Commentaires de refus</h3>';
                html += '<details class="moderation-details"><summary>' + moderableStories.length + ' commentaire(s) de refus à modérer</summary>'
                    + moderableStories.map(function (story) {
                        const moderation = story.moderation || {};
                        return '<details class="moderation-subitem"><summary><strong>' + esc(story.id) + '</strong> — ' + esc(story.titre)
                            + storyModerationBadge(story) + '</summary>'
                            + moderationField('Commentaire de refus', 'story', team.id, story.id, 'refus_commentaire', story.refus_commentaire,
                                moderation.refus_commentaire, null, 'Refus du Product Owner' + (story.refusee_at ? ' · ' + fmtDateTime(story.refusee_at) : ''))
                            + '</details>';
                    }).join('') + '</details></div>';
            }

            html += '</section>';
        });

        return html;
    }

    function renderTeacherHistory() {
        let html = pageHeading(
            'Historique de la simulation',
            'Journal chronologique des actions importantes. Aucune date de naissance n’y est enregistrée.',
            '<button type="button" class="btn btn-ghost btn-small" data-action="history-refresh">Actualiser</button>'
        );
        if (App.historyLoading && !App.history) {
            return html + '<div class="loading-card"><span class="spinner"></span>Chargement de l’historique…</div>';
        }
        if (!App.history) {
            return html + '<div class="empty-state"><strong>Historique non chargé</strong>Cliquez sur Actualiser.</div>';
        }
        const entries = (App.history.entries || []);
        const teams = ['all', '0', '1', '2', '3'];
        html += '<div class="card"><div class="card-header"><div><h2>' + entries.length + ' entrée(s)</h2>'
            + '<p class="card-subtitle">Ordre chronologique</p></div>'
            + '<div class="field" style="max-width:210px"><label for="history-filter">Filtrer</label><select id="history-filter" data-change="history-filter">'
            + teams.map(function (t) {
                const label = t === 'all' ? 'Toutes les entrées' : t === '0' ? 'Formateur / global' : 'Équipe ' + t;
                return '<option value="' + t + '"' + (App.historyFilter === t ? ' selected' : '') + '>' + label + '</option>';
            }).join('') + '</select></div></div>';

        const filtered = entries.filter(function (entry) {
            return App.historyFilter === 'all' || String(entry.team) === App.historyFilter;
        });
        if (!filtered.length) {
            html += '<div class="empty-state"><strong>Aucune entrée</strong></div>';
        } else {
            html += '<div class="history-list">' + filtered.map(function (entry) {
                const teamLabel = Number(entry.team) === 0 ? 'Global' : 'Équipe ' + entry.team;
                const details = entry.details && typeof entry.details === 'object' ? entry.details : null;
                let detailHtml = '';
                if (details && (details.old !== undefined || details.new !== undefined)) {
                    detailHtml = '<span class="history-moderation-detail"><strong>Ancien texte :</strong> '
                        + esc(details.old === null || details.old === undefined || details.old === '' ? '—' : details.old)
                        + '<br><strong>Nouveau texte :</strong> '
                        + esc(details.new === null || details.new === undefined || details.new === '' ? '—' : details.new)
                        + '</span>';
                }
                return '<div class="history-entry">'
                    + '<span class="history-time">' + esc(fmtDateTime(entry.at)) + '</span>'
                    + '<span class="history-team">' + esc(teamLabel) + '</span>'
                    + '<span class="history-author">' + esc(entry.author) + '<br><span class="tiny muted">' + esc(historyTypeLabel(entry.type)) + '</span></span>'
                    + '<span class="history-message">' + esc(entry.message) + '</span>'
                    + detailHtml
                    + '</div>';
            }).join('') + '</div>';
        }
        html += '</div>';
        return html;
    }

    // Indicateurs communs aux bilans formateur et étudiant : une seule liste
    // et un seul rendu pour éviter toute divergence entre les deux interfaces.
    const BILAN_ROWS = [
        ['User Stories sélectionnées', 'us_selectionnees', ''],
        ['User Stories terminées (prêtes + validées)', 'us_terminees', ''],
        ['User Stories validées', 'us_validees', ''],
        ['User Stories refusées', 'us_refusees', ''],
        ['Story Points validés', 'points_valides', ''],
        ['Valeur métier validée', 'valeur_validee', ''],
        ['Valeur métier sélectionnée', 'valeur_selectionnee', ''],
        ['Tâches terminées', 'taches_terminees', '/ total'],
        ['Tâches en cours', 'taches_en_cours', ''],
        ['Tâches bloquées', 'taches_bloquees', ''],
        ['Tâches à faire', 'taches_a_faire', ''],
        ['Heures réalisées', 'heures_terminees', 'h'],
        ['Heures restantes', 'heures_restantes', 'h'],
        ['Capacité initiale', 'capacite_initiale', 'h'],
        ['Capacité réduite par événements', 'capacite_reduite', 'h'],
        ['Capacité restante', 'capacite_restante', 'h'],
        ['Blocages rencontrés', 'blocages_rencontres', ''],
        ['Changements de priorité', 'changements_priorite', ''],
        ['Événements déclenchés', 'evenements_declenches', ''],
        ['Décisions prises', 'decisions', ''],
        ['Échanges de rôles', 'echanges_roles', ''],
        ['Changements de Sprint Goal', 'changements_sprint_goal', ''],
        ['Actions d’adaptation', 'actions_adaptation', ''],
        ['Couverture du sprint (US validées / sélectionnées)', 'couverture_sprint', '%'],
    ];

    function bilanTableHtml(teams) {
        let html = '<div class="card"><div class="table-wrap"><table class="bilan-table"><thead><tr><th>Indicateur</th>'
            + teams.map(function (team) { return '<th class="number">' + esc(team.name) + '</th>'; }).join('')
            + '</tr></thead><tbody>';
        BILAN_ROWS.forEach(function (row) {
            html += '<tr><td class="metric-name">' + esc(row[0]) + '</td>'
                + teams.map(function (team) {
                    let value = team[row[1]];
                    if (row[1] === 'taches_terminees') {
                        value = String(team.taches_terminees) + ' / ' + String(team.taches_total);
                    } else if (row[2] === 'h') {
                        value = hours(value);
                    } else if (row[2] === '%') {
                        value = String(value) + ' %';
                    }
                    return '<td class="number value-cell">' + esc(value) + '</td>';
                }).join('') + '</tr>';
        });
        return html + '</tbody></table></div>'
            + '<p class="table-note">La valeur métier est indépendante des Story Points. Un nombre élevé de points validés ne garantit pas la plus forte valeur livrée.</p></div>';
    }

    function bilanTeamCardsHtml(teams) {
        return '<div class="grid-3' + (teams.length === 1 ? ' bilan-single' : '') + '" style="margin-top:16px">'
            + teams.map(function (team) {
                return '<section class="card card-accent"><h2>' + esc(team.name) + '</h2>'
                    + '<p class="small muted">Sprint Goal</p><p class="goal-text">' + esc(team.sprint_goal) + '</p>'
                    + '<p class="small muted">Rétrospective</p><p class="small">'
                    + esc(team.retrospective || 'Aucune rétrospective enregistrée.') + '</p>'
                    + '<p class="small muted">Membres : ' + esc((team.members || []).map(function (m) { return m.login + ' (' + roleLabel(m.role) + ')'; }).join(' · ')) + '</p>'
                    + '<p class="small muted">Événements : ' + esc(team.evenements_declenches) + ' déclenché(s), '
                    + esc(team.evenements_traites) + ' traité(s), ' + esc(team.evenements_en_attente) + ' en attente.</p>'
                    + '</section>';
            }).join('') + '</div>';
    }

    function renderTeacherBilan() {
        const phaseFinished = !!(App.state && App.state.session && App.state.session.phase === 'finished');
        const bilan = App.state && App.state.bilan;
        const bilanReady = phaseFinished && bilan && Array.isArray(bilan.teams) && bilan.teams.length > 0;
        const exportButton = '<button type="button" class="btn btn-secondary btn-small"' + (bilanReady ? ' data-action="export-bilan"' : ' disabled') + '>Export JSON</button>';
        let html = pageHeading(
            'Bilan comparatif',
            'Support de débriefing : comparaison des trois équipes, sans classement ni note globale.',
            toolHelpButton('bilan') + ' ' + exportButton + ' ' + '<button type="button" class="btn btn-ghost btn-small" data-view="global">Vue globale</button>'
        );
        if (!phaseFinished) {
            html += '<div class="card card-warning"><h2>Bilan disponible à la fin de la simulation</h2>'
                + '<p>Les résultats seront calculés automatiquement à ' + esc(App.state.session.schedule.end) + '. '
                + 'Temps restant : <strong>' + esc(formatRemaining((num(App.state.session.sim_end_ms) - nowMs()) / 1000)) + '</strong>.</p>'
                + '<p class="muted small">En attendant, la vue globale permet de suivre les indicateurs de chaque équipe sans les classer.</p></div>';
            return html;
        }
        if (!bilanReady) {
            html += '<div class="empty-state"><strong>Bilan indisponible</strong>Actualisez la page.</div>';
            return html;
        }

        html += bilanTableHtml(bilan.teams);
        html += bilanTeamCardsHtml(bilan.teams);
        return html;
    }

    function renderStudentHistory() {
        const htmlHead = pageHeading(
            'Historique — Équipe ' + num(App.state.me.team),
            'Journal chronologique des actions de votre équipe. Seules les actions concernant votre équipe sont affichées.',
            ''
        );
        if (!App.history) {
            return htmlHead + '<div class="empty-state"><strong>Historique non chargé</strong>L’historique apparaîtra lors de la prochaine synchronisation.</div>';
        }
        const entries = (App.history.entries || []).slice().sort(function (a, b) {
            return num(b.ts || 0) - num(a.ts || 0);
        });
        if (!entries.length) {
            return htmlHead + '<div class="empty-state"><strong>Aucune entrée</strong>Les actions de votre équipe apparaîtront ici.</div>';
        }
        let html = htmlHead + '<div class="card"><div class="card-header"><div><h2>' + entries.length + ' entrée(s)</h2>'
            + '<p class="card-subtitle">Ordre chronologique, du plus récent au plus ancien</p></div></div>';
        html += '<div class="history-list">' + entries.map(function (entry) {
            const details = entry.details && typeof entry.details === 'object' ? entry.details : null;
            let detailHtml = '';
            if (details && (details.old !== undefined || details.new !== undefined)) {
                detailHtml = '<span class="history-moderation-detail"><strong>Ancien texte :</strong> '
                    + esc(details.old === null || details.old === undefined || details.old === '' ? '—' : details.old)
                    + '<br><strong>Nouveau texte :</strong> '
                    + esc(details.new === null || details.new === undefined || details.new === '' ? '—' : details.new)
                    + '</span>';
            }
            return '<div class="history-entry">'
                + '<span class="history-time">' + esc(fmtDateTime(entry.at)) + '</span>'
                + '<span class="history-author">' + esc(entry.author) + '<br><span class="tiny muted">' + esc(historyTypeLabel(entry.type)) + '</span></span>'
                + '<span class="history-message">' + esc(entry.message) + '</span>'
                + detailHtml
                + '</div>';
        }).join('') + '</div></div>';
        return html;
    }

    function renderStudentBilan() {
        let html = pageHeading(
            'Bilan — Équipe ' + num(App.state.me.team),
            'Résultats actualisés de votre équipe, consultables à tout moment et sans modification des données.',
            toolHelpButton('bilan')
        );
        const bilan = App.state.bilan;
        const teams = bilan && Array.isArray(bilan.teams) ? bilan.teams : [];
        const ownTeam = teams.find(function (team) {
            return Number(team.id) === Number(App.state.me.team);
        });
        if (!ownTeam) {
            html += '<div class="empty-state"><strong>Bilan indisponible</strong>Actualisez la page.</div>';
            return html;
        }

        // Une seule colonne et une seule carte : le serveur n'envoie que
        // l'équipe de l'étudiant, jamais les autres équipes.
        html += bilanTableHtml([ownTeam]);
        html += bilanTeamCardsHtml([ownTeam]);
        return html;
    }

    // -----------------------------------------------------------------------
    // Modales
    // -----------------------------------------------------------------------
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.hidden = false;
        const first = modal.querySelector('input:not([type="hidden"]), textarea, select, button');
        if (first && typeof first.focus === 'function') {
            window.setTimeout(function () { first.focus(); }, 20);
        }
    }

    function closeModal(id) {
        if (id === 'ui-modal') {
            closeUiDialog(null);
            return;
        }
        const modal = document.getElementById(id);
        if (modal) modal.hidden = true;
    }

    function openClientFeedbackModal(story, feedback) {
        const panel = document.getElementById('client-feedback-panel');
        const storyEl = document.getElementById('client-feedback-story');
        const responsibleEl = document.getElementById('client-feedback-responsible');
        const messageEl = document.getElementById('client-feedback-message');
        const developerEl = document.getElementById('client-feedback-developer');
        if (!panel || !storyEl || !responsibleEl || !messageEl || !developerEl) return;

        const tone = ['success', 'warning', 'danger'].includes(feedback.tone) ? feedback.tone : 'success';
        panel.dataset.variant = tone;
        messageEl.dataset.tone = tone;
        storyEl.textContent = story.id + ' — ' + story.titre;
        responsibleEl.textContent = feedback.client_name || 'Responsable client';
        messageEl.textContent = feedback.message || '';
        developerEl.textContent = feedback.developer || '';
        openModal('client-feedback-modal');
    }

    async function ensureClientFeedbackConsulted(storyId) {
        const story = storyById(myTeam(), storyId);
        if (!story || story.etat !== 'a_valider' || story.client_feedback_consulted) return true;
        await showAlert(
            'Avant de prendre votre décision, veuillez consulter la réponse du client en cliquant sur le bouton À valider. Vous pourrez ensuite prendre votre décision en tenant compte de ses observations.',
            'Consultation obligatoire',
            'warning'
        );
        return false;
    }

    async function clientRefusalTooltip(story) {
        if (!story || story.client_feedback_position !== 'negative') return '';
        const response = await apiAction('consult_client_feedback', { story_id: story.id }, { noPoll: true });
        const feedback = response && response.feedback;
        if (!feedback || feedback.position !== 'negative') return '';
        const clientName = String(feedback.client_name || '').trim();
        const reason = String(feedback.message || '').trim();
        if (!clientName || !reason) return '';
        return 'Nom du client : ' + clientName + '\nRaison du refus du client : ' + reason;
    }

    function helpList(items) {
        return '<ul>' + (items || []).map(function (item) {
            return '<li>' + esc(item) + '</li>';
        }).join('') + '</ul>';
    }

    function openToolHelp(tool) {
        const help = TOOL_HELP[tool];
        const modal = document.getElementById('tool-help-modal');
        const title = document.getElementById('tool-help-modal-title');
        const content = document.getElementById('tool-help-content');
        if (!help || !modal || !title || !content) return;

        const role = App.state && App.state.me ? App.state.me.role : null;
        const roleText = help.byRole && help.byRole[role] ? help.byRole[role] : '';

        let topExtra = '';
        if (help.simulation) {
            topExtra += '<section><h3>Un Kanban pédagogique et simulé</h3><p>' + esc(help.simulation.intro) + '</p>'
                + helpList(help.simulation.facts) + '</section>';
        }

        let details = '';
        if (help.fields) {
            details += '<section><h3>Renseigner chaque champ</h3><ul>'
                + help.fields.map(function (field) {
                    return '<li><strong>' + esc(field.label) + ' :</strong> ' + esc(field.text) + '</li>';
                }).join('')
                + '</ul></section>';
        }
        if (help.indicators) {
            details += '<section><h3>Indicateurs, calculs et interprétation</h3>'
                + (help.indicatorsIntro ? '<p>' + esc(help.indicatorsIntro) + '</p>' : '')
                + '<ul>'
                + help.indicators.map(function (item) {
                    return '<li><strong>' + esc(item.label) + ' :</strong> ' + esc(item.text) + '</li>';
                }).join('')
                + '</ul></section>';
        }
        if (help.statistics) {
            details += '<section><h3>Statistiques des décisions client</h3><p>' + esc(help.statistics) + '</p></section>';
        }
        if (help.developerDuties) {
            details += '<section><h3>Responsabilités du Developer</h3>' + helpList(help.developerDuties) + '</section>';
        }
        if (help.blocked) {
            details += '<section><h3>Quand une tâche est-elle bloquée ?</h3><p>' + esc(help.blocked.intro) + '</p>'
                + '<h4>Distinguer les états</h4>' + helpList(help.blocked.states)
                + '<h4>Exemples de blocage réel</h4>' + helpList(help.blocked.examples)
                + '<p>' + esc(help.blocked.notBlocked) + '</p>'
                + '<h4>Que faire lorsqu’une tâche est bloquée ?</h4>' + helpList(help.blocked.actions)
                + '</section>';
        }
        if (help.acceptance) {
            details += '<section><h3>Vérifier la Definition of Done avant de valider</h3><p>' + esc(help.acceptance.intro) + '</p>'
                + '<h4>Questions à se poser</h4>' + helpList(help.acceptance.questions);
            if (help.acceptance.showDod && App.state && Array.isArray(App.state.definition_of_done)) {
                details += '<div class="help-callout"><strong>Definition of Done actuelle :</strong>'
                    + helpList(App.state.definition_of_done) + '</div>';
            }
            details += '<h4>Exemples dans WorkFlow IT</h4><ul>'
                + (help.acceptance.examples || []).map(function (example) {
                    return '<li><strong>' + esc(example.title) + ' :</strong> ' + esc(example.text) + '</li>';
                }).join('')
                + '</ul><p>' + esc(help.acceptance.reminder) + '</p></section>';
        }

        title.textContent = 'Aide — ' + help.title;
        content.innerHTML = '<section><h3>À quoi sert cet outil ?</h3><p>' + esc(help.purpose) + '</p></section>'
            + '<section><h3>Qui utilise cet outil ?</h3><p>' + esc(help.roles) + '</p>'
            + (roleText ? '<div class="help-callout"><strong>Votre rôle (' + esc(roleLabel(role)) + ') :</strong> ' + esc(roleText) + '</div>' : '')
            + '</section>'
            + topExtra
            + '<section><h3>Comment l’utiliser ?</h3>' + helpList(help.steps) + '</section>'
            + details
            + '<section><h3>Règles à respecter</h3>' + helpList(help.rules) + '</section>'
            + '<section class="help-tips"><h3>Exemple concret</h3><p>' + esc(help.example) + '</p></section>';
        openModal('tool-help-modal');
    }

    function closeUiDialog(value) {
        const modal = document.getElementById('ui-modal');
        const state = uiDialogState;
        uiDialogState = null;
        if (state && state.inputHandler) {
            const input = document.getElementById('ui-modal-input');
            if (input) input.removeEventListener('input', state.inputHandler);
        }
        if (modal) modal.hidden = true;
        if (state && typeof state.resolve === 'function') {
            state.resolve(value);
        }
    }

    function uiDialogElements() {
        return {
            modal: document.getElementById('ui-modal'),
            panel: document.getElementById('ui-modal-panel'),
            kicker: document.getElementById('ui-modal-kicker'),
            title: document.getElementById('ui-modal-title'),
            message: document.getElementById('ui-modal-message'),
            field: document.getElementById('ui-modal-field'),
            label: document.getElementById('ui-modal-label'),
            input: document.getElementById('ui-modal-input'),
            hint: document.getElementById('ui-modal-hint'),
            error: document.getElementById('ui-modal-error'),
            cancel: document.getElementById('ui-modal-cancel'),
            confirm: document.getElementById('ui-modal-confirm'),
        };
    }

    function setUiDialogError(message) {
        const error = document.getElementById('ui-modal-error');
        if (!error) return;
        error.textContent = message || '';
        error.hidden = !message;
    }

    function openUiDialog(options) {
        const opts = options || {};
        const els = uiDialogElements();
        const mode = opts.mode || 'confirm';
        if (!els.modal || !els.panel || !els.title || !els.message || !els.field
            || !els.input || !els.cancel || !els.confirm) {
            return Promise.resolve(null);
        }

        if (uiDialogState) closeUiDialog(null);

        return new Promise(function (resolve) {
            const variant = opts.variant || (mode === 'alert' ? 'info' : 'primary');
            const buttonVariant = {
                danger: 'danger',
                warning: 'warning',
                success: 'success',
                error: 'primary',
                info: 'primary',
                primary: 'primary',
            }[variant] || 'primary';
            const kicker = opts.kicker || {
                alert: 'Information',
                confirm: 'Confirmation',
                prompt: 'Saisie',
            }[mode] || 'Confirmation';
            const title = opts.title || {
                alert: 'Information',
                confirm: 'Confirmer l’action',
                prompt: 'Saisir une valeur',
            }[mode] || 'Confirmation';
            const confirmLabel = opts.confirmLabel || (mode === 'alert' ? 'Fermer' : 'Confirmer');

            els.panel.setAttribute('role', mode === 'alert' ? 'alertdialog' : 'dialog');
            els.panel.dataset.variant = variant;
            els.kicker.textContent = kicker;
            els.title.textContent = title;
            els.message.textContent = opts.message || '';
            els.message.style.whiteSpace = 'pre-line';
            els.field.hidden = mode !== 'prompt';
            els.cancel.hidden = mode === 'alert';
            els.confirm.textContent = confirmLabel;
            els.confirm.className = 'btn btn-' + buttonVariant;
            setUiDialogError('');

            els.input.value = opts.value || '';
            els.input.placeholder = opts.placeholder || '';
            els.input.maxLength = Number(opts.maxLength || opts.max || 500);
            els.input.setAttribute('aria-invalid', 'false');
            els.label.textContent = opts.label || 'Saisie';
            if (els.hint) {
                els.hint.textContent = opts.hint || '';
                els.hint.hidden = !opts.hint;
            }
            const tooltip = String(opts.tooltip || '').trim();
            [els.input, els.label, els.field].forEach(function (element) {
                if (!element) return;
                if (tooltip) {
                    element.title = tooltip;
                } else {
                    element.removeAttribute('title');
                }
            });

            const inputHandler = (mode === 'prompt' && opts.exact) ? function () {
                els.confirm.disabled = (els.input.value !== opts.exact);
            } : null;
            if (inputHandler) {
                els.confirm.disabled = true;
                els.input.addEventListener('input', inputHandler);
            } else {
                els.confirm.disabled = false;
            }
            uiDialogState = {
                mode: mode,
                options: opts,
                resolve: resolve,
                inputHandler: inputHandler,
            };
            els.modal.hidden = false;

            window.setTimeout(function () {
                if (!uiDialogState || uiDialogState.resolve !== resolve) return;
                const target = mode === 'prompt' ? els.input : els.confirm;
                if (target && typeof target.focus === 'function') {
                    target.focus();
                    if (mode === 'prompt' && typeof target.select === 'function') target.select();
                }
            }, 20);
        });
    }

    function showAlert(message, title, variant) {
        return openUiDialog({
            mode: 'alert',
            title: title || 'Information',
            message: message || '',
            confirmLabel: 'Fermer',
            variant: variant || 'info',
        });
    }

    function showConfirm(options) {
        return openUiDialog(Object.assign({}, options || {}, { mode: 'confirm' })).then(function (value) {
            return value === true;
        });
    }

    function showPrompt(options) {
        return openUiDialog(Object.assign({}, options || {}, { mode: 'prompt' })).then(function (value) {
            return typeof value === 'string' ? value : null;
        });
    }

    function setPasswordError(message) {
        const error = document.getElementById('password-error');
        if (!error) return;
        error.textContent = message || '';
        error.hidden = !message;
    }

    function submitUiDialog() {
        const state = uiDialogState;
        if (!state) return;
        const opts = state.options || {};
        if (state.mode === 'alert' || state.mode === 'confirm') {
            closeUiDialog(true);
            return;
        }

        const input = document.getElementById('ui-modal-input');
        const value = input ? input.value.trim() : '';
        const min = Number(opts.min || 0);
        const max = Number(opts.max || 0);
        let error = '';
        if (opts.required && !value) {
            error = 'Une valeur est requise.';
        } else if (min > 0 && value.length < min) {
            error = 'Saisissez au moins ' + min + ' caractères.';
        } else if (max > 0 && value.length > max) {
            error = 'La saisie ne doit pas dépasser ' + max + ' caractères.';
        } else if (typeof opts.validate === 'function') {
            error = opts.validate(value) || '';
        }

        if (error) {
            setUiDialogError(error);
            if (input) {
                input.setAttribute('aria-invalid', 'true');
                input.focus();
            }
            return;
        }
        closeUiDialog(value);
    }

    function moderationPayload(button) {
        const payload = {
            type: button.dataset.type,
            team: Number(button.dataset.team),
            content_id: button.dataset.content,
            field: button.dataset.field,
        };
        if (button.dataset.index !== undefined && button.dataset.index !== '') {
            payload.index = Number(button.dataset.index);
        }
        return payload;
    }

    function openModerationModal(button) {
        const title = document.getElementById('moderation-modal-title');
        const label = document.getElementById('moderation-label');
        const text = document.getElementById('moderation-text');
        const type = document.getElementById('moderation-type');
        const team = document.getElementById('moderation-team');
        const content = document.getElementById('moderation-content');
        const field = document.getElementById('moderation-field');
        const index = document.getElementById('moderation-index');
        if (!title || !label || !text || !type || !team || !content || !field || !index) return;

        title.textContent = 'Modifier — ' + (button.dataset.label || 'contenu');
        label.textContent = button.dataset.label || 'Contenu';
        text.value = button.dataset.current || '';
        text.maxLength = 500;
        type.value = button.dataset.type || '';
        team.value = button.dataset.team || '0';
        content.value = button.dataset.content || '';
        field.value = button.dataset.field || '';
        index.value = button.dataset.index || '';
        openModal('moderation-modal');
        window.setTimeout(function () { text.focus(); text.select(); }, 30);
    }

    function syncTaskBlockingField() {
        const state = document.getElementById('task-state');
        const field = document.getElementById('task-blocking-field');
        const input = document.getElementById('task-blocking');
        if (!state || !field || !input) return;
        const isBlocked = state.value === 'bloque';
        field.hidden = !isBlocked;
        input.required = isBlocked;
        if (!isBlocked) input.value = '';

        const submit = document.querySelector('#task-form button[type="submit"]');
        const taskId = (document.getElementById('task-id') || {}).value || '';
        if (!submit) return;
        if (state.value === 'termine' && taskId) {
            if (App.challengePassedTasks.has(taskId)) {
                submit.disabled = false;
            } else {
                submit.disabled = true;
                openTaskChallenge(taskId);
            }
        } else {
            submit.disabled = false;
        }
    }

    function openTaskModal(mode, taskId) {
        if (!userCanWrite() || myRole() !== 'DEVELOPER') return;
        const team = myTeam();
        if (!team) return;

        const stories = team.stories.filter(function (story) {
            if (!story.selectionnee || story.etat === 'validee') return false;
            // Une User Story déjà associée à une tâche Kanban (y compris
            // archivée ou terminée) n'est plus proposée à la création.
            if (mode === 'create') {
                const hasTask = (team.tasks || []).some(function (task) {
                    return task.us_id === story.id;
                });
                if (hasTask) return false;
            }
            return true;
        });
        if (mode === 'create' && stories.length === 0) {
            flash('Sélectionnez d’abord une User Story dans le Sprint.', 'error');
            return;
        }

        const title = document.getElementById('task-modal-title');
        const form = document.getElementById('task-form');
        const idField = document.getElementById('task-id');
        const storySelect = document.getElementById('task-story');
        const titleField = document.getElementById('task-title');
        const description = document.getElementById('task-description');
        const hoursField = document.getElementById('task-hours');
        const assignee = document.getElementById('task-assignee');
        const stateField = document.getElementById('task-state');
        const risksField = document.getElementById('task-risks');
        const blocking = document.getElementById('task-blocking');
        const helpButton = document.getElementById('task-help-button');

        if (!form || !storySelect || !titleField) return;
        form.reset();
        idField.value = '';
        storySelect.innerHTML = stories.map(function (story) {
            return '<option value="' + esc(story.id) + '">' + esc(story.id) + ' — ' + esc(story.titre) + ' (' + esc(story.estimation) + ' SP)</option>';
        }).join('');
        const developers = team.members.filter(function (m) { return m.role === 'DEVELOPER'; });
        assignee.innerHTML = developers.map(function (m) {
            return '<option value="' + esc(m.login) + '">' + esc(m.login) + ' — ' + esc(roleLabel(m.role)) + '</option>';
        }).join('');
        if (developers.length) {
            const me = terminalLogin();
            assignee.value = developers.some(function (m) { return m.login === me; }) ? me : developers[0].login;
        }

        const updateTaskRisks = function () {
            const story = storyById(team, storySelect.value);
            if (risksField) risksField.textContent = (story && story.risques) ? story.risques : 'Aucun risque majeur identifié à ce stade.';
        };

        const descriptionField = description ? description.closest('.field') : null;
        const descriptionLabel = document.querySelector('label[for="task-description"]');
        const updateTaskStoryHelp = function () {
            const story = storyById(team, storySelect.value);
            const criteria = story && Array.isArray(story.criteres) ? story.criteres : [];
            const help = story
                ? 'Description et critères d’acceptation de ' + story.id + '\n\n'
                    + String(story.description || '') + '\n\n'
                    + 'Critères d’acceptation :\n'
                    + (criteria.length ? criteria.map(function (c) { return '• ' + c; }).join('\n') : 'Aucun critère renseigné.')
                : '';
            [description, descriptionLabel, descriptionField].forEach(function (element) {
                if (!element) return;
                if (help) {
                    element.title = help;
                } else {
                    element.removeAttribute('title');
                }
            });
        };
        const updateTaskStoryContext = function () {
            updateTaskRisks();
            updateTaskStoryHelp();
        };
        storySelect.onchange = updateTaskStoryContext;

        const stateOptionsCreate = '<option value="a_faire">À faire</option>'
            + '<option value="en_cours">En cours</option>'
            + '<option value="bloque">Bloqué</option>';
        const stateOptionsEdit = stateOptionsCreate
            + '<option value="termine">Terminé</option>';

        if (mode === 'edit') {
            const task = taskById(team, taskId);
            if (!task || task.archived || task.etat === 'termine') return;
            if (title) title.textContent = 'Modifier ' + task.id;
            if (helpButton) helpButton.hidden = true;
            idField.value = task.id;
            storySelect.value = task.us_id;
            storySelect.disabled = true;
            updateTaskStoryContext();
            titleField.value = task.titre;
            description.value = task.description || '';
            hoursField.value = task.heures;
            assignee.value = task.assigne && developers.some(function (m) {
                return m.login === task.assignee;
            }) ? task.assigne : (developers.length ? developers[0].login : '');
            const me = terminalLogin();
            if (developers.some(function (m) { return m.login === me; })) {
                assignee.value = me;
            }
            stateField.innerHTML = stateOptionsEdit;
            stateField.value = task.etat;
            blocking.value = task.blocage || '';
        } else {
            if (title) title.textContent = 'Nouvelle tâche';
            if (helpButton) helpButton.hidden = false;
            storySelect.disabled = false;
            updateTaskStoryContext();
            hoursField.value = '2';
            stateField.innerHTML = stateOptionsCreate;
            stateField.value = 'a_faire';
            blocking.value = '';
        }
        syncTaskBlockingField();
        openModal('task-modal');
    }

    function terminalLogin() {
        return (App.state && App.state.me && App.state.me.login) ? String(App.state.me.login) : 'utilisateur';
    }

    function terminalPath() {
        return '/home/' + terminalLogin();
    }

    function terminalPrint(text, className) {
        const output = document.getElementById('challenge-terminal-output');
        if (!output) return;
        const line = document.createElement('div');
        line.className = 'terminal-line' + (className ? ' ' + className : '');
        line.textContent = text;
        output.appendChild(line);
        output.scrollTop = output.scrollHeight;
    }

    function terminalPrompt() {
        return terminalLogin() + '@workflow-it:' + terminalPath() + '$';
    }

    function terminalUpdatePrompt() {
        const promptEl = document.getElementById('challenge-terminal-prompt');
        if (promptEl) promptEl.textContent = terminalPrompt();
    }

    function terminalPrintCommand(command) {
        terminalPrint(terminalPrompt() + ' ' + command, 'terminal-command');
    }

    function terminalResetContext() {
        App.activeChallenge = null;
        App.terminalChallengeId = null;
        App.terminalLastAt = 0;
        App.terminalPrintedLevel = 0;
        App.terminalBusy = false;
        App.terminalHistory = [];
        App.terminalHistoryIndex = 0;
        if (App.terminalCooldownTimer) {
            window.clearTimeout(App.terminalCooldownTimer);
            App.terminalCooldownTimer = null;
        }

        const output = document.getElementById('challenge-terminal-output');
        if (output) output.textContent = '';
        const input = document.getElementById('challenge-terminal-input');
        if (input) {
            input.value = '';
            input.disabled = true;
        }
        const errorEl = document.getElementById('challenge-error');
        if (errorEl) {
            errorEl.hidden = true;
            errorEl.textContent = '';
        }
        const countdownEl = document.getElementById('challenge-countdown');
        if (countdownEl) countdownEl.textContent = '';
    }

    function closeChallengeModal() {
        terminalResetContext();
        closeModal('challenge-modal');
    }

    function terminalPrintIndices(from, to) {
        const challenge = App.activeChallenge;
        if (!challenge) return;
        const indices = Array.isArray(challenge.indices) ? challenge.indices : [];
        const start = Math.max(0, Number(from) || 0);
        const end = Math.min(indices.length, Math.max(start, Number(to) || 0));
        for (let i = start; i < end; i++) {
            terminalPrint('[Indice ' + (i + 1) + ']\n' + String(indices[i]), 'terminal-hint');
        }
        App.terminalPrintedLevel = Math.max(App.terminalPrintedLevel, end);
    }

    function terminalInit(challenge) {
        const output = document.getElementById('challenge-terminal-output');
        const input = document.getElementById('challenge-terminal-input');
        if (!output || !challenge) return;

        terminalUpdatePrompt();
        if (App.terminalChallengeId !== challenge.challenge_id) {
            App.terminalChallengeId = challenge.challenge_id;
            App.terminalLastAt = 0;
            App.terminalPrintedLevel = 0;
            App.terminalBusy = false;
            App.terminalHistory = [];
            App.terminalHistoryIndex = 0;
            output.textContent = '';
            if (input) input.value = '';
            terminalPrint('Terminal GNU/Linux simulé — ' + terminalPath(), 'terminal-info');
            terminalPrint('Saisissez help pour afficher les commandes disponibles.', 'terminal-info');
        }

        const level = Math.max(0, Math.min(3, Number(challenge.index_level) || 0));
        if (level > App.terminalPrintedLevel) {
            terminalPrintIndices(App.terminalPrintedLevel, level);
        }
        if (input) input.disabled = false;
    }

    function renderChallengeModal() {
        const challenge = App.activeChallenge;
        if (!challenge) return;
        const type = (challenge && challenge.type) || 'quiz';
        const storyEl = document.getElementById('challenge-story');
        const instructionEl = document.getElementById('challenge-instruction');
        const questionEl = document.getElementById('challenge-question');
        const optionsEl = document.getElementById('challenge-options');
        const terminalEl = document.getElementById('challenge-terminal');
        const submitEl = document.getElementById('challenge-submit');
        const errorEl = document.getElementById('challenge-error');
        const explanationEl = document.getElementById('challenge-explanation');
        if (storyEl) storyEl.textContent = 'User Story associée : ' + challenge.story_id;
        if (instructionEl) instructionEl.textContent = (type === 'terminal') ? (challenge.instruction || '') : '';
        const titleEl = document.getElementById('challenge-modal-title');
        if (titleEl) titleEl.textContent = (type === 'terminal') ? 'Challenge avant « Prête à valider »' : 'Challenge avant « Terminé »';
        const challengeHelpButton = document.getElementById('challenge-help-button');
        if (challengeHelpButton) challengeHelpButton.hidden = (type !== 'terminal');
        if (type === 'terminal') {
            if (terminalEl) terminalEl.hidden = false;
            terminalInit(challenge);
            if (questionEl) questionEl.hidden = true;
            if (optionsEl) optionsEl.hidden = true;
            if (submitEl) submitEl.hidden = true;
        } else {
            if (terminalEl) terminalEl.hidden = true;
            if (questionEl) questionEl.hidden = false;
            if (questionEl) questionEl.textContent = challenge.question;
            if (optionsEl) {
                optionsEl.hidden = false;
                optionsEl.innerHTML = (challenge.options || []).map(function (option, index) {
                    return '<label class="radio-option"><input type="radio" name="challenge-option" value="' + esc(index) + '"> '
                        + esc(option) + '</label>';
                }).join('');
            }
            if (submitEl) submitEl.hidden = false;
        }
        if (errorEl) { errorEl.hidden = true; errorEl.textContent = ''; }
        if (explanationEl) { explanationEl.hidden = true; explanationEl.textContent = ''; }
        updateChallengeCountdown();
    }

    async function openTaskChallenge(taskId) {
        const data = await apiAction('challenge_start', { task_id: taskId });
        if (!data) return false;
        App.activeChallenge = data;
        renderChallengeModal();
        openModal('challenge-modal');
        return true;
    }

    async function openTaskChallengeForStory(storyId) {
        const data = await apiAction('challenge_terminal_start', { story_id: storyId });
        if (!data) return false;
        // Le contexte de la modale ne doit jamais pouvoir provenir d'une autre US.
        if (data.type !== 'terminal' || String(data.story_id) !== String(storyId)) {
            flash('Le défi technique reçu ne correspond pas à la User Story sélectionnée.', 'error');
            return false;
        }
        App.activeChallenge = data;
        renderChallengeModal();
        openModal('challenge-modal');
        return true;
    }

    function updateChallengeCountdown() {
        const modal = document.getElementById('challenge-modal');
        if (!modal || modal.hidden || !App.activeChallenge) return;
        if (App.terminalCooldownTimer) {
            window.clearTimeout(App.terminalCooldownTimer);
            App.terminalCooldownTimer = null;
        }

        const countdownEl = document.getElementById('challenge-countdown');
        const errorEl = document.getElementById('challenge-error');
        const submitEl = document.getElementById('challenge-submit');
        const type = (App.activeChallenge && App.activeChallenge.type) || 'quiz';

        if (type === 'terminal') {
            const terminalInputEl = document.getElementById('challenge-terminal-input');
            if (terminalInputEl) terminalInputEl.disabled = false;
            if (submitEl) submitEl.hidden = true;

            const cooldown = Math.max(0, App.terminalLastAt + TERMINAL_COMMAND_DELAY_MS - nowMs());
            if (cooldown <= 0 && errorEl && errorEl.textContent.indexOf('Patientez encore') === 0) {
                errorEl.hidden = true;
                errorEl.textContent = '';
            }
            if (countdownEl) {
                countdownEl.textContent = cooldown > 0
                    ? 'Commande en attente : ' + Math.ceil(cooldown / 1000) + ' s. La saisie reste disponible.'
                    : 'Délai entre commandes : 2 s. La saisie reste disponible.';
            }
            if (cooldown > 0) {
                // Rafraîchit le décompte sans bloquer la saisie ni dupliquer
                // le mécanisme de limitation.
                App.terminalCooldownTimer = window.setTimeout(function () {
                    App.terminalCooldownTimer = null;
                    updateChallengeCountdown();
                }, Math.max(100, Math.min(500, cooldown)));
            }
            return;
        }

        if (submitEl) submitEl.hidden = false;
        const duration = Number(App.activeChallenge.duration) || 30;
        const remaining = Math.max(0, App.activeChallenge.started_at + duration - Math.floor(nowMs() / 1000));
        if (countdownEl) {
            countdownEl.textContent = remaining > 0
                ? 'Validation possible dans ' + remaining + ' s.'
                : 'Vous pouvez maintenant valider votre réponse.';
        }
        if (submitEl) {
            const selected = document.querySelector('input[name="challenge-option"]:checked');
            submitEl.disabled = remaining > 0 || !selected;
        }
    }

    async function submitTaskChallenge() {
        if (!App.activeChallenge) return;
        const type = (App.activeChallenge && App.activeChallenge.type) || 'quiz';
        if (type === 'terminal') {
            await submitTaskChallengeTerminal();
            return;
        }
        const selected = document.querySelector('input[name="challenge-option"]:checked');
        if (!selected) {
            const errorEl = document.getElementById('challenge-error');
            if (errorEl) { errorEl.hidden = false; errorEl.textContent = 'Sélectionnez une réponse.'; }
            return;
        }
        const result = await apiAction('challenge_answer', {
            challenge_id: App.activeChallenge.challenge_id,
            option: Number(selected.value),
        });
        if (!result) return;
        if (result.status === 'wrong') {
            const explanationEl = document.getElementById('challenge-explanation');
            if (explanationEl) {
                explanationEl.hidden = false;
                explanationEl.textContent = result.selected_explanation + ' ' + result.correct_explanation;
            }
            App.activeChallenge.started_at = result.started_at;
            App.activeChallenge.attempts = result.attempts;
            if (result.duration !== undefined) App.activeChallenge.duration = result.duration;
            updateChallengeCountdown();
            return;
        }
        if (result.status === 'success') {
            if (result.task_id) App.challengePassedTasks.add(result.task_id);
            const taskId = result.task_id;
            const taskModal = document.getElementById('task-modal');
            const isEditing = taskModal && !taskModal.hidden;
            closeChallengeModal();
            if (isEditing) {
                const idField = document.getElementById('task-id');
                if (idField && idField.value === taskId) {
                    const payload = {
                        task_id: taskId,
                        titre: document.getElementById('task-title').value,
                        description: document.getElementById('task-description').value,
                        us_id: document.getElementById('task-story').value,
                        heures: Number(document.getElementById('task-hours').value),
                        assigne: document.getElementById('task-assignee').value,
                        etat: 'termine',
                        blocage: document.getElementById('task-blocking').value,
                    };
                    const data = await apiAction('update_task', payload);
                    if (data) {
                        closeModal('task-modal');
                        const form = document.getElementById('task-form');
                        if (form) form.reset();
                        const storySelect = document.getElementById('task-story');
                        if (storySelect) storySelect.disabled = false;
                    }
                }
            } else {
                if (taskId) {
                    const select = document.querySelector('select[data-task="' + taskId + '"]');
                    if (select) select.value = 'termine';
                }
                await apiAction('move_task', { task_id: taskId, etat: 'termine', blocage: '' });
            }
            await pollNow();
        }
    }

    function terminalHandleLocal(command) {
        const challenge = App.activeChallenge;
        if (!challenge) return false;
        const login = terminalLogin();

        if (command === 'pwd') {
            terminalPrint(terminalPath());
            return true;
        }

        if (command === 'ls') {
            terminalPrint(TERMINAL_DIRS.join(' '));
            return true;
        }

        if (command === 'ls -l') {
            terminalPrint(TERMINAL_DIRS.map(function (dir) {
                return 'drwxr-xr-x  2 ' + login + ' developers 4096 ' + dir;
            }).join('\n'));
            return true;
        }

        if (command === 'ls -la') {
            const lines = [
                'drwxr-xr-x  8 ' + login + ' developers 4096 .',
                'drwxr-xr-x  3 root    root       4096 ..',
                '-rw-------  1 ' + login + ' developers  512 .env',
                '-rw-r--r--  1 ' + login + ' developers  220 .bashrc',
                '-rw-r--r--  1 ' + login + ' developers  807 .profile',
            ].concat(TERMINAL_DIRS.map(function (dir) {
                return 'drwxr-xr-x  2 ' + login + ' developers 4096 ' + dir;
            }));
            terminalPrint(lines.join('\n'));
            return true;
        }

        if (command === 'help') {
            const exploration = Array.isArray(challenge.exploration) && challenge.exploration.length
                ? challenge.exploration
                : ['pwd', 'ls', 'ls -l', 'ls -la'];
            const lines = ['Commandes disponibles :'].concat(exploration.map(String));
            if (Number(challenge.index_level || 0) >= 3) lines.push('solution');
            terminalPrint(lines.join('\n'));
            return true;
        }

        if (command === 'solution' && Number(challenge.index_level || 0) < 3) {
            terminalPrint('La commande solution est disponible après le troisième indice.', 'terminal-error');
            return true;
        }

        return false;
    }

    async function submitTaskChallengeTerminal() {
        if (!App.activeChallenge) return;
        const inputEl = document.getElementById('challenge-terminal-input');
        const errorEl = document.getElementById('challenge-error');
        const command = inputEl ? (inputEl.value || '').trim() : '';
        if (!command) {
            if (errorEl) { errorEl.hidden = false; errorEl.textContent = 'Saisissez une commande.'; }
            return;
        }
        if (App.terminalBusy) return;

        const cooldown = Math.max(0, App.terminalLastAt + TERMINAL_COMMAND_DELAY_MS - nowMs());
        if (cooldown > 0) {
            if (errorEl) {
                errorEl.hidden = false;
                errorEl.textContent = 'Patientez encore ' + Math.ceil(cooldown / 1000) + ' s avant la prochaine commande.';
            }
            updateChallengeCountdown();
            return;
        }

        if (errorEl) { errorEl.hidden = true; errorEl.textContent = ''; }
        App.terminalBusy = true;
        App.terminalLastAt = nowMs();
        App.terminalHistory.push(command);
        if (App.terminalHistory.length > 100) App.terminalHistory.shift();
        App.terminalHistoryIndex = App.terminalHistory.length;
        terminalPrintCommand(command);
        if (inputEl) inputEl.value = '';

        try {
            if (terminalHandleLocal(command)) {
                return;
            }

            const result = await apiAction('challenge_terminal_answer', {
                challenge_id: App.activeChallenge.challenge_id,
                command: command,
            }, {
                noPoll: true,
                onError: function (message) {
                    terminalPrint(message, 'terminal-error');
                },
            });
            if (!result) return;

            if (result.status === 'wrong') {
                terminalPrint('bash: ' + command + ': command not found', 'terminal-error');
                terminalPrint('Saisissez help pour afficher les commandes disponibles.', 'terminal-info');
                if (result.attempts !== undefined) App.activeChallenge.attempts = result.attempts;
                updateChallengeCountdown();
                return;
            }

            if (result.status === 'hint' || result.status === 'perhaps') {
                const previousLevel = Number(result.from_level !== undefined ? result.from_level : App.terminalPrintedLevel) || 0;
                const newLevel = Math.max(previousLevel, Number(result.index_level) || 0);
                App.activeChallenge.index_level = newLevel;
                if (Array.isArray(result.indices)) App.activeChallenge.indices = result.indices;
                terminalPrintIndices(previousLevel, newLevel);
                if (result.message) terminalPrint(result.message, 'terminal-info');
                if (result.attempts !== undefined) App.activeChallenge.attempts = result.attempts;
                updateChallengeCountdown();
                return;
            }

            if (result.status === 'locked') {
                terminalPrint(result.message || 'La commande solution est disponible après le troisième indice.', 'terminal-error');
                updateChallengeCountdown();
                return;
            }

            if (result.status === 'solution') {
                terminalPrint('Commande attendue :\n' + String(result.command || ''), 'terminal-success');
                updateChallengeCountdown();
                return;
            }

            if (result.status === 'success') {
                const storyId = result.story_id;
                terminalPrint('Commande validée. La User Story peut être déclarée prête à valider.', 'terminal-success');
                closeChallengeModal();
                if (storyId) {
                    await apiAction('declare_ready', { story_id: storyId }, { noPoll: true });
                }
                await pollNow();
            }
        } finally {
            App.terminalBusy = false;
            updateChallengeCountdown();
            const modal = document.getElementById('challenge-modal');
            if (modal && !modal.hidden && inputEl && !inputEl.disabled) inputEl.focus();
        }
    }

    // -----------------------------------------------------------------------
    // Traitement des actions
    // -----------------------------------------------------------------------
    async function handleClick(event) {
        // Seuls les boutons de navigation portent data-view. Le conteneur
        // #app-shell possède aussi cet attribut pour l'état initial : il ne
        // doit jamais être interprété comme un clic de navigation.
        const viewButton = event.target.closest('button[data-view]');
        if (viewButton) {
            switchView(viewButton.dataset.view);
            return;
        }

        const closeButton = event.target.closest('[data-close-modal]');
        if (closeButton) {
            if (closeButton.dataset.closeModal === 'challenge-modal') {
                closeChallengeModal();
            } else {
                closeModal(closeButton.dataset.closeModal);
            }
            return;
        }

        const button = event.target.closest('[data-action]');
        if (!button) return;
        const action = button.dataset.action;

        if (action === 'open-help') {
            openModal('help-modal');
            return;
        }
        if (action === 'tool-help') {
            openToolHelp(button.dataset.tool);
            return;
        }
        if (action === 'task-help') {
            openToolHelp('taskForm');
            return;
        }
        if (action === 'kanban-stats') {
            openKanbanStats(button.dataset.team || null);
            return;
        }
        if (action === 'challenge-help') {
            openToolHelp('terminalValidation');
            return;
        }
        if (action === 'change-password') {
            const form = document.getElementById('password-form');
            if (!form) return;
            form.reset();
            setPasswordError('');
            openModal('password-modal');
            return;
        }
        if (action === 'teacher-reset-password') {
            if (!isTeacher) return;
            let login = (button.dataset.login || '').trim().toLowerCase();
            if (!login) {
                login = await showPrompt({
                    title: 'Réinitialiser un mot de passe',
                    message: 'Saisissez le login de l’étudiant concerné. Son mot de passe sera remis à sa date de naissance initiale, au format JJMMAAAA.',
                    label: 'Login de l’étudiant',
                    placeholder: 'identifiant',
                    hint: 'Le mot de passe et la date de naissance ne seront jamais affichés.',
                    min: 1,
                    max: 30,
                    required: true,
                    confirmLabel: 'Continuer',
                    variant: 'warning',
                    validate: function (value) {
                        return /^[a-z]{1,30}$/.test(value.trim().toLowerCase())
                            ? ''
                            : 'Utilisez uniquement des lettres minuscules (1 à 30 caractères).';
                    },
                });
                if (login === null) return;
                login = login.trim().toLowerCase();
            }
            const confirmed = await showConfirm({
                title: 'Confirmer la réinitialisation',
                message: 'Réinitialiser le mot de passe de « ' + login + ' » à sa valeur initiale ? L’étudiant pourra le changer après sa prochaine connexion.',
                confirmLabel: 'Réinitialiser',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('teacher_reset_password', { login: login });
            return;
        }
        if (action === 'task-new') {
            openTaskModal('create');
            return;
        }
        if (action === 'task-edit') {
            openTaskModal('edit', button.dataset.task);
            return;
        }
        if (action === 'task-archive') {
            const confirmed = await showConfirm({
                title: 'Archiver la tâche',
                message: 'Archiver cette tâche ? Elle ne sera plus visible dans le Kanban actif, mais restera tracée dans l’historique.',
                confirmLabel: 'Archiver',
                variant: 'danger',
            });
            if (!confirmed) return;

            const teamId = button.dataset.team || null;
            const team = teamId ? teamById(teamId) : myTeam();
            const task = taskById(team, button.dataset.task);
            const hours = task ? num(task.heures) : 0;
            const phrase = 'JE CONFIRME L’ARCHIVAGE DE CETTE TÂCHE';
            let impact = 'Conséquences sur le bilan de l’équipe :\n';
            impact += '- Tâches actives : -1\n';
            impact += '- Charge totale : -' + hours + ' h\n';
            if (task && task.etat === 'termine') {
                impact += '- Tâches terminées : -1\n';
                impact += '- Heures terminées : -' + hours + ' h\n';
            } else {
                impact += '- Reste à faire : -' + hours + ' h\n';
                if (task && task.etat === 'bloque') {
                    impact += '- Tâches bloquées : -1\n';
                }
            }
            impact += 'La tâche disparaîtra du Kanban actif et ne pourra plus être modifiée ni réactivée. Ses données resteront conservées dans les archives et l’historique.';

            const second = await showPrompt({
                title: 'Confirmation renforcée de l’archivage',
                message: impact,
                label: 'Phrase de confirmation',
                placeholder: phrase,
                hint: 'Saisissez exactement : ' + phrase,
                min: phrase.length,
                max: phrase.length + 10,
                required: true,
                exact: phrase,
                validate: function (value) {
                    return value === phrase ? '' : 'La phrase saisie est incorrecte.';
                },
                confirmLabel: 'Confirmer définitivement l’archivage',
                cancelLabel: 'Annuler',
                variant: 'danger',
            });
            if (second === null || second !== phrase) return;
            const payload = { task_id: button.dataset.task, confirmation: phrase };
            if (teamId) payload.team = teamId;
            await apiAction('archive_task', payload);
            return;
        }
        if (action === 'report-blockage') {
            const reason = await showPrompt({
                title: 'Signaler un blocage',
                message: 'Décrivez l’obstacle réel qui empêche de poursuivre. Une tâche difficile ou longue n’est pas forcément bloquée. Cette explication sera visible par l’équipe et le formateur.',
                label: 'Explication du blocage',
                placeholder: 'Dépendance, problème technique, information manquante…',
                hint: 'Exemple : information indispensable manquante, dépendance technique, environnement indisponible. 3 à 300 caractères.',
                min: 3,
                max: 300,
                required: true,
                confirmLabel: 'Signaler le blocage',
                variant: 'warning',
            });
            if (reason === null) return;
            await apiAction('report_blockage', {
                task_id: button.dataset.task,
                blocage: reason,
            });
            return;
        }
        if (action === 'respond-event') {
            const optionLabel = (button.textContent || '').trim() || 'cette décision';
            const confirmed = await showConfirm({
                title: 'Confirmer la décision',
                message: 'Appliquer « ' + optionLabel + ' » ? La conséquence de ce choix sera immédiatement enregistrée pour votre équipe.',
                confirmLabel: 'Appliquer la décision',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('respond_event', {
                event_id: button.dataset.event,
                option: Number(button.dataset.option),
            });
            return;
        }
        if (action === 'moderate-edit') {
            openModerationModal(button);
            return;
        }
        if (action === 'moderate-delete') {
            const label = button.dataset.label || 'ce contenu';
            const confirmed = await showConfirm({
                title: 'Supprimer le contenu',
                message: 'Supprimer ' + label + ' ? Le contenu ne sera plus visible, mais l’historique conservera une trace.',
                confirmLabel: 'Supprimer',
                variant: 'danger',
            });
            if (!confirmed) return;

            const phrase = 'JE CONFIRME LA SUPPRESSION DE CE CONTENU';
            const second = await showPrompt({
                title: 'Confirmation renforcée de la suppression',
                message: 'Supprimer ' + label + ' ? Cette action retirera le contenu de la vue de l’équipe sans possibilité de réplique immédiate : l’historique conservera une trace.',
                label: 'Phrase de confirmation',
                placeholder: phrase,
                hint: 'Saisissez exactement : ' + phrase,
                min: phrase.length,
                max: phrase.length + 10,
                required: true,
                exact: phrase,
                validate: function (value) {
                    return value === phrase ? '' : 'La phrase saisie est incorrecte.';
                },
                confirmLabel: 'Confirmer définitivement la suppression',
                cancelLabel: 'Annuler',
                variant: 'danger',
            });
            if (second === null || second !== phrase) return;
            const payload = moderationPayload(button);
            payload.confirmation = phrase;
            await apiAction('moderate_delete', payload);
            return;
        }
        if (action === 'story-move') {
            const direction = button.dataset.dir === 'up' ? 'Monter' : 'Descendre';
            const confirmed = await showConfirm({
                title: direction + ' la User Story',
                message: direction + ' la User Story ' + button.dataset.story + ' dans le Product Backlog ?',
                confirmLabel: 'Confirmer',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('move_story', { story_id: button.dataset.story, direction: button.dataset.dir });
            return;
        }
        if (action === 'toggle-sprint') {
            const selecting = button.dataset.selected === '1';
            const confirmed = await showConfirm({
                title: selecting ? 'Sélectionner la User Story' : 'Retirer la User Story',
                message: selecting
                    ? 'Ajouter cette User Story au Sprint ? Elle sera traitée pendant le Sprint par l’équipe.'
                    : 'Retirer cette User Story du Sprint ? Elle ne sera plus sélectionnée pour le Sprint en cours.',
                confirmLabel: selecting ? 'Sélectionner' : 'Retirer',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('toggle_sprint', {
                story_id: button.dataset.story,
                selected: selecting,
            });
            return;
        }
        if (action === 'declare-ready') {
            const storyId = button.dataset.story;
            const team = myTeam();
            const doneTasks = (team && team.tasks ? team.tasks : []).filter(function (t) {
                return t.us_id === storyId && t.etat === 'termine' && !t.archived;
            });
            const handlers = new Set();
            doneTasks.forEach(function (t) {
                let handler = null;
                ['updated_by', 'assigne', 'created_by'].forEach(function (field) {
                    if (handler) return;
                    const l = t[field];
                    if (l && l !== 'systeme') {
                        const member = (team.members || []).find(function (m) { return m.login === l; });
                        if (member && member.role === 'DEVELOPER') handler = l;
                    }
                });
                if (handler) handlers.add(handler);
            });
            const me = (App.state && App.state.me && App.state.me.login) ? App.state.me.login : '';
            if (handlers.size > 0 && !handlers.has(me)) {
                showAlert('Vous n’êtes pas l’auteur de la tâche rattachée à cette User Story. Seul le développeur qui a traité la tâche peut la déclarer prête à valider.', 'Accès refusé', 'warning');
                return;
            }
            const started = await openTaskChallengeForStory(storyId);
            if (!started) return;
        }
        if (action === 'export-bilan') {
            const confirmed = await showConfirm({
                title: 'Exporter le bilan',
                message: 'Télécharger le bilan et l’historique au format JSON ? Le fichier peut être vérifié avec l’outil palmares.html, disponible sur le site.',
                confirmLabel: 'Télécharger',
                variant: 'warning',
            });
            if (!confirmed) return;
            if (!App.history && isTeacher) await loadHistory();
            const payload = {
                exported_at: new Date().toISOString(),
                session: App.state ? App.state.session : null,
                bilan: App.state ? App.state.bilan : null,
                history: App.history,
            };
            const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'bilan-export-' + ((App.state && App.state.session && App.state.session.date) ? App.state.session.date : 'session') + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            return;
        }
        if (action === 'client-feedback') {
            if (myRole() !== 'PO') {
                flash('Action réservée au Product Owner.', 'error');
                return;
            }
            const story = storyById(myTeam(), button.dataset.story);
            if (!story || story.etat !== 'a_valider') return;
            const response = await apiAction('consult_client_feedback', { story_id: story.id }, { noPoll: true });
            if (!response || !response.feedback) return;
            story.client_feedback_consulted = true;
            story.client_feedback_position = response.feedback.position;
            openClientFeedbackModal(story, response.feedback);
            await pollNow();
            return;
        }
        if (action === 'validate-story') {
            if (!(await ensureClientFeedbackConsulted(button.dataset.story))) return;
            const confirmed = await showConfirm({
                title: 'Valider la User Story',
                message: 'Avez-vous vérifié les critères d’acceptation et toute la Definition of Done, affichée dans Sprint Review ? Si un critère ou un point de DoD n’est pas satisfait, refusez la User Story avec un motif précis.',
                confirmLabel: 'Valider',
                variant: 'success',
            });
            if (!confirmed) return;
            await apiAction('validate_story', { story_id: button.dataset.story });
            return;
        }
        if (action === 'refuse-story') {
            if (!(await ensureClientFeedbackConsulted(button.dataset.story))) return;
            const story = storyById(myTeam(), button.dataset.story);
            const tooltip = await clientRefusalTooltip(story);
            const comment = await showPrompt({
                title: 'Refuser la User Story',
                message: 'Indiquez le motif précis du refus. Ce commentaire sera visible par l’équipe.',
                label: 'Motif du refus',
                placeholder: 'Critère d’acceptation ou point de DoD non satisfait, défaut bloquant…',
                hint: 'Précisez le critère ou le point de DoD à corriger. 5 à 500 caractères.',
                tooltip: tooltip,
                min: 5,
                max: 500,
                required: true,
                confirmLabel: 'Refuser la story',
                variant: 'danger',
            });
            if (comment === null) return;
            await apiAction('refuse_story', { story_id: button.dataset.story, comment: comment });
            return;
        }
        if (action === 'swap-respond') {
            const accepted = button.dataset.accept === '1';
            if (accepted && myRole() === 'DEVELOPER') {
                const team = myTeam();
                const me = App.state && App.state.me ? App.state.me.login : '';
                if (developerIsAffected(team, me)) {
                    showAlert('Vous avez des User Stories en attente de validation. Déclarez-les prêtes avant d’accepter cet échange.', 'Échange impossible', 'warning');
                    return;
                }
            }
            const confirmed = await showConfirm({
                title: accepted ? 'Accepter l’échange de rôles' : 'Refuser l’échange de rôles',
                message: accepted
                    ? 'Accepter cette proposition ? Les deux membres échangeront immédiatement leurs rôles.'
                    : 'Refuser cette proposition ? Elle sera clôturée sans échange de rôles.',
                confirmLabel: accepted ? 'Accepter l’échange' : 'Refuser l’échange',
                variant: accepted ? 'warning' : 'danger',
            });
            if (!confirmed) return;
            await apiAction('respond_swap', {
                proposal_id: button.dataset.proposal,
                accept: accepted,
            });
            return;
        }
        if (action === 'swap-cancel') {
            const confirmed = await showConfirm({
                title: 'Annuler la proposition',
                message: 'Annuler cette proposition d’échange de rôles ? Elle ne pourra plus être acceptée.',
                confirmLabel: 'Annuler la proposition',
                variant: 'danger',
            });
            if (!confirmed) return;
            await apiAction('cancel_swap', { proposal_id: button.dataset.proposal });
            return;
        }
        if (action === 'teacher-pause') {
            const confirmed = await showConfirm({
                title: 'Suspendre la simulation',
                message: 'Suspendre la simulation ? Les étudiants ne pourront plus modifier les données pendant la pause.',
                confirmLabel: 'Suspendre',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('teacher_pause', {});
            return;
        }
        if (action === 'teacher-resume') {
            const confirmed = await showConfirm({
                title: 'Reprendre la simulation',
                message: 'Reprendre la simulation ? Les étudiants pourront à nouveau modifier les données.',
                confirmLabel: 'Reprendre',
                variant: 'success',
            });
            if (!confirmed) return;
            await apiAction('teacher_resume', {});
            return;
        }
        if (action === 'teacher-stop') {
            const confirmed = await showConfirm({
                title: 'Fin de la simulation',
                message: 'La simulation est terminée. Souhaitez-vous arrêter la session ?',
                confirmLabel: 'Arrêter la session',
                variant: 'danger',
            });
            if (!confirmed) return;
            await apiAction('teacher_stop', {});
            return;
        }
        if (action === 'teacher-reset') {
            const dateAnswer = await showPrompt({
                title: 'Nouvelle séance',
                message: 'Date de la prochaine séance (AAAA-MM-JJ) ?',
                label: 'Date',
                required: true,
                validate: function (value) {
                    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
                    if (!m) return 'Format attendu : AAAA-MM-JJ.';
                    const y = Number(m[1]);
                    const mo = Number(m[2]);
                    const d = Number(m[3]);
                    const dt = new Date(Date.UTC(y, mo - 1, d, 0, 0, 0, 0));
                    if (dt.getUTCFullYear() !== y || dt.getUTCMonth() !== mo - 1 || dt.getUTCDate() !== d) {
                        return 'Date invalide.';
                    }
                    return '';
                },
            });
            if (dateAnswer === null) return;
            const word = (App.state.config && App.state.config.reset_word) || 'REINITIALISER';
            const answer = await showPrompt({
                title: 'Réinitialiser la séance',
                message: 'Cette action efface la séance en cours et remet les équipes dans leur état initial. Cette opération est irréversible. La prochaine séance sera programmée le ' + dateAnswer + '.',
                label: 'Mot de confirmation',
                hint: 'Saisissez « ' + word + ' » pour confirmer.',
                value: '',
                min: 1,
                max: 80,
                required: true,
                confirmLabel: 'Réinitialiser la séance',
                variant: 'danger',
                validate: function (value) {
                    return value === word ? '' : 'Confirmation incorrecte : aucune remise à zéro effectuée.';
                },
            });
            if (answer === null) return;
            await apiAction('teacher_reset', { confirmation: answer, date: dateAnswer });
            return;
        }
        if (action === 'history-refresh') {
            App.history = null;
            App.historyLoading = false;
            loadHistory();
            return;
        }
    }

    async function handleChange(event) {
        const select = event.target.closest('[data-change]');
        if (!select) return;
        const kind = select.dataset.change;
        if (kind === 'story-estimate') {
            const story = storyById(myTeam(), select.dataset.story);
            const previous = story ? story.estimation : '';
            const confirmed = await showConfirm({
                title: 'Estimer la User Story',
                message: 'Changer l’estimation de ' + select.dataset.story + ' à ' + select.value + ' Story Points ?',
                confirmLabel: 'Confirmer',
                variant: 'warning',
            });
            if (!confirmed) {
                if (previous) select.value = previous;
                return;
            }
            await apiAction('estimate_story', {
                story_id: select.dataset.story,
                estimation: Number(select.value),
            });
            return;
        }
        if (kind === 'story-priority') {
            const story = storyById(myTeam(), select.dataset.story);
            const previous = story ? story.priorite : '';
            const confirmed = await showConfirm({
                title: 'Changer la priorité',
                message: 'Changer la priorité de ' + select.dataset.story + ' en « ' + select.value + ' » ?',
                confirmLabel: 'Confirmer',
                variant: 'warning',
            });
            if (!confirmed) {
                if (previous) select.value = previous;
                return;
            }
            await apiAction('set_priority', {
                story_id: select.dataset.story,
                priority: select.value,
            });
            return;
        }
        if (kind === 'task-move') {
            const team = myTeam();
            const task = taskById(team, select.dataset.task);
            const previousState = task ? task.etat : 'a_faire';
            if (!task || previousState === 'termine') {
                select.value = previousState;
                select.disabled = true;
                return;
            }
            const nextState = select.value;
            let reason = '';
            if (nextState === previousState) {
                return;
            }
            if (nextState === 'bloque') {
                const answer = await showPrompt({
                    title: 'Déplacer vers « Bloqué »',
                    message: 'Expliquez l’obstacle réel qui empêche de poursuivre. Une tâche difficile ou longue n’est pas forcément bloquée. Cette explication sera visible par l’équipe et le formateur.',
                    label: 'Explication du blocage',
                    placeholder: 'Dépendance, problème technique, information manquante…',
                    hint: 'Exemple : information indispensable manquante, dépendance technique, environnement indisponible. 3 à 300 caractères.',
                    min: 3,
                    max: 300,
                    required: true,
                    confirmLabel: 'Déplacer la tâche',
                    variant: 'warning',
                });
                if (answer === null) {
                    select.value = previousState;
                    return;
                }
                reason = answer;
            } else if (nextState === 'termine') {
                const opened = await openTaskChallenge(select.dataset.task);
                if (!opened) {
                    select.value = previousState;
                }
                return;
            } else {
                const confirmed = await showConfirm({
                    title: 'Changer l’état de la tâche',
                    message: 'Déplacer cette tâche vers « ' + kanbanLabel(nextState) + ' » ?',
                    confirmLabel: 'Déplacer la tâche',
                    variant: 'primary',
                });
                if (!confirmed) {
                    select.value = previousState;
                    return;
                }
            }
            const result = await apiAction('move_task', {
                task_id: select.dataset.task,
                etat: nextState,
                blocage: reason,
            });
            if (!result) {
                select.value = previousState;
            }
            return;
        }
        if (kind === 'history-filter') {
            App.historyFilter = select.value;
            render();
        }
    }

    async function handleSubmit(event) {
        const form = event.target.closest('form[data-form]');
        if (!form) return;
        event.preventDefault();
        const kind = form.dataset.form;

        if (kind === 'logout') {
            const confirmed = await showConfirm({
                title: 'Confirmer la déconnexion',
                message: 'Voulez-vous vraiment vous déconnecter de WorkFlow IT ?',
                confirmLabel: 'Se déconnecter',
                variant: 'warning',
            });
            if (!confirmed) return;
            form.submit();
            return;
        }

        if (kind === 'password') {
            const currentField = form.querySelector('[name="current_password"]');
            const newField = form.querySelector('[name="new_password"]');
            const confirmField = form.querySelector('[name="confirm_password"]');
            if (!currentField || !newField || !confirmField) return;

            const clean = function (value) { return String(value || '').replace(/\s+/g, ''); };
            const currentPassword = clean(currentField.value);
            const newPassword = clean(newField.value);
            const confirmation = clean(confirmField.value);

            if (!/^\d{8}$/.test(currentPassword)) {
                setPasswordError('Le mot de passe actuel doit contenir exactement 8 chiffres.');
                currentField.focus();
                return;
            }
            if (!/^\d{8}$/.test(newPassword)) {
                setPasswordError('Le nouveau mot de passe doit contenir exactement 8 chiffres.');
                newField.focus();
                return;
            }
            if (newPassword !== confirmation) {
                setPasswordError('La confirmation ne correspond pas au nouveau mot de passe.');
                confirmField.focus();
                return;
            }

            setPasswordError('');
            const data = await apiAction('change_password', {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmation,
            }, { onError: setPasswordError });
            if (data) {
                closeModal('password-modal');
                form.reset();
            }
            return;
        }

        if (kind === 'chat') {
            const field = form.querySelector('[name="text"]');
            if (!field || !field.value.trim()) return;
            const text = field.value.trim();
            const confirmed = await showConfirm({
                title: 'Envoyer le message',
                message: 'Envoyer ce message à l’équipe ? Il sera visible par les membres de l’équipe et par le formateur.',
                confirmLabel: 'Envoyer',
            });
            if (!confirmed) return;
            const data = await apiAction('chat_send', { text: text });
            if (data) {
                field.value = '';
                if (document.activeElement === field) document.activeElement.blur();
            }
            return;
        }

        if (kind === 'goal') {
            const goal = form.querySelector('[name="goal"]');
            const justification = form.querySelector('[name="justification"]');
            const participants = Array.from(form.querySelectorAll('[name="participants"]:checked')).map(function (el) { return el.value; });
            if (!goal || !justification) return;
            const confirmed = await showConfirm({
                title: 'Enregistrer le Sprint Goal',
                message: 'Enregistrer ce Sprint Goal et sa justification ? La modification sera tracée dans l’historique.',
                confirmLabel: 'Enregistrer',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('update_goal', {
                goal: goal.value,
                justification: justification.value,
                participants: participants,
            });
            return;
        }

        if (kind === 'retrospective') {
            const text = form.querySelector('[name="text"]');
            if (!text || !text.value.trim()) return;
            const confirmed = await showConfirm({
                title: 'Enregistrer la rétrospective',
                message: 'Enregistrer cette contribution à la rétrospective ? Elle sera partagée avec l’équipe et conservée dans l’historique.',
                confirmLabel: 'Enregistrer',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('update_retrospective', { text: text.value.trim() });
            return;
        }

        if (kind === 'swap') {
            const target = form.querySelector('[name="target"]');
            if (!target) return;
            const team = myTeam();
            const me = App.state && App.state.me ? App.state.me.login : '';
            if (myRole() === 'DEVELOPER' && developerIsAffected(team, me)) {
                showAlert('Vous avez des User Stories en attente de validation. Déclarez-les prêtes avant de proposer un échange.', 'Échange impossible', 'warning');
                return;
            }
            if (developerIsAffected(team, target.value)) {
                showAlert('Ce Developer a des User Stories en attente de validation. Il doit d’abord les déclarer prêtes.', 'Échange impossible', 'warning');
                return;
            }
            const targetLabel = target.options && target.selectedIndex >= 0
                ? target.options[target.selectedIndex].textContent.trim()
                : target.value;
            const confirmed = await showConfirm({
                title: 'Proposer un échange de rôles',
                message: 'Proposer cet échange à ' + targetLabel + ' ? La proposition expirera après cinq minutes.',
                confirmLabel: 'Envoyer la proposition',
                variant: 'warning',
            });
            if (!confirmed) return;
            await apiAction('propose_swap', { target: target.value });
            return;
        }

        if (kind === 'announce') {
            const target = form.querySelector('[name="target"]');
            const text = form.querySelector('[name="text"]');
            if (!target || !text) return;
            if (!text.value.trim()) {
                flash('Le texte de l’annonce est vide.', 'error');
                return;
            }
            const confirmed = await showConfirm({
                title: 'Publier l’annonce',
                message: 'Publier cette annonce ? Elle sera immédiatement visible par les étudiants concernés.',
                confirmLabel: 'Publier',
                variant: 'warning',
            });
            if (!confirmed) return;
            const data = await apiAction('teacher_announce', { target: Number(target.value), text: text.value });
            if (data) {
                text.value = '';
                if (document.activeElement === text) document.activeElement.blur();
            }
            return;
        }

        if (kind === 'moderation') {
            const payload = {
                type: (document.getElementById('moderation-type') || {}).value || '',
                team: Number((document.getElementById('moderation-team') || {}).value || 0),
                content_id: (document.getElementById('moderation-content') || {}).value || '',
                field: (document.getElementById('moderation-field') || {}).value || '',
                text: (document.getElementById('moderation-text') || {}).value || '',
            };
            const index = (document.getElementById('moderation-index') || {}).value;
            if (index !== undefined && index !== '') payload.index = Number(index);
            const confirmed = await showConfirm({
                title: 'Enregistrer la correction',
                message: 'Enregistrer cette correction ? Le nouveau texte sera visible par les étudiants et l’ancien restera dans l’historique.',
                confirmLabel: 'Enregistrer la correction',
                variant: 'warning',
            });
            if (!confirmed) return;
            const data = await apiAction('moderate_edit', payload);
            if (data) {
                closeModal('moderation-modal');
            }
            return;
        }
    }

    async function handleTaskSubmit(event) {
        event.preventDefault();
        const form = event.target;
        if (!form || form.id !== 'task-form') return;
        const id = document.getElementById('task-id').value;
        const payload = {
            titre: document.getElementById('task-title').value,
            description: document.getElementById('task-description').value,
            us_id: document.getElementById('task-story').value,
            heures: Number(document.getElementById('task-hours').value),
            assigne: document.getElementById('task-assignee').value,
            etat: document.getElementById('task-state').value,
            blocage: document.getElementById('task-blocking').value,
        };
        let data;
        if (id && payload.etat === 'termine' && !App.challengePassedTasks.has(id)) {
            await openTaskChallenge(id);
            return;
        }
        if (id) {
            const confirmed = await showConfirm({
                title: 'Enregistrer les modifications',
                message: 'Enregistrer les modifications de la tâche ' + id + ' ? Les changements seront visibles par l’équipe.',
                confirmLabel: 'Enregistrer',
                variant: 'warning',
            });
            if (!confirmed) return;
            payload.task_id = id;
            data = await apiAction('update_task', payload);
        } else {
            data = await apiAction('create_task', payload);
        }
        if (data) {
            closeModal('task-modal');
            form.reset();
            document.getElementById('task-story').disabled = false;
        }
    }

    // -----------------------------------------------------------------------
    // Initialisation
    // -----------------------------------------------------------------------
    function initEvents() {
        document.addEventListener('click', handleClick);
        document.addEventListener('change', handleChange);
        document.addEventListener('submit', function (event) {
            if (event.target && event.target.id === 'ui-modal-form') {
                event.preventDefault();
                submitUiDialog();
            } else if (event.target && event.target.id === 'task-form') {
                handleTaskSubmit(event);
            } else {
                handleSubmit(event);
            }
        });

        const taskState = document.getElementById('task-state');
        if (taskState) taskState.addEventListener('change', syncTaskBlockingField);
    const challengeSubmit = document.getElementById('challenge-submit');
    if (challengeSubmit) challengeSubmit.addEventListener('click', submitTaskChallenge);
    const challengeTerminalForm = document.getElementById('challenge-terminal-form');
    if (challengeTerminalForm) {
        challengeTerminalForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitTaskChallengeTerminal();
        });
    }
    const challengeTerminalInput = document.getElementById('challenge-terminal-input');
    if (challengeTerminalInput) {
        challengeTerminalInput.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown') return;
            if (event.altKey || event.ctrlKey || event.metaKey || !App.terminalHistory.length) return;
            event.preventDefault();
            if (event.key === 'ArrowUp') {
                if (App.terminalHistoryIndex > 0) App.terminalHistoryIndex--;
                challengeTerminalInput.value = App.terminalHistory[App.terminalHistoryIndex] || '';
            } else if (App.terminalHistoryIndex < App.terminalHistory.length - 1) {
                App.terminalHistoryIndex++;
                challengeTerminalInput.value = App.terminalHistory[App.terminalHistoryIndex] || '';
            } else {
                App.terminalHistoryIndex = App.terminalHistory.length;
                challengeTerminalInput.value = '';
            }
            const end = challengeTerminalInput.value.length;
            try {
                challengeTerminalInput.setSelectionRange(end, end);
            } catch (err) {
                // champ non compatible avec la sélection
            }
        });
    }
    document.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'challenge-option') updateChallengeCountdown();
    });

        const helpButton = document.getElementById('help-button');
        if (helpButton) {
            helpButton.addEventListener('click', function () { openModal('help-modal'); });
        }

        const teacherHelpButton = document.getElementById('teacher-help-button');
        if (teacherHelpButton) {
            teacherHelpButton.addEventListener('click', function () { openModal('teacher-help-modal'); });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const toolHelp = document.getElementById('tool-help-modal');
                if (toolHelp && !toolHelp.hidden) {
                    closeModal('tool-help-modal');
                    return;
                }
                const challengeModal = document.getElementById('challenge-modal');
                if (challengeModal && !challengeModal.hidden) {
                    closeChallengeModal();
                    return;
                }
                closeModal('ui-modal');
                closeModal('help-modal');
                closeModal('kanban-stats-modal');
                closeModal('teacher-help-modal');
                closeModal('task-modal');
                closeModal('moderation-modal');
                closeModal('password-modal');
            }
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) pollNow();
            else schedulePoll();
        });

        // Les boutons de navigation existent avant l'état ; le premier état
        // activera le bon onglet.
        setActiveNav();
        startClock();
        fetchState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEvents);
    } else {
        initEvents();
    }
})();
