# WorkFlow IT — simulation Scrum

Application PHP/HTML/CSS/JavaScript natif pour une simulation Scrum de 85 minutes actives, après 10 minutes de prise en main.
Le serveur PHP est l’autorité unique : rôles, horloge, permissions, événements,
historique et remise à zéro sont contrôlés côté serveur.

## 1. Prérequis

- PHP **8.0 ou supérieur** (8.1/8.2/8.3 recommandé, testé avec PHP 8.3 et 8.5).
- Extensions PHP : `json`, `mbstring`, `session`, `random`, `date`.
- Écriture par l’utilisateur PHP dans le dossier `datas/`.
- Serveur Apache avec `.htaccess` (hébergement LWS) ou configuration Nginx
  équivalente interdisant l’accès HTTP direct à `datas/` et `includes/`.
- HTTPS fortement recommandé pour protéger les cookies de session.

Aucune base de données, aucun framework, aucun build front-end, aucun WebSocket.

## 2. Arborescence

```text
workflow-it/
├── workflow.php          point d’entrée HTML / connexion
├── api.php               endpoint AJAX unique
├── config.php            horaires, chemins, constantes de sécurité
├── cron_events.php       déclencheur CLI facultatif pour les événements
├── includes/
│   ├── auth.php          sessions, connexion, utilisateurs
│   ├── functions.php     JSON, verrous, validation, CSRF, historique
│   └── game.php          règles Scrum, événements, bilan
├── assets/
│   ├── style.css
│   ├── app.js
│   └── login.js          connexion asynchrone et confirmation
├── datas/
│   ├── .htaccess         protection Apache
│   ├── users.txt         comptes fournis (jamais à exposer)
│   ├── session.json
│   ├── team_1.json
│   ├── team_2.json
│   ├── team_3.json
│   ├── events.json
│   ├── history.json
│   ├── login_attempts.json
│   ├── rate_limit.json
│   └── *.lock            verrous créés à l’exécution
├── README.md
├── TESTS.md
├── RAPPORT_VERIFICATION.md
├── RAPPORT_MODERATION.md
└── RAPPORT_TESTS_FINAUX.md
```

Les fichiers `datas/*.json` peuvent être recréés par la remise à zéro du formateur.
Les fichiers `.lock` sont des verrous techniques : ils ne contiennent pas de données
métier et ne doivent pas être édités.

## 3. Installation sur LWS

1. Déposer l’arborescence de l’application dans le dossier Web du site (ou un
   sous-dossier). Ne pas publier les fichiers `prompt_*.txt` (dont
   `prompt_scrum.txt`, `prompt_verification.txt`, `prompt_amelioration.txt` et
   `prompt_tests_finaux.txt`) : ce sont des documents de conception et de test,
   à conserver hors de la racine Web.
2. Vérifier que le dossier `datas/` est accessible en écriture au compte PHP.
   Les fichiers JSON sont écrits atomiquement (fichier temporaire puis `rename`).
3. Conserver `datas/.htaccess`. Il interdit l’accès direct aux données sur Apache :
   `Require all denied` (Apache 2.4) avec compatibilité Apache 2.2.
4. Vérifier depuis un navigateur que `https://votre-site/datas/users.txt` répond
   **403 Forbidden**, de même que `datas/session.json` et `includes/config.php`.
   Ne pas poursuivre le déploiement si ces URL sont accessibles.
5. Pour Nginx ou un autre serveur qui ignore `.htaccess`, ajouter une règle
   `deny all` sur `datas/` et `includes/`, ou placer ces dossiers hors de la
   racine Web.
6. Activer HTTPS et vérifier que le cookie de session est émis avec `Secure`
   lorsque `HTTPS` est détecté.
7. Ouvrir `workflow.php`, vérifier les identifiants puis confirmer la
   participation dans la modale. Annuler doit laisser la page de connexion
   affichée, sans accès aux données.
8. Vérifier l’heure du serveur et les horaires affichés. La simulation démarre
   automatiquement à **13 h 10** et se termine à **14 h 35**, fuseau Europe/Paris.
   La prise en main occupe 13 h 00–13 h 10 ; l’analyse post-session peut utiliser
   14 h 35–14 h 45.

