# Rapport de vérification — WorkFlow IT

Date de la dernière passe : 5 novembre 2026.
Périmètre : application `workflow-it/`, spécifications `prompt_scrum.txt`,
consignes `prompt_verification.txt` et ajout de modération décrit dans
`prompt_amelioration.txt`.

## A. Résultat de l’audit

| Élément | Valeur |
|---|---:|
| Fichiers examinés dans le livrable | 32 |
| Fichiers PHP vérifiés | 8 |
| Vérifications automatisées PHP/HTTP réussies | **806** |
| Suites navigateur Playwright réussies | 7 |
| Scénarios de concurrence validés | 6 |
| Anomalies/écarts détectés | 22 |
| Anomalies/écarts corrigés | 22 |
| Améliorations UX ajoutées | 2 |
| Anomalies bloquantes restantes | 0 |

Les 806 vérifications se répartissent ainsi : 491 tests directs PHP, 69 tests
directs de modération, 6 tests de limites, 78 tests HTTP sur serveur PHP local,
42 tests HTTP de modération sur `php -S`, 78 tests HTTP derrière Apache 2.4 +
PHP 8.3 et 42 tests HTTP de modération derrière Apache. Les tests ont été
exécutés sur des copies jetables sous `/tmp/opencode` ; les données livrées ont
ensuite été régénérées. L’audit initial avait totalisé 621 vérifications ; la
passe complémentaire de modération ajoute 153 vérifications (69 + 42 + 42).
Le terminal technique totalise **273 vérifications** : 134 côté serveur, 52
côté client sur le code réel, 26 en HTTP et 61 en navigateur.

La passe corrective du 5 novembre 2026 a rejoué le lint PHP/JS et ces quatre
suites terminales : **273 / 273, 0 échec**. Les 806 vérifications PHP/HTTP
ci-dessus restent le résultat de la passe antérieure ; elles n’ont pas été
rejouées dans cette passe ciblée et ne sont donc pas présentées comme une
validation du correctif terminal. L’absence de `command` / `partials` dans les
payloads limite les fuites accidentelles, mais ne constitue pas un secret fort :
le bundle JavaScript et les données accessibles au navigateur restent
inspectables.

## B. Corrections réalisées

1. **Navigation des onglets** : le gestionnaire utilisait `matches('[data-view]')`
   et quittait la vue après certaines actions. Corrigé avec
   `closest('button[data-view]')` (`assets/app.js`). Test : changement de
   priorité et sélection depuis le Backlog ; la vue reste sur le Backlog.
2. **Contrôles après 14 h 35** : les boutons de modification pouvaient rester
   actifs côté client. Corrigé par `userCanWrite()` et les contrôles de phase
   (`assets/app.js`, `includes/game.php`). Test : contrôles absents après la fin.
3. **Phase non revérifiée sous verrou** : une requête pouvait passer en phase
   `running` puis écrire après la fin. Phase revérifiée dans les callbacks
   verrouillés (`includes/game.php`). Test : requête chevauchant la fin refusée
   en 409.
4. **Écriture partielle à la fin** : la révision et le contenu d’équipe ne
   devaient pas changer si la fin survenait pendant le traitement. Vérifié par
   un test de course ; aucune écriture, révision inchangée.
5. **Cache après reset** : ajout d’une `generation` de session et invalidation
   des caches clients (`includes/game.php`, `assets/app.js`). Test : génération
   différente après reset, état rechargé.
6. **Annonces locales après reset** : les clés de stockage local incluent la
   génération, pour ne pas réafficher une ancienne annonce. Test navigateur et
   test de génération.
7. **Badge d’échange de rôle** : le badge de proposition en attente n’était pas
   rafraîchi. Corrigé dans le rendu et la synchronisation. Test Playwright :
   badge à `1`, puis proposition visible.
8. **Blocage par le PO et le Scrum Master** : seul un Developer pouvait
   signaler un blocage. Ajout de `report_blockage` pour tous les membres
   (`api.php`, `includes/game.php`, `assets/app.js`). Test Playwright : blocage
   signalé par le PO et affiché.
9. **Historique du déblocage** : ajout de l’entrée `task_unblock` lors du
   déblocage. Test direct : type présent dans l’historique.
