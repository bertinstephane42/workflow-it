# Rapport de modération — WorkFlow IT

Date de la passe : 5 novembre 2026.
Périmètre : ajout de la modération des contenus par le formateur, tel que décrit
dans `prompt_amelioration.txt`, sans refonte de l’application Scrum existante.

## A. Résultat

| Élément | Valeur |
|---|---:|
| Vérifications automatisées réussies sur le code final | **806** |
| Échecs | **0** |
| Suites navigateur Playwright réussies | **7** |
| Vérifications terminal complémentaires | **273 / 273** |
| Contrôles Apache `403` sur `datas/` et `includes/` | **3/3** |
| `cron_events.php` appelé en HTTP | **404** |
| Anomalies bloquantes restantes | **0** |
| Tests sur un hébergement LWS réel | **non réalisés** |

Répartition : 491 tests directs existants, 69 tests directs de modération,
6 tests de limites, 78 tests HTTP sur `php -S`, 42 tests HTTP de modération sur
`php -S`, 78 tests HTTP derrière Apache 2.4 + PHP 8.3 et 42 tests HTTP de
modération derrière Apache. Les sept suites Playwright couvrent notamment le
parcours général, les contrôles complémentaires, la modération, les modales de
confirmation, la connexion en deux étapes et le terminal technique.

Les tests ont été exécutés dans des copies jetables sous `/tmp/opencode`. Les
données livrées n’ont pas servi de base de test et ont été régénérées à la fin.

## B. Champs libres couverts

| Contenu | Champs modérables | Suppression |
|---|---|---|
| Chat | `text` | logique : message marqué supprimé, texte vidé, filtré de l’API |
| Tâche | `titre`, `description`, `blocage` | titre/blocage remplacés par une mention neutre ; description vidée ; tâche conservée |
| Sprint Goal | `sprint_goal`, `sprint_goal_justification` | mention neutre ou valeur vide ; équipe et stories conservées |
| User Story | `refus_commentaire` | commentaire vidé ; story conservée |
| Annonce | `text` | logique : annonce marquée supprimée, texte vidé, filtrée de l’API |

Chaque ligne de la vue **Modération** identifie l’équipe, le type, l’identifiant,
l’auteur connu et la date. Les tâches indiquent leur auteur de création, leur
dernière modification et l’auteur du blocage lorsqu’ils existent. Les annonces
indiquent leur auteur et leur date. Le Sprint Goal indique son dernier auteur.

### Champs absents du modèle actuel

L’application ne permettait pas, avant cette modification, de créer une User
Story depuis un compte étudiant, de saisir une justification libre en réponse à
un événement ou de commenter une proposition d’échange de rôles. Ces champs ne
sont pas inventés pour les besoins du test. Tous les champs libres réellement
existants sont modérables, y compris la justification du Sprint Goal, qui était
acceptée par l’action serveur mais n’était pas conservée.

## C. Fonctionnement

- Un onglet **Modération** est ajouté à la navigation du formateur.
- Les boutons **Modifier** et **Supprimer** sont affichés uniquement au
  formateur, au plus près des contenus dans la vue dédiée et dans la vue des
  annonces.
- La modification ouvre une modale préremplie avec le texte courant. La
  soumission utilise `api.php?action=moderate_edit`.
- La suppression demande une confirmation puis utilise
  `api.php?action=moderate_delete`.
- Les textes corrigés sont immédiatement renvoyés par l’API et affichés avec un
  badge « Modifié par le formateur ».
- Les contenus supprimés disparaissent des réponses API et des vues étudiantes.
  Les objets métier nécessaires au jeu restent présents.
- L’historique conserve `moderation_edit` ou `moderation_delete`, l’équipe,
  l’auteur formateur, la date, l’ancien texte et le nouveau texte. La vue
  **Historique** affiche ces deux versions au formateur.
- Aucun étudiant ne reçoit les boutons de modération.

## D. Fichiers modifiés

- `includes/game.php` : règles de ciblage, validation, application des
  modifications/suppressions, métadonnées de modération, justification du
  Sprint Goal, traçabilité des auteurs de tâches, filtrage des contenus
  supprimés dans l’état public.
- `api.php` : actions `moderate_edit` et `moderate_delete` dans l’endpoint
  unique, avec les contrôles d’écriture existants.
- `assets/app.js` : vue de modération, modale, boutons, confirmation, badges,
  affichage ancien/nouveau dans l’historique, gestion de l’échappement, routage
  des erreurs vers la modale graphique.