### Tâche planifiée facultative

Les événements sont entièrement calculés côté serveur. Pour qu’ils avancent même
sans navigateur connecté, créer une tâche planifiée LWS toutes les minutes :

```text
php /chemin/vers/workflow-it/cron_events.php
```

Le script refuse toute exécution Web (`PHP_SAPI !== 'cli'` → 404). Sans cron,
les événements arrivés à échéance sont matérialisés à la prochaine requête
authentifiée ; ils ne sont jamais déclenchés par le JavaScript du navigateur.

## 4. Configuration

Tout se règle dans `config.php` :

- `APP_TIMEZONE` : fuseau de référence, par défaut `Europe/Paris`.
- `SESSION_OPEN_TIME`, `SIM_START_TIME`, `SIM_END_TIME` : horaires fixes.
  La fin ne peut pas être déplacée par le formateur.
- `SESSION_DATE_OVERRIDE` : date forcée `AAAA-MM-JJ` pour une séance de test ;
  laisser la chaîne vide en production.
- `DEVELOPER_CAPACITY_HOURS`, `SWAP_TTL_SECONDS`, `CHAT_MAX_LENGTH`,
  `CHAT_MIN_INTERVAL_SECONDS`, `MAX_EVENTS_PER_TEAM`, `EVENT_MIN_GAP_SECONDS`.
- `POLL_INTERVAL_MS` : intervalle de synchronisation nominale.
- `MAX_LOGIN_ATTEMPTS`, `LOGIN_LOCK_SECONDS`, `LOGIN_CONFIRM_TTL_SECONDS`,
  `API_RATE_LIMIT_PER_SECOND`, `PASSWORD_REQUEST_COOLDOWN_SECONDS`.
- `RESET_WORD` : mot de confirmation de la remise à zéro. Le remplacer avant
  la mise en production et ne jamais le publier dans la documentation.

### Comptes

Le fichier `datas/users.txt` utilise une ligne par compte :

```text
login;JJMMAAAA;groupe;role;hash
```

- `login` : minuscules ASCII, 1 à 30 caractères.
- `JJMMAAAA` : valeur initiale du mot de passe, jamais envoyée au navigateur.
  Pour les étudiants, c’est la date de naissance au format jour-mois-année :
  elle doit être valide et sert aussi à la réinitialisation par le formateur.
  Pour le formateur, ce champ est son mot de passe initial à 8 chiffres, sans
  contrôle de validité de date.
- `groupe` : `0` pour le formateur, `1`, `2` ou `3` pour une équipe.
- `role` : `PO`, `SM`, `DEV` (ou les variantes acceptées par `normalize_role`).
- `hash` : hash sécurisé du mot de passe actuel, généré automatiquement par
  l’application. Ne jamais le modifier à la main.

Les anciens fichiers à quatre colonnes restent lisibles : au premier démarrage,
l’application ajoute automatiquement le hash manquant sans modifier la valeur
initiale de la deuxième colonne. Le mot de passe initial des étudiants est
toujours leur date de naissance `JJMMAAAA` ; ils peuvent ensuite le changer
depuis le bouton **Mon mot de passe**. Le formateur peut réinitialiser le mot
de passe d’un étudiant à cette valeur initiale depuis la **Vue globale**, après
confirmation. La réinitialisation est strictement réservée au formateur et ne
transmet jamais la date de naissance au navigateur.

Le groupe 0 est réservé au formateur ; les étudiants doivent former des équipes
avec exactement un PO, un Scrum Master et au moins un Developer. Les rôles
courants peuvent ensuite changer par échange accepté.

## 5. Connexion, confirmations et déroulement

### Connexion en deux étapes

La page de connexion vérifie les identifiants en AJAX, sans rechargement complet.
Lorsque le login et le mot de passe sont acceptés, aucune session applicative
n’est encore ouverte : une modale demande explicitement de **Rejoindre la partie**
ou d’**Annuler**. À la première connexion, le mot de passe étudiant est sa date
de naissance au format `JJMMAAAA` ; il peut ensuite le personnaliser.

