# Rapport de tests finaux — authentification et robustesse

Date de la passe : 5 novembre 2026.
Périmètre : parcours utilisateur de `workflow-it/`, conformément à
`prompt_tests_finaux.txt`. Les tests ont été exécutés sur des copies jetables
sous `/tmp/opencode`. Les données livrées n’ont pas été utilisées comme jeu de
test et restent dans leur état initial.

## A. Synthèse

L’application est stable localement : **806 vérifications PHP/HTTP réussies
lors de la passe antérieure, 0 échec**, **273 vérifications terminal rejouées
dans la passe corrective, 0 échec** et **sept suites Playwright réussies** lors
de la passe antérieure. Les parcours étudiants, formateur et modération ne
présentent pas de régression connue. Les 273 vérifications terminales sont les
seules suites rejouées après le correctif de déclenchement ; elles couvrent
134 tests serveur, 52 tests client, 26 tests HTTP et 61 tests navigateur.

La principale amélioration de cette passe est la connexion en deux étapes :

1. vérification asynchrone des identifiants, sans rechargement de page ;
2. confirmation explicite dans une modale avant tout accès applicatif ;
3. création de la session authentifiée et régénération de son identifiant
   uniquement après confirmation ;
4. annulation qui supprime la connexion en attente, sans laisser d’accès.

Aucune anomalie critique ou bloquante n’a été détectée après corrections. Les
réserves restantes concernent l’hébergement LWS réel, qui n’a pas été testé.

## B. Tests d’authentification

| Scénario | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|
| Compte joueur valide | Vérification acceptée, modale de confirmation | Modale affichée, aucun accès avant confirmation | Réussi |
| Compte formateur valide | Même parcours, droits formateur après confirmation | Conforme | Réussi |
| Secret formateur de 8 chiffres non-date | `load_users()` et `login_begin()` l’acceptent sans `checkdate()` | Conforme sur copie isolée | Réussi |
| Login avec espaces avant/après | Normalisation serveur, authentification acceptée | Conforme | Réussi |
| Login avec casse différente | Normalisation serveur, authentification acceptée | Conforme | Réussi |
| Nouvelle session navigateur | Aucun accès avant confirmation | Conforme | Réussi |
| Confirmation | Session créée, identifiant de session régénéré, application chargée | Conforme | Réussi |
| Annulation ou fermeture de la modale | Aucun accès, aucune session authentifiée | Conforme, `api.php?action=state` reste en `401` | Réussi |
| Confirmation sans CSRF | Refusée | `403` | Réussi |
| Rejeu après confirmation ou annulation | Refusé | `403` | Réussi |
| Login inexistant | Refus générique | Conforme | Réussi |
| Date de naissance incorrecte | Refus générique | Conforme | Réussi |
| Login et date incorrects | Refus générique | Conforme | Réussi |
| Login ou date vides | Message compréhensible, pas de plantage | Refus côté navigateur et serveur | Réussi |
| Date incomplète ou mal formée | Refus | Conforme | Réussi |
| Caractères inattendus, HTML ou JavaScript | Aucun script exécuté, aucune erreur critique | Échappement et validation serveur | Réussi |
| Échec de connexion | Message sans rechargement, date effacée, login conservé, bouton réactivé | Conforme | Réussi |
| Disparition du message | Fluide après cinq secondes | Conforme | Réussi |
| Double soumission | Une seule requête, bouton désactivé et état d’attente | Conforme | Réussi |
| Erreur réseau | Message compréhensible, nouvelle tentative possible | Conforme | Réussi |
| Réponse serveur invalide | Message compréhensible, formulaire non bloqué | Conforme | Réussi |
| Actualisation après connexion | Session conservée | Conforme | Réussi |
| Second onglet | Session et interface disponibles | Conforme | Réussi |
| Déconnexion puis reconnexion | Session fermée (`401`), reconnexion possible | Conforme | Réussi |
| Tentatives répétées | Verrouillage temporaire sans révéler le compte | Conforme, test HTTP | Réussi |
| Expiration de session après trois heures | Retour au formulaire | Non exécuté (durée de session) | Non exécuté |
| Fermeture réelle puis réouverture du navigateur | Session restaurée si le cookie persiste | Non exécuté | Non exécuté |

## C. Tests de robustesse

| Domaine | Couverture | Résultat |
|---|---|---|
| Stockage JSON, verrous, atomicité | Écritures concurrentes, JSON invalide, absence de perte | 491 tests directs, 0 échec |
| Modération | Droits, champs libres, historique ancien/nouveau, XSS, doublons | 69 tests directs, 0 échec |
| Limites | Débit API, fin de simulation pendant une écriture | 6 tests, 0 échec |
| API et sessions | CSRF, rôles, visibilité, pause, fin, reset, verrouillage | 78 tests `php -S`, 0 échec |
| API modération | Rôle, CSRF, suppression concurrente, historique | 42 tests `php -S`, 0 échec |
| Même API derrière Apache | Protection `.htaccess`, sessions, permissions | 78 + 42 tests, 0 échec |
| Navigation et parcours | PO, Developer, formateur, Kanban, chat, rôles, aide par rôle, aide formateur, annonces, bilan | Playwright OK |
| Modales | Confirmation, annulation, saisie, alerte serveur, absence de dialogue natif | Playwright OK |
| Connexion | Erreurs, réseau, confirmation, annulation, CSRF, actualisation, onglets, déconnexion | Playwright OK |
| XSS | Chat et champs libres échappés, aucun script exécuté | Réussi |
| Accès direct | `datas/users.txt`, JSON et `includes/config.php` en `403` sous Apache | Réussi |
| `cron_events.php` en HTTP | `404` | Réussi |