10. **Tâche sur une story « à valider »** : la création d’une tâche doit
    remettre la story en travail. Corrigé et testé.
11. **Événement `add_task`** : la conséquence pouvait ajouter une tâche sans
    story sélectionnée ou dans un état incohérent. Conditions et remise en
    travail corrigées. Test des conséquences d’événement.
12. **Éligibilité des événements RGPD/retard** : les conditions pouvaient
    proposer un événement inapplicable. Filtrage corrigé. Test du catalogue et
    des conséquences.
13. **Réouverture d’une story par événement** : les dates de validation/refus
    restaient présentes. Nettoyage ajouté. Test direct.
14. **Participants du Sprint Goal** : un type ou un participant invalide
    pouvait être accepté. Validation stricte côté serveur. Test des rejets.
15. **Composition après échange de rôles** : l’écriture des deux membres et de
    la composition devait être atomique. Corrigé et testé, y compris les
    propositions concurrentes.
16. **CSP** : ajout de `object-src 'none'` aux en-têtes HTML. Vérifié par les
    tests HTTP.
17. **Erreurs en production** : `display_errors` désactivé, erreurs détaillées
    uniquement dans les journaux. Vérifié : aucune trace de pile dans les
    réponses HTTP, y compris les erreurs 401/400/500.
18. **Favicon 404** : ajout d’une icône `data:` pour supprimer la requête
    parasite. Vérifié dans les parcours navigateur.
19. **Documentation** : correction d’une phrase contradictoire du `README.md`
    sur l’accès avant 13 h 00, ajout des consignes de non-publication des
    fichiers de conception et mise à jour de `TESTS.md`.
20. **Connexion UX et sécurité** : vérification AJAX en deux étapes, aucune
    session applicative avant confirmation, modale « Rejoindre / Annuler »,
    jeton de confirmation à durée limitée, CSRF, régénération de session à la
    confirmation, erreur sans rechargement, date de naissance effacée, login
    conservé, bouton réactivé et message masqué après cinq secondes
    (`includes/auth.php`, `api.php`, `workflow.php`, `assets/login.js`).
21. **Alertes et confirmations** : remplacement de tous les dialogues natifs par
    la modale unique `#ui-modal`, avec confirmation avant les actions
    destructrices ou conséquentes et routage des erreurs serveur vers une alerte
    graphique (`assets/app.js`, `assets/style.css`, `workflow.php`).
22. **Déclenchement et interface du terminal** : `applyState()` ouvrait la
    modale dès qu’un défi terminal était présent dans l’état. L’ouverture
    automatique est supprimée ; seul le clic sur **Prête à valider** ouvre la
    modale, avec vérification de la User Story retournée. La fermeture efface
    le contexte local. L’interface utilise une seule ligne de saisie intégrée :
    le champ « Commande », le bouton « Exécuter » et le libellé d’aide parasite
    sont supprimés, au profit du prompt, de l’historique
    ↑/↓, du focus et du défilement. Le message « Patientez encore… » disparaît
    automatiquement à la fin du délai de 2 s. Chaque clic sur **Prête à
    valider** démarre une validation à zéro : le serveur remet le niveau
    d’indices à 0 pour l’US, afin qu’une validation ultérieure ne réaffiche
    jamais automatiquement les trois indices d’une tentative abandonnée. La
    disponibilité du bouton exige une US sélectionnée et toutes ses tâches
    actives terminées.
    Testé par les suites terminales (273 vérifications, 0 échec) et le parcours
    navigateur US01/US03/US05/US22.

Le déclencheur `cron_events.php` a également été ajouté comme amélioration
d’exploitation facultative : il permet aux événements d’avancer sans navigateur
connecté. Ce n’est pas une correction d’une anomalie fonctionnelle.

## C. État de conformité