- Avant confirmation, `workflow.php` n’affiche aucune coquille applicative,
  `api.php?action=state` répond `401` et le polling ne démarre pas.
- La confirmation est liée à un jeton aléatoire stocké en session, expire après
  `LOGIN_CONFIRM_TTL_SECONDS` (120 secondes par défaut) et exige le jeton CSRF.
- L’annulation supprime la connexion en attente. Aucune session authentifiée ni
  donnée d’équipe n’est laissée accessible.
- La session PHP est régénérée au moment de la confirmation, pas avant.
- Un échec affiche un message générique sans recharger la page : le mot de passe
  est effacé, le login est conservé, le bouton redevient actif et le message
  disparaît après cinq secondes.
- Le parcours fonctionne aussi sans JavaScript grâce au formulaire de secours :
  la vérification affiche alors la modale côté serveur et la confirmation est un
  POST classique.

### Gestion des mots de passe

- **Changer son mot de passe** : chaque utilisateur connecté dispose du bouton
  **Mon mot de passe**. Le changement exige le mot de passe actuel, un nouveau
  mot de passe de 8 chiffres et sa confirmation. Les trois contrôles sont
  refaits côté serveur. Le formateur utilise le même formulaire.
- **Mot de passe oublié** : la page de connexion propose un formulaire discret.
  La demande est enregistrée côté serveur et le formateur la voit dans sa
  **Vue globale**, avec un badge sur l’onglet. La réponse à l’étudiant reste
  volontairement neutre, qu’il existe ou non, et aucun mot de passe n’est
  modifié automatiquement. Une même demande est limitée à une toutes les
  `PASSWORD_REQUEST_COOLDOWN_SECONDS` (600 secondes par défaut).
- **Réinitialisation par le formateur** : depuis la **Vue globale**, le formateur
  peut réinitialiser un étudiant après confirmation. Le mot de passe revient à
  la date de naissance initiale stockée dans `users.txt`. La date n’est jamais
  affichée ni renvoyée au navigateur, et la demande en attente est marquée comme
  traitée.
- Les mots de passe ne sont jamais stockés en clair : `users.txt` contient un
  hash, et les notifications ou l’historique ne contiennent ni mot de passe ni
  date de naissance.

### Terminal technique avant validation

Chaque User Story peut être défendue par un terminal GNU/Linux simulé avant le
passage à « Prête à valider ». La modale ne s’ouvre que sur un clic explicite
sur **Prête à valider** : elle n’est jamais déclenchée par le chargement, la
connexion, le rechargement, la navigation, le Product Backlog ou une
synchronisation d’état. Sa fermeture réinitialise le contexte temporaire et le
terminal ne peut pas reprendre les données d’une autre User Story.

Le quiz de validation d’une tâche conserve son déclenchement lors du passage
explicite de cette tâche à « Terminé » : ce n’est pas une ouverture depuis
l’état synchronisé. Seul le terminal d’une User Story est strictement réservé
au clic sur **Prête à valider**.

L’interface est un terminal unique : une seule ligne de saisie avec prompt,
sans champ « Commande » séparé, sans bouton « Exécuter » et sans libellé
d’aide parasite. L’émulation reste
entièrement côté navigateur : le prompt utilise le login connecté
(`<login>@workflow-it:/home/<login>$`) et les commandes `pwd`, `ls`, `ls -l`,
`ls -la` et `help` ne touchent jamais le système réel. La saisie conserve
l’historique, le focus et le défilement ; les flèches haut/bas rappellent les
commandes précédentes. `help` reprend le champ `exploration` du défi courant,
sans commande codée en dur pour une autre User Story.

La progression est volontairement aidée : une commande d’exploration débloque le
premier indice, le premier extrait caché débloque le deuxième, le second extrait
débloque le troisième. Les indices restent dans l’historique de la validation en
cours. Chaque clic sur **Prête à valider** démarre toutefois une nouvelle
validation : le niveau d’indices de cette User Story est remis à zéro, y compris
lorsqu’une tentative précédente avait été abandonnée. Les indices d’une autre
validation ne sont donc jamais réaffichés automatiquement ; ils doivent être
obtenus un par un. La commande `solution` n’est proposée qu’après le troisième
indice et restitue alors la commande exacte. La commande exacte est contrôlée
avant les extraits, ce qui évite qu’une réponse correcte commençant par un
extrait soit mal classée.