Éléments du prompt non applicables au modèle actuel : création de User Story
par un étudiant, commentaire d’échange de rôle et justification libre de réponse
à un événement. Ces champs n’existent pas dans l’application et n’ont pas été
inventés. Tous les champs libres réellement présents sont couverts par les tests
de modération et de validation.

## D. Corrections effectuées

### D.1 Connexion en deux étapes

- **Fichiers** : `includes/auth.php`, `api.php`, `workflow.php`,
  `assets/login.js`.
- **Problème** : la connexion validait les identifiants et ouvrait immédiatement
  la session, sans confirmation explicite ni possibilité d’annuler proprement.
- **Correction** : ajout de `login_begin()`, `login_confirm()`, `login_cancel()`
  et d’une connexion en attente à durée limitée. La session authentifiée n’est
  créée qu’après confirmation ; l’identifiant de session et le jeton CSRF sont
  alors régénérés.
- **Justification** : la vérification et l’autorisation restent côté serveur ;
  la modale ne fait que déclencher une action protégée par CSRF et par un jeton
  de confirmation aléatoire.

### D.2 Expérience de connexion

- **Fichiers** : `assets/login.js`, `assets/style.css`, `workflow.php`.
- **Problème** : les erreurs rechargeaient la page et ne proposaient pas d’état
  d’attente ni de disparition temporisée.
- **Correction** : soumission AJAX, bouton désactivé avec libellé d’attente,
  message d’erreur non technique, effacement de la date de naissance,
  conservation du login, réactivation du bouton, disparition après cinq secondes
  et gestion des erreurs réseau ou réponses invalides.
- **Justification** : parcours plus simple et robuste, sans framework ni
  dépendance supplémentaire.

### D.3 Alertes et confirmations

- **Fichiers** : `assets/app.js`, `assets/style.css`, `workflow.php`.
- **Problème** : les dialogues natifs ne correspondaient pas à l’interface et les
  actions critiques pouvaient être déclenchées sans confirmation visuelle.
- **Correction** : modale unique `#ui-modal` pour les alertes, confirmations et
  saisies courtes ; confirmation avant archivage, mise à jour de tâche, blocage,
  événement, validation/refus, échange, pause/reprise/reset, Sprint Goal,
  annonce et modération. Les erreurs serveur sont routées vers une alerte
  graphique.
- **Justification** : cohérence visuelle et prévention des actions destructrices
  accidentelles, sans modifier les contrôles serveur.

### D.4 Documentation et tests

- **Fichiers** : `README.md`, `TESTS.md`, `RAPPORT_VERIFICATION.md`,
  `RAPPORT_MODERATION.md`, ce rapport.
- **Correction** : documentation du parcours de confirmation, des modales, des
  nouveaux tests et des limites LWS.

### D.5 Aide pédagogique par rôle

- **Fichiers** : `workflow.php`, `assets/app.js`, `assets/style.css`.
- **Problème** : l’aide mélangeait des consignes générales et des informations
  de supervision, sans parcours assez concret pour des étudiants débutants en
  Scrum.
- **Correction** : ajout de consignes actionnables pour le Product Owner, le
  Scrum Master et les Developers, d’un déroulé conseillé, de repères sur le
  backlog, les Story Points, la valeur, le Kanban et la Definition of Done, et
  d’une section « Trucs et astuces ». Les consignes de préparation, de
  supervision, de modération, d’historique, de bilan et de remise à zéro sont
  déplacées dans une modale **Aide formateur**, rendue uniquement dans la
  session du formateur.
- **Justification** : les étudiants trouvent directement ce qu’ils doivent faire
  selon leur rôle, tandis que les procédures réservées au formateur ne sont ni
  affichées ni envoyées aux étudiants.

### D.6 Terminal technique progressif

- **Fichiers** : `includes/game.php`, `assets/app.js`, `assets/style.css`.
- **Problème** : le terminal imposait 30 secondes d’attente, n’affichait pas
  réellement les indices et utilisait une aide statique. La commande exacte
  pouvait aussi être classée comme un extrait lorsqu’elle commençait par l’un
  des `partials`. Surtout, `applyState()` ouvrait automatiquement la modale
  dès qu’un défi terminal restait en attente, y compris au chargement, à la
  connexion, au rechargement ou après une synchronisation.