- `assets/login.js` : connexion asynchrone, confirmation, annulation et gestion
  des erreurs sans rechargement.
- `assets/style.css` : styles de la vue, des champs, des badges, de la modale
  et du message de connexion temporisé.
- `workflow.php` : entrée de navigation formateur, modale de modification et
  aide.
- `README.md`, `TESTS.md`, `RAPPORT_VERIFICATION.md` et ce rapport : documentation
  et résultats.

## E. Sécurité et compatibilité

- Les actions de modération sont refusées aux étudiants côté serveur (`403`),
  même pour leur propre contenu.
- Le jeton CSRF, le rôle, l’équipe, le type, le champ, l’identifiant et l’index
  sont validés côté serveur.
- La limite globale de requêtes API reste active.
- Les écritures restent en JSON, avec `flock()`, verrou global multi-fichiers et
  écriture atomique.
- Le HTML/JS saisi est stocké comme texte et échappé dans le DOM ; un test
  Playwright vérifie qu’un `<img onerror=...>` ne crée aucun élément et
  n’exécute aucun script.
- Les contenus supprimés ne sont pas renvoyés aux étudiants, y compris après
  synchronisation AJAX.
- Deux suppressions simultanées du même contenu donnent une réussite et un
  `409`, sans corruption JSON ni réapparition.
- Aucun mot de passe ni date de naissance n’est ajouté aux réponses, à
  l’historique ou aux rapports.
- Aucune base de données, aucun framework, aucun build, aucun WebSocket et
  aucun changement d’horaire n’ont été introduits.

## F. Anomalies et écarts traités

1. La justification du Sprint Goal était reçue mais non stockée : elle est
   maintenant conservée, affichée au formateur et modérable.
2. Les tâches ne conservaient pas systématiquement l’auteur de création et
   l’auteur du blocage : ces informations sont ajoutées pour l’identification
   demandée.
3. La suppression d’une annonce devait être séparée de la suppression d’une
   équipe : l’historique utilise la cible réelle de l’annonce sans modifier les
   fichiers d’équipe.
4. Les boutons de modification sont masqués lorsque le champ est déjà supprimé,
   afin d’éviter une action impossible et un message d’erreur inutile.
5. Les métadonnées de modération d’un champ ne sont plus effacées lorsqu’un
   étudiant modifie un autre champ de la même tâche ou du même Sprint Goal.

Aucune anomalie bloquante n’a été détectée après les tests finaux.

## G. Limites connues

- Aucun test n’a été réalisé sur un hébergement **LWS réel**. Les points à
  vérifier sur place restent : application de `datas/.htaccess` et
  `includes/.htaccess`, version PHP et extensions, droits d’écriture,
  HTTPS/cookie `Secure`, limite de requêtes, cron, sauvegarde et parcours réel
  de connexion avec confirmation/annulation.
- Le serveur intégré `php -S` ne lit pas `.htaccess` : il ne doit pas être utilisé
  en production.
- La modération ne remplace pas les règles métier existantes : un étudiant peut
  continuer à modifier ses propres tâches, le Sprint Goal collectif ou une story
  selon les règles déjà en place. Le texte supprimé lui-même ne réapparaît pas
  automatiquement.
- Les champs libres absents du modèle (création de story étudiante, justification
  d’événement, commentaire d’échange) ne peuvent pas être modérés tant qu’ils
  n’existent pas.

## H. Conformité au prompt de modération

| Exigence | État |
|---|---|
| Modération réservée au formateur | conforme, contrôles serveur et absence de boutons étudiants |
| Tous les contenus libres existants | conforme pour chat, tâches, Sprint Goal, rétrospective, commentaires de refus et annonces |
| Identification équipe, type, identifiant, auteur, date | conforme dans la vue et l’historique |
| Modifier et supprimer les textes | conforme |
| Suppression logique et disparition des réponses normales | conforme |
| Historique avec ancien/nouveau texte, date et action | conforme, vue formateur |
| CSRF, rôles, limite de requêtes | conforme |
| JSON, `flock()`, écritures atomiques, endpoint unique | conforme |
| Serveur PHP autoritaire | conforme |
| Synchronisation AJAX sans réapparition | conforme, testée |
| Pas de régression visuelle | conforme, suites Playwright |
| Documents de conception non déployés | à respecter lors du déploiement ; `prompt_amelioration.txt` est également exclu |

## I. Conclusion

La fonctionnalité de modération est opérationnelle et testée localement. La
validation finale du déploiement reste conditionnée aux vérifications réelles
sur l’hébergement LWS ; ce rapport ne constitue pas une validation de production.