Le délai de 30 secondes a été supprimé. Le client applique un délai de
2 secondes entre deux commandes, tout en laissant le champ éditable pour
préparer la suivante. Le message « Patientez encore… » disparaît
automatiquement dès que le délai est écoulé. Les payloads de démarrage et d’état ne contiennent ni
`command` ni `partials` ; la commande n’est renvoyée qu’après une demande
explicite `solution` au troisième indice. Cette séparation évite les fuites
accidentelles, mais ne constitue pas un secret fort : le bundle JavaScript et
les données accessibles au navigateur restent inspectables. Le flux « Prête à
valider », les rôles, le CSRF, les verrous JSON et l’endpoint AJAX unique sont
conservés.

### Confirmations et alertes

L’interface authentifiée utilise une modale unique pour les alertes, les
confirmations et les saisies courtes. Les actions destructrices ou
conséquentes demandent une confirmation avant leur envoi : archivage, mise à
jour d’une tâche, signalement de blocage, réponse à un événement, validation ou
refus d’une story, échange de rôles, pause/reprise/reset du formateur, Sprint
Goal, annonces, modération et suppression de contenu. Les succès non bloquants
restent affichés dans le bandeau de notification ; aucune alerte native du
navigateur n’est utilisée.

### Aide intégrée

Le bouton **Aide générale** ouvre une modale pédagogique disponible pendant toute
la séance. Elle présente le principe de la simulation, les actions attendues pour
chaque rôle (Product Owner, Scrum Master, Developers), un déroulé conseillé, les
notions de backlog, Sprint, Story Points, valeur métier, Kanban et Definition of
Done, le fonctionnement des événements, du chat, des annonces et des échanges de
rôles, ainsi qu’une section **Trucs et astuces**. Le contenu est adapté aux
étudiants débutants et n’invente aucune fonctionnalité absente de l’interface.

Chaque outil étudiant (**Tableau de bord**, **Sprint Review et rétrospective**,
**Product Backlog**, **Kanban**, **Événements**, **Chat et annonces**,
**Rôles et échanges**) dispose en plus d’un bouton **Aide de l’outil** dans son
en-tête. Il ouvre une modale contextualisée qui explique la finalité de l’outil,
les rôles concernés, les étapes d’utilisation, les règles et un exemple concret.
Le contenu s’adapte au rôle connecté. La même modale est réutilisée pour tous les
outils et se ferme par le bouton, la croix ou la touche Échap.

L’aide **Kanban** précise que le tableau est un support pédagogique : l’application
ne produit aucun code ni livrable technique et le traitement des tâches est
simulé. Elle rappelle les responsabilités des Developers, la différence entre une
tâche difficile et une tâche réellement bloquée, les causes de blocage attendues
et la conduite à tenir pour signaler puis lever l’obstacle. Le bouton
**Statistiques**, placé dans l’en-tête du Kanban, ouvre une modale unique qui
présente les décisions du client : répartition sans risque / avec risque,
acceptations et refus, taux par développeur et détail des critères de risque
constatés. Ces données sont figées au moment de chaque décision et ne modifient
ni le moteur 30/60 ni le workflow de validation. L’aide **Product
Backlog** ajoute une méthode de vérification de la Definition of Done avant
validation, avec des questions de contrôle et des exemples cohérents avec
WorkFlow IT. La liste exacte de la DoD reste celle affichée par l’application.

Le formulaire **Nouvelle tâche** dispose de son propre bouton **? Aide**. Cette
aide explique la décomposition des User Stories en tâches techniques, le rôle de
chaque champ, la différence entre les heures des tâches et les Story Points, les
quatre états du Kanban et la conduite à tenir en cas de blocage. Le champ
**Explication du blocage** n’apparaît que lorsque l’état sélectionné est
« Bloqué ». L’aide rappelle que la création d’une tâche ne réalise pas le travail
et que l’application ne produit aucun code à la place des Developers.