- **Correction** : suppression complète de l’ouverture automatique ; seul le
  clic explicite sur **Prête à valider** ouvre la modale, après vérification
  que le défi retourné correspond à l’US demandée. La fermeture réinitialise
  le contexte local. L’interface devient un terminal unique avec une seule
  ligne de saisie : suppression du champ « Commande », du bouton « Exécuter »
  et du libellé d’aide parasite, prompt dynamique, historique
  ↑/↓, focus et défilement. Le message d’attente du délai de 2 s disparaît
  automatiquement à l’échéance. Chaque nouvelle validation remet le niveau
  d’indices à zéro : les trois indices d’une tentative abandonnée ne sont pas
  réaffichés automatiquement et doivent être obtenus un par un.
  L’émulation prend en charge `/home/<login>`, `pwd`, `ls`, `ls -l`, `ls -la`
  et `help`, avec progression par exploration puis par les deux extraits
  cachés, trois indices conservés et `solution` déverrouillée seulement après
  le troisième indice. Le délai serveur de 30 secondes est supprimé ; le
  client applique un délai de 2 secondes tout en laissant la saisie éditable.
  La commande exacte est contrôlée avant les extraits.
- **Données métiers** : US05 trie effectivement les priorités puis les dates
  (`awk` + `sort -t ";" -k1,1 -k2,2`) et US22 utilise
  `vendor/bin/codecept --help`. Le bouton **Prête à valider** exige une US
  sélectionnée dont toutes les tâches actives sont terminées.
- **Confidentialité** : les payloads de démarrage et d’état ne contiennent ni
  `command` ni `partials` ; la commande n’est renvoyée qu’après une demande
  explicite `solution` au troisième indice. Aucune route API n’est ajoutée.
  Cette séparation ne garantit pas un secret fort : le bundle JavaScript et les
  données accessibles au navigateur restent inspectables.
- **Justification** : le terminal reste une simulation fermée, sans accès au
  système de fichiers, et le flux « Prête à valider » existant est conservé.
  Le quiz technique garde son interface, son délai et son endpoint. Il reste
  déclenché par le passage explicite d’une tâche à « Terminé », jamais par une
  synchronisation d’état.

## E. Non-régression

| Contrôle | Résultat |
|---|---:|
| Tests directs PHP | 491 / 491 |
| Tests directs de modération | 69 / 69 |
| Tests de limites | 6 / 6 |
| Tests HTTP sur `php -S` | 78 / 78 |
| Tests HTTP de modération sur `php -S` | 42 / 42 |
| Tests HTTP derrière Apache 2.4 + PHP 8.3 | 78 / 78 |
| Tests HTTP de modération derrière Apache | 42 / 42 |
| **Total automatisé** | **806 / 806** |
| Terminal technique (serveur, client, HTTP, navigateur) | 273 / 273 |
| Playwright général et complémentaire | OK |
| Playwright modération | OK |
| Playwright modales | OK |
| Playwright connexion | OK |
| Playwright terminal | OK |
| `php -l` sur tous les fichiers PHP | OK |
| `node --check assets/app.js` et `assets/login.js` | OK |
| Données livrées | JSON valides, état initial, aucun fichier de test |

## F. Points de vigilance

1. **LWS réel non testé** : `.htaccess`, version PHP, extensions, droits,
   HTTPS/cookie `Secure`, limite de requêtes, cron et sauvegarde restent à
   vérifier sur l’hébergement.
2. **`RESET_WORD`** : la valeur par défaut doit être remplacée avant toute
   utilisation réelle.
3. **Documents de conception** : tous les fichiers `prompt_*.txt` doivent rester
   hors de la racine Web, y compris `prompt_tests_finaux.txt`.
4. **Serveur intégré** : `php -S` ignore `.htaccess` et expose `datas/users.txt` ;
   il ne doit jamais être utilisé en production.
5. **Session de trois heures** : l’expiration réelle n’a pas été attendue pendant
   la campagne ; le comportement est couvert par la logique serveur mais pas par
   un test de bout en bout de trois heures.
6. **Fermeture réelle du navigateur** : non testée ; l’actualisation et un second
   onglet ont été validés.
7. **Charge multi-clients** : les tests de concurrence locaux passent, mais une
   charge réelle de trois équipes sur LWS n’a pas été mesurée. La réponse serveur
   `429` est testée ; le ralentissement du polling client en conditions réelles
   n’a pas été mesuré.
8. **Champs absents** : les tests ne couvrent pas la création de story étudiante
   ou les commentaires d’échange, car ces fonctionnalités n’existent pas.

## G. Conclusion

L’application est utilisable et fiable dans l’environnement local testé. Le
parcours d’authentification est complet, la confirmation est réellement
protégée côté serveur, les erreurs sont compréhensibles et les actions critiques
sont confirmées. Aucune régression connue n’a été introduite.

Elle ne peut pas être déclarée prête pour la production sans les vérifications
réelles sur LWS et sans le remplacement du mot de remise à zéro par défaut.