| Fonctionnalité | Résultat du test | Statut | Commentaire |
|---|---|---|---|
| Authentification, sessions, CSRF | Vérification en deux étapes, confirmation explicite, annulation, régénération, jetons invalides, déconnexion | Conforme | Cookies `HttpOnly`, `SameSite=Lax`, `Secure` sous HTTPS ; aucun accès avant confirmation |
| Limitation des tentatives | 5 échecs puis verrouillage, bon mot de passe refusé pendant le délai | Conforme | Test HTTP |
| Rôles et échanges | PO/SM/DEV, propositions, acceptation/refus, expiration | Conforme | Composition toujours valide |
| Backlog et estimations | 16 stories, priorités, ordre, sélection, estimation | Conforme | Trois copies indépendantes |
| Tâches, Kanban, DoD | Création, déplacement, blocage, déblocage, archivage, validation/refus | Conforme | Blocage ouvert à tous les membres |
| Sprint Goal | Participants, justification, mise à jour collective | Conforme | Validation serveur |
| Événements | Catalogue, déclenchement, conséquences, réponse, historique | Conforme | Cron LWS réel non testé |
| Chat et annonces | Anti-flood, visibilité par équipe, annonces globales/ciblées | Conforme | XSS échappé |
| Supervision formateur | Vue globale, détails, pause/reprise, annonces, historique, bilan | Conforme | Pas de classement ni de note |
| Horloge et phases | Ouverture, découverte, simulation, pause, fin | Conforme | Horloge serveur Europe/Paris |
| Fin de simulation | Blocage des écritures, bilan disponible | Conforme | Requête concurrente à la fin refusée |
| Remise à zéro | Étudiants interdits, mot requis, reset recréé | Conforme | Génération et données réinitialisées |
| Sécurité HTTP et données | 403 sur `datas/` et `includes/` sous Apache | Conforme en local | À confirmer sur LWS |
| Limite de débit | 25 req/s, 429 au-delà | Conforme en local | Limites LWS à confirmer |
| Synchronisation | Polling, reprise, invalidation, pas de polls parallèles | Conforme fonctionnellement | Pas de test de charge multi-sites |
| Alertes et confirmations | Modale graphique, annulation/confirmation, erreurs serveur, actions critiques | Conforme | Aucun dialogue natif ; test Playwright dédié |
| Documentation | Installation, exploitation, tests, limites | Conforme | README et TESTS.md à jour |

## D. Vérifications techniques

- `php -l` : 7 fichiers PHP, aucune erreur.
- `node --check assets/app.js` et `node --check assets/login.js` : aucune erreur.
- JSON : les 8 fichiers de `datas/` sont valides ; aucun fichier `.tmp_*`
  résiduel ; écritures atomiques et verrous testés.
- Concurrence : 20 × 50 incréments = 1000 ; 12 tâches simultanées avec
  identifiants uniques ; 4 messages simultanés ; événement concurrent à
  8 créations de tâches ; deux propositions d’échange simultanées, une seule
  en attente.
- Sécurité : CSRF, sessions, limitation de connexion, limitation API, XSS,
  absence de dates de naissance dans les réponses, `.htaccess` Apache.
- Synchronisation : horloge serveur, rechargement après reset, badge d’échange,
  pause/reprise, contrôles après la fin.
- Fonctionnel : sept suites Playwright, dont parcours PO, Developer, formateur,
  Kanban, chat, rôles, aide, annonces, historique, bilan, blocage PO, XSS, état
  final, modération, modales de confirmation et connexion en deux étapes.
- Exploitation : `cron_events.php` fonctionne en CLI et répond 404 en HTTP.

## E. Points restant à vérifier sur LWS

1. Application réelle de `datas/.htaccess` et `includes/.htaccess` : `403` sur
   `datas/users.txt`, les JSON et `includes/config.php`.
2. Droits, propriétaire et écriture de `datas/` après téléversement FTP.
3. Version PHP et extensions réellement disponibles sur l’hébergement.
4. Limite de requêtes LWS et comportement du seuil de 25 req/s.
5. Configuration réelle de la tâche cron `cron_events.php`.
6. HTTPS, redirection HTTP → HTTPS et cookie `Secure`.
7. Charge réelle avec trois équipes et plusieurs navigateurs simultanés.
8. Sauvegarde et restauration de `datas/` avant une séance.
9. Retrait de tous les fichiers `prompt_*.txt`, dont `prompt_scrum.txt`,
   `prompt_verification.txt`, `prompt_amelioration.txt` et
   `prompt_tests_finaux.txt`, de la racine Web.