Sous le login étudiant, l’en-tête affiche le **rôle initial** et le **rôle actuel**.
Le rôle actuel est mis à jour après un échange accepté sans reconnexion ; le rôle
initial reste informatif et ne donne jamais accès aux droits correspondants.

L’onglet **Bilan** est disponible en permanence pour chaque étudiant, dès la
découverte et jusqu’après la fin de la simulation. Il présente les indicateurs de
sa seule équipe, avec les mêmes définitions que le bilan formateur, et se met à
jour avec l’actualisation habituelle de la page. Il est strictement consultatif :
il ne modifie ni les règles de fin de Sprint ni les données enregistrées, et ne
donne jamais accès aux autres équipes ni aux informations du formateur.

Le formateur dispose en plus d’un bouton **Aide formateur**. Cette modale n’est
rendue que dans sa session : elle couvre la préparation, la supervision, les
annonces, la modération, l’historique, le bilan, la remise à zéro et les bonnes
pratiques de débriefing. Les consignes réservées au formateur ne sont donc pas
envoyées aux étudiants.

### Déroulement

- **Avant 13 h 00** : la page de connexion reste accessible et affiche l’état de
  la séance ; les données de simulation ne sont pas encore consultables.
- **13 h 00–13 h 10** : découverte, lecture du backlog, de l’aide et des règles ;
  les modifications sont bloquées.
- **13 h 10–14 h 35** : 85 minutes de simulation active — sélection du Sprint,
  estimations, tâches, Kanban, événements, Review, rétrospective, chat et échanges
  de rôles.
- **14 h 35–14 h 45** : modifications bloquées, analyse post-session et, pour le
  formateur, bilan comparatif et historique.

Le formateur peut suspendre/reprendre la simulation. La pause bloque les
modifications des joueurs mais ne déplace pas l’heure de fin.

### Modération des contenus libres

Le formateur dispose d’un onglet **Modération** réservé à son rôle. Il peut y
corriger ou supprimer les textes libres produits par les étudiants :

- messages du chat ;
- titres, descriptions et motifs de blocage des tâches ;
- Sprint Goal et justification collective ;
- rétrospective collective ;
- commentaires de refus des User Stories ;
- annonces du formateur (dans leur vue dédiée).

Les titres, descriptions et critères des User Stories sont fournis par
l’application et ne sont pas modifiables par les étudiants : ils ne sont donc
pas modérables par le formateur.

Chaque ligne indique l’équipe, le type de contenu, son identifiant, son auteur
lorsqu’il est connu et la date. Une suppression est logique : le contenu disparaît
des réponses API et de l’interface normale, mais reste tracé dans l’historique
avec l’ancien texte, le nouveau texte, la date, l’action et le formateur. Les
tâches, User Stories et annonces ne sont pas supprimées en tant qu’objets.

Les actions utilisent `api.php` (`moderate_edit`, `moderate_delete`) avec le
même jeton CSRF, les mêmes verrous JSON et la même limite de requêtes que le
reste de l’application. Aucun bouton de modération n’est envoyé aux étudiants.
Les champs libres absents du modèle actuel (création de User Story par un
étudiant, justification de réponse à un événement, commentaire d’échange de
rôles) ne sont pas inventés : tous les champs libres réellement existants sont
modérables.

## 6. Remise à zéro

La remise à zéro est réservée au formateur, nécessite le mot `RESET_WORD` et
est refusée pendant une simulation en cours ou en pause.

Elle recrée la session, les trois copies du Product Backlog, les tâches, les
événements, les chats, les annonces et l’historique. Après 14 h 35, la nouvelle
séance est planifiée pour le lendemain ; avant 14 h 35, elle reste sur la journée
courante.

Procédure :

1. Se connecter avec le compte formateur.
2. Ouvrir **Vue globale**.
3. Cliquer sur **Nouvelle séance**.
4. Saisir le mot de confirmation configuré.
5. Vérifier l’état affiché et la nouvelle date de séance.

En cas de données corrompues, sauvegarder `datas/` puis contacter
l’administrateur technique : l’application refuse d’écraser silencieusement un
fichier JSON invalide.

## 7. Sécurité et exploitation

- Toutes les actions passent par `api.php` ; aucune écriture directe de JSON par
  le navigateur.
- Les rôles et l’équipe sont relus depuis la session et les fichiers serveur.
- Les écritures JSON utilisent un verrou stable par fichier et un verrou global
  pour les opérations multi-fichiers ; les écritures sont atomiques.
- Les jetons CSRF sont obligatoires pour toute écriture HTTP, y compris la
  vérification et la confirmation de connexion.
- Une vérification réussie ne crée qu’une connexion en attente, sans droit
  d’accès. La session authentifiée est créée et son identifiant régénéré
  seulement après confirmation explicite.
- Les sessions utilisent un cookie `HttpOnly`, `SameSite=Lax` et `Secure` sous
  HTTPS.
- Limitation des tentatives de connexion, expiration de la confirmation et
  limite globale de requêtes API.
- Les dates de naissance ne sont jamais incluses dans l’état, l’historique ou
  les réponses d’erreur.
- Chaque utilisateur authentifié peut changer son mot de passe (8 chiffres)
  avec son mot de passe actuel et une confirmation identique. Le formateur peut
  réinitialiser un étudiant à sa valeur initiale ; cette action lui est
  strictement réservée. Les deux contrôles sont refaits côté serveur.
- La modération est contrôlée côté serveur : rôle formateur, cible validée,
  jeton CSRF, limite de requêtes et verrou global. Un étudiant ne peut ni
  modifier ni supprimer un contenu par les actions `moderate_*`.
- L’historique des interventions est réservé au formateur et contient l’ancien
  et le nouveau texte ; il ne contient jamais de mot de passe ni de date de
  naissance.
- Les erreurs détaillées sont écrites dans les journaux serveur, pas affichées.
- Le dossier `datas/` doit être sauvegardé avant une remise à zéro et protégé
  HTTP. Ne jamais versionner un `users.txt` réel dans un dépôt public.
- Le formateur ne peut ni avancer le démarrage, ni retarder la fin, ni changer
  la durée depuis l’interface.

## 8. Dépannage

| Symptôme | Vérification |
|---|---|
| Page blanche / erreur de démarrage | Droits sur `datas/`, syntaxe PHP, présence de `users.txt`. |
| `Fichier JSON invalide` | Restaurer une sauvegarde ou demander une remise à zéro technique. Ne pas éditer un JSON à la main. |
| `Trop de requêtes` | Attendre une seconde ; la limite protège LWS. |
| `Trop de tentatives` | Attendre la fin du verrouillage de connexion. |
| Événements absents | Vérifier que la phase est « en cours », puis configurer le cron `cron_events.php`. |
| `403` sur `datas/users.txt` | Comportement attendu : la protection Apache fonctionne. |
| `200` sur `datas/users.txt` | Déploiement non sûr (Nginx ou `.htaccess` ignoré) : bloquer l’accès avant utilisation. |

## 9. Compatibilité et limites

- Testé en local avec PHP 8.5 (CLI et serveur intégré), Apache 2.4 + PHP 8.3
  et Chromium/Playwright.
- La protection `.htaccess` est testée sous Apache ; elle ne s’applique pas au
  serveur intégré `php -S`, qui ne doit pas être utilisé en production.
- LWS peut imposer ses propres limites de requêtes, de cron ou de taille de
  fichiers ; vérifier `datas/` et les journaux après déploiement.
- La modération a été validée par 69 tests directs, 42 tests HTTP sur `php -S`,
  42 tests HTTP derrière Apache et une suite Playwright dédiée. La connexion en
  deux étapes, les alertes et les confirmations disposent de leurs propres tests
  HTTP et Playwright. La passe de correction du terminal a rejoué le lint et
  **273 vérifications terminal, 0 échec** ; les 806 vérifications PHP/HTTP
  proviennent de la passe antérieure. Le détail et les limites figurent dans
  `TESTS.md`, `RAPPORT_VERIFICATION.md`, `RAPPORT_MODERATION.md` et
  `RAPPORT_TESTS_FINAUX.md`.
- Aucun test n’a été effectué sur un hébergement LWS réel ; les points à vérifier
  sur place sont indiqués dans `TESTS.md`.