10. Connexion réelle avec confirmation, annulation et expiration du jeton sur
    plusieurs navigateurs et appareils.

## F. Conclusion

L’application est **prête sous réserve des tests sur LWS**.

Tous les tests locaux obligatoires passent, aucune anomalie bloquante connue ne
subsiste, et les données livrées ont été régénérées dans un état propre :
16 User Stories par équipe, aucune tâche, aucun message, aucun échange, aucune
annonce et un historique limité à l’initialisation.

Elle ne peut pas être déclarée « prête à être déployée sans réserve » avant
d’avoir vérifié sur LWS la protection Apache, les droits d’écriture, HTTPS, la
limite de requêtes et le cron. Avant la séance, remplacer également la valeur
par défaut de `RESET_WORD` dans `config.php` et ne jamais utiliser `php -S` en
production : le serveur intégré ignore `.htaccess` et laisse `datas/users.txt`
accessible.


## G. Passe complémentaire — modération des contenus

La fonctionnalité demandée dans `prompt_amelioration.txt` a été ajoutée puis
retestée sur le code final. Résultats détaillés dans `RAPPORT_MODERATION.md`.

| Contrôle | Résultat |
|---|---:|
| Tests directs de modération | 69 / 69 |
| Tests HTTP de modération sur `php -S` | 42 / 42 |
| Tests HTTP de modération derrière Apache | 42 / 42 |
| Suite Playwright de modération | OK |
| Double suppression concurrente | 1 succès + 1 conflit 409 |
| Contrôles Apache `403` (`datas/users.txt`, `datas/team_1.json`, `includes/config.php`) | 3 / 3 |
| `cron_events.php` en HTTP | 404 |

La modération est réservée au formateur, couvre les champs libres existants,
filtre les contenus supprimés des réponses étudiantes et conserve dans
l’historique l’ancien texte, le nouveau texte, la date et l’action. Aucun test
réel sur l’hébergement LWS n’a été effectué : les réserves de la section E
restent valables.

## H. Passe complémentaire — connexion, alertes et confirmations

Cette passe répond au prompt de tests finaux côté utilisateur et remplace les
dialogues natifs par l’interface graphique existante.

| Contrôle | Résultat |
|---|---:|
| Tests directs PHP | 491 / 491 |
| Tests directs de modération | 69 / 69 |
| Tests de limites | 6 / 6 |
| Tests HTTP sur `php -S` | 78 / 78 |
| Tests HTTP de modération sur `php -S` | 42 / 42 |
| Tests HTTP derrière Apache 2.4 + PHP 8.3 | 78 / 78 |
| Tests HTTP de modération derrière Apache | 42 / 42 |
| **Total automatisé PHP/HTTP** | **806 / 806** |
| Terminal technique (serveur, client, HTTP, navigateur) | 273 / 273 |
| Playwright général + complémentaire | OK |
| Playwright modération | OK |
| Playwright modales | OK |
| Playwright connexion | OK |
| Playwright terminal | OK |

La connexion est vérifiée en AJAX puis confirmée dans une modale. Avant
confirmation, l’API d’état répond `401`, aucune coquille applicative n’est
rendue et le polling ne démarre pas. L’annulation supprime la connexion en
attente. Le jeton de confirmation expire après 120 secondes et exige le jeton
CSRF. En cas d’erreur, la page ne se recharge pas, la date de naissance est
effacée, le login est conservé, le bouton redevient actif et le message
disparaît après cinq secondes. Les tests Playwright vérifient également
l’absence de tout dialogue natif `alert` / `confirm` / `prompt`.

L’aide a été enrichie pour les débutants : consignes par rôle, déroulé conseillé,
repères Scrum et Definition of Done, trucs et astuces. Une seconde modale
**Aide formateur** est rendue uniquement dans la session du formateur et
n’apparaît pas dans le HTML étudiant. Les tests Playwright vérifient les deux
modales, leur fermeture et l’affichage mobile.

Le détail scénario par scénario, les tests non exécutés et les points de
vigilance figurent dans `RAPPORT_TESTS_FINAUX.md`. Aucune validation de
production LWS n’est incluse dans cette passe.
