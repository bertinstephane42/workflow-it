<?php
/**
 * Banque de questions techniques pour le challenge de passage à « Terminé ».
 *
 * Deux questions sont associées à chaque User Story existante.
 * La réponse correcte n'est jamais transmise au navigateur avant validation.
 */

declare(strict_types=1);

$CHALLENGE_QUESTIONS = [
    'US01' => [
        [
            'id' => 'US01-Q1',
            'question' => 'Un collaborateur saisit un identifiant inconnu et un mot de passe quelconque. Quel comportement l’application doit-elle adopter ?',
            'options' => [
                'Afficher un message générique sans indiquer si l’identifiant existe.',
                'Indiquer que l’identifiant est inconnu pour aider l’utilisateur.',
                'Créer automatiquement un compte temporaire.',
                'Rediriger vers le formulaire de création de compte.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : un message générique évite de révéler l’existence des comptes et limite l’énumération d’identifiants.',
                'Cette réponse expose l’existence des comptes et facilite une attaque par énumération.',
                'La création automatique d’un compte n’est pas une réponse de connexion et créerait un compte non autorisé.',
                'La redirection vers une création de compte n’est pas le comportement attendu pour une tentative de connexion.',
            ],
        ],
        [
            'id' => 'US01-Q2',
            'question' => 'Après plusieurs tentatives de connexion échouées, quelle mesure est la plus pertinente pour protéger le service ?',
            'options' => [
                'Ralentir ou limiter les tentatives pour réduire le risque de force brute.',
                'Afficher le mot de passe attendu après trois échecs.',
                'Désactiver définitivement le compte après une erreur.',
                'Ignorer les échecs car l’application est pédagogique.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : le ralentissement ou la limitation des tentatives réduit le risque de force brute sans bloquer l’utilisateur légitime.',
                'Afficher le mot de passe attendu compromettrait totalement la sécurité.',
                'Désactiver définitivement le compte après une erreur serait disproportionné et bloquerait un utilisateur légitime.',
                'Ignorer les échecs n’est pas acceptable : même dans une simulation, les règles de sécurité doivent être crédibles.',
            ],
        ],
    ],
    'US02' => [
        [
            'id' => 'US02-Q1',
            'question' => 'Une demande est créée avec un titre vide et une catégorie absente. Que doit faire le système ?',
            'options' => [
                'Refuser la création et indiquer les champs obligatoires.',
                'Créer la demande avec des valeurs par défaut.',
                'Enregistrer la demande puis demander la complétion plus tard.',
                'Accepter la demande si l’utilisateur est connecté.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : les champs obligatoires doivent être validés avant l’enregistrement.',
                'Des valeurs par défaut masqueraient une saisie incomplète et fausseraient le suivi.',
                'Enregistrer une demande incomplète dégrade la qualité des données et le traitement.',
                'Le fait d’être connecté n’exempte pas de respecter les règles de validation.',
            ],
        ],
        [
            'id' => 'US02-Q2',
            'question' => 'Après la création d’une demande, quel élément est indispensable pour le suivi par l’auteur ?',
            'options' => [
                'Un identifiant unique et visible dans la liste de l’auteur.',
                'Le mot de passe de l’auteur dans l’accusé de création.',
                'Un statut initial aléatoire.',
                'La suppression automatique après 24 heures.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : l’identifiant unique permet de retrouver et de suivre la demande.',
                'Le mot de passe ne doit jamais être affiché dans un accusé de création.',
                'Un statut initial aléatoire rendrait le suivi incohérent.',
                'La suppression automatique empêcherait le suivi de la demande.',
            ],
        ],
    ],
    'US03' => [
        ['id'=>'US03-Q1','question'=>'Une catégorie est modifiée après la création d’une demande. Que doit garantir le système ?','options'=>['Conserver la catégorie enregistrée et afficher la nouvelle valeur de façon cohérente.','Supprimer la catégorie de toutes les demandes existantes.','Ignorer la modification pour les anciennes demandes.','Changer automatiquement la catégorie de toutes les demandes.'],'correct'=>0,'explanations'=>['Bonne réponse : la donnée historique doit rester cohérente.','Supprimer la catégorie casserait le suivi des demandes existantes.','Ignorer la modification créerait une incohérence visible.','Changer automatiquement toutes les demandes fausserait l’historique.']],
        ['id'=>'US03-Q2','question'=>'Un utilisateur choisit une catégorie invalide envoyée manuellement. Que doit faire l’application ?','options'=>['Refuser la valeur et proposer uniquement les catégories autorisées.','Accepter la valeur pour ne pas bloquer l’utilisateur.','Convertir la valeur en catégorie par défaut.','Enregistrer la valeur puis la corriger plus tard.'],'correct'=>0,'explanations'=>['Bonne réponse : la liste doit être validée côté serveur.','Accepter une valeur invalide compromet l’intégrité des données.','Convertir silencieusement masque l’erreur.','Enregistrer pour corriger plus tard dégrade la qualité.']],
    ],
    'US04' => [
        ['id'=>'US04-Q1','question'=>'Un utilisateur demande à voir ses demandes. Quelle règle d’accès doit s’appliquer ?','options'=>['Il ne voit que ses propres demandes et leur statut.','Il voit toutes les demandes de l’entreprise.','Il voit les demandes de son service uniquement.','Il voit les demandes terminées de tous les utilisateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : l’isolation par utilisateur protège la confidentialité.','Voir toutes les demandes violerait la confidentialité.','Le service seul ne suffit pas : l’auteur doit rester maître de ses données.','Voir les demandes terminées de tous violerait aussi la confidentialité.']],
        ['id'=>'US04-Q2','question'=>'Le statut d’une demande est mis à jour par un technicien. Que doit voir l’auteur ?','options'=>['Le nouveau statut dans son suivi personnel.','L’ancien statut jusqu’à la fin de la journée.','Un message générique sans détail.','Rien, car seul le technicien suit la demande.'],'correct'=>0,'explanations'=>['Bonne réponse : l’auteur doit être informé de l’avancement.','Garder l’ancien statut rend l’information obsolète.','Un message générique manque de précision.','L’auteur doit pouvoir suivre sa demande.']],
    ],
    'US05' => [
        ['id'=>'US05-Q1','question'=>'Un technicien consulte les demandes en attente. Comment doivent-elles être présentées ?','options'=>['Demandes non assignées, triées par priorité puis par date.','Toutes les demandes, y compris celles déjà assignées.','Les demandes triées uniquement par date.','Les demandes de la catégorie la plus récente.'],'correct'=>0,'explanations'=>['Bonne réponse : cela évite de reprendre une demande déjà prise en charge.','Inclure les demandes assignées créerait des doublons de traitement.','Trier uniquement par date ignore la priorité.','La catégorie la plus récente n’est pas un critère pertinent.']],
        ['id'=>'US05-Q2','question'=>'Une demande non assignée est ouverte depuis le détail. Quel contrôle est nécessaire ?','options'=>['Vérifier que le technicien est autorisé à consulter cette demande.','Afficher la demande sans contrôle.','Autoriser uniquement le responsable.','Refuser l’accès si la demande est urgente.'],'correct'=>0,'explanations'=>['Bonne réponse : les droits doivent être vérifiés avant affichage.','Afficher sans contrôle exposerait des données.','Le responsable n’est pas le seul à pouvoir consulter.','L’urgence ne justifie pas un refus d’accès.']],
    ],
    'US06' => [
        ['id'=>'US06-Q1','question'=>'Un technicien change le statut d’une demande. Que doit enregistrer le système ?','options'=>['Le nouveau statut avec sa date et son auteur.','Le nouveau statut sans historique.','L’ancien statut uniquement.','Le statut dans un fichier temporaire.'],'correct'=>0,'explanations'=>['Bonne réponse : la traçabilité exige la date et l’auteur.','Sans historique, le suivi devient impossible.','Conserver seulement l’ancien statut perd l’information nouvelle.','Un fichier temporaire ne garantit pas la persistance.']],
        ['id'=>'US06-Q2','question'=>'Le collaborateur consulte sa demande après un changement de statut. Que doit-il voir ?','options'=>['Le nouveau statut dans le suivi.','Le statut initial jusqu’à la prochaine synchronisation.','Un message indiquant que le statut est indisponible.','Rien, car le technicien est seul à suivre la demande.'],'correct'=>0,'explanations'=>['Bonne réponse : l’auteur doit voir l’avancement.','Garder l’ancien statut rend l’information obsolète.','Le statut n’est pas indisponible.','L’auteur doit suivre sa demande.']],
    ],
    'US07' => [
        ['id'=>'US07-Q1','question'=>'Un responsable affecte une demande à un technicien. Que doit vérifier le système ?','options'=>['Que le technicien fait partie de l’équipe et est actif.','Que le technicien est connecté au moment de l’affectation.','Que la demande est urgente.','Que le responsable est aussi technicien.'],'correct'=>0,'explanations'=>['Bonne réponse : l’affectation doit cibler un membre valide de l’équipe.','La connexion instantanée n’est pas nécessaire pour l’affectation.','L’urgence n’est pas un critère d’affectation.','Le responsable n’a pas à être technicien.']],
        ['id'=>'US07-Q2','question'=>'Après l’affectation, où le technicien doit-il retrouver la demande ?','options'=>['Dans sa liste de demandes à traiter.','Dans la liste de toutes les demandes de l’entreprise.','Dans les demandes archivées.','Dans le chat de l’équipe.'],'correct'=>0,'explanations'=>['Bonne réponse : l’affectation doit rendre la demande visible au technicien.','La liste de toutes les demandes violerait les droits.','Les demandes archivées ne sont pas actives.','Le chat n’est pas le lieu de suivi des demandes.']],
    ],
    'US03' => [
        ['id'=>'US03-Q1','question'=>'Une catégorie est modifiée après la création d’une demande. Que doit garantir le système ?','options'=>['Conserver la catégorie enregistrée et afficher la nouvelle valeur de façon cohérente.','Supprimer la catégorie de toutes les demandes existantes.','Ignorer la modification pour les anciennes demandes.','Changer automatiquement la catégorie de toutes les demandes.'],'correct'=>0,'explanations'=>['Bonne réponse : la donnée historique doit rester cohérente.','Supprimer la catégorie casserait le suivi des demandes existantes.','Ignorer la modification créerait une incohérence visible.','Changer automatiquement toutes les demandes fausserait l’historique.']],
        ['id'=>'US03-Q2','question'=>'Un utilisateur choisit une catégorie invalide envoyée manuellement. Que doit faire l’application ?','options'=>['Refuser la valeur et proposer uniquement les catégories autorisées.','Accepter la valeur pour ne pas bloquer l’utilisateur.','Convertir la valeur en catégorie par défaut.','Enregistrer la valeur puis la corriger plus tard.'],'correct'=>0,'explanations'=>['Bonne réponse : la liste doit être validée côté serveur.','Accepter une valeur invalide compromet l’intégrité des données.','Convertir silencieusement masque l’erreur.','Enregistrer pour corriger plus tard dégrade la qualité.']],
    ],
    'US04' => [
        ['id'=>'US04-Q1','question'=>'Un utilisateur demande à voir ses demandes. Quelle règle d’accès doit s’appliquer ?','options'=>['Il ne voit que ses propres demandes et leur statut.','Il voit toutes les demandes de l’entreprise.','Il voit les demandes de son service uniquement.','Il voit les demandes terminées de tous les utilisateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : l’isolation par utilisateur protège la confidentialité.','Voir toutes les demandes violerait la confidentialité.','Le service seul ne suffit pas : l’auteur doit rester maître de ses données.','Voir les demandes terminées de tous violerait aussi la confidentialité.']],
        ['id'=>'US04-Q2','question'=>'Le statut d’une demande est mis à jour par un technicien. Que doit voir l’auteur ?','options'=>['Le nouveau statut dans son suivi personnel.','L’ancien statut jusqu’à la fin de la journée.','Un message générique sans détail.','Rien, car seul le technicien suit la demande.'],'correct'=>0,'explanations'=>['Bonne réponse : l’auteur doit être informé de l’avancement.','Garder l’ancien statut rend l’information obsolète.','Un message générique manque de précision.','L’auteur doit pouvoir suivre sa demande.']],
    ],
    'US05' => [
        ['id'=>'US05-Q1','question'=>'Un technicien consulte les demandes en attente. Comment doivent-elles être présentées ?','options'=>['Demandes non assignées, triées par priorité puis par date.','Toutes les demandes, y compris celles déjà assignées.','Les demandes triées uniquement par date.','Les demandes de la catégorie la plus récente.'],'correct'=>0,'explanations'=>['Bonne réponse : cela évite de reprendre une demande déjà prise en charge.','Inclure les demandes assignées créerait des doublons de traitement.','Trier uniquement par date ignore la priorité.','La catégorie la plus récente n’est pas un critère pertinent.']],
        ['id'=>'US05-Q2','question'=>'Une demande non assignée est ouverte depuis le détail. Quel contrôle est nécessaire ?','options'=>['Vérifier que le technicien est autorisé à consulter cette demande.','Afficher la demande sans contrôle.','Autoriser uniquement le responsable.','Refuser l’accès si la demande est urgente.'],'correct'=>0,'explanations'=>['Bonne réponse : les droits doivent être vérifiés avant affichage.','Afficher sans contrôle exposerait des données.','Le responsable n’est pas le seul à pouvoir consulter.','L’urgence ne justifie pas un refus d’accès.']],
    ],
    'US06' => [
        ['id'=>'US06-Q1','question'=>'Un technicien change le statut d’une demande. Que doit enregistrer le système ?','options'=>['Le nouveau statut avec sa date et son auteur.','Le nouveau statut sans historique.','L’ancien statut uniquement.','Le statut dans un fichier temporaire.'],'correct'=>0,'explanations'=>['Bonne réponse : la traçabilité exige la date et l’auteur.','Sans historique, le suivi devient impossible.','Conserver seulement l’ancien statut perd l’information nouvelle.','Un fichier temporaire ne garantit pas la persistance.']],
        ['id'=>'US06-Q2','question'=>'Le collaborateur consulte sa demande après un changement de statut. Que doit-il voir ?','options'=>['Le nouveau statut dans le suivi.','L’ancien statut jusqu’à la prochaine synchronisation.','Un message indiquant que le statut est indisponible.','Rien, car seul le technicien suit la demande.'],'correct'=>0,'explanations'=>['Bonne réponse : l’auteur doit voir l’avancement.','Garder l’ancien statut rend l’information obsolète.','Le statut n’est pas indisponible.','L’auteur doit pouvoir suivre sa demande.']],
    ],
    'US07' => [
        ['id'=>'US07-Q1','question'=>'Un responsable affecte une demande à un technicien. Que doit vérifier le système ?','options'=>['Que le technicien fait partie de l’équipe et est actif.','Que le technicien est connecté au moment de l’affectation.','Que la demande est urgente.','Que le responsable est aussi technicien.'],'correct'=>0,'explanations'=>['Bonne réponse : l’affectation doit cibler un membre valide de l’équipe.','La connexion instantanée n’est pas nécessaire pour l’affectation.','L’urgence n’est pas un critère d’affectation.','Le responsable n’a pas à être technicien.']],
        ['id'=>'US07-Q2','question'=>'Après l’affectation, où le technicien doit-il retrouver la demande ?','options'=>['Dans sa liste de demandes à traiter.','Dans la liste de toutes les demandes de l’entreprise.','Dans les demandes archivées.','Dans le chat de l’équipe.'],'correct'=>0,'explanations'=>['Bonne réponse : l’affectation doit rendre la demande visible au technicien.','La liste de toutes les demandes violerait les droits.','Les demandes archivées ne sont pas actives.','Le chat n’est pas le lieu de suivi des demandes.']],
    ],
    'US08' => [
        ['id'=>'US08-Q1','question'=>'Un collaborateur doit être informé d’un changement de statut. Quelle information doit contenir la notification ?','options'=>['La demande concernée et le nouveau statut.','Le mot de passe du collaborateur.','Le nom complet de tous les techniciens.','Un lien externe vers un site tiers.'],'correct'=>0,'explanations'=>['Bonne réponse : la notification doit être utile et ciblée.','Le mot de passe ne doit jamais être transmis.','La liste complète des techniciens n’est pas pertinente.','Un lien externe non maîtrisé n’est pas approprié.']],
        ['id'=>'US08-Q2','question'=>'Un collaborateur consulte ses notifications. Que doit-il pouvoir faire ?','options'=>['Les consulter et les marquer comme lues.','Les supprimer définitivement pour tous les utilisateurs.','Les modifier pour changer le statut.','Les envoyer à d’autres collaborateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : la consultation et le marquage comme lu sont attendus.','Supprimer pour tous violerait le partage.','Modifier une notification ne doit pas changer le statut.','Renvoyer à d’autres n’est pas prévu.']],
    ],
    'US09' => [
        ['id'=>'US09-Q1','question'=>'Un collaborateur ajoute un commentaire à une demande. Quelle validation minimale est nécessaire ?','options'=>['Le commentaire doit être non vide.','Le commentaire peut être vide si l’auteur est connecté.','Le commentaire doit contenir un mot de passe.','Le commentaire est facultatif sans limite.'],'correct'=>0,'explanations'=>['Bonne réponse : un commentaire vide n’apporte pas d’information.','Être connecté ne justifie pas un commentaire vide.','Le mot de passe n’a rien à faire dans un commentaire.','Une limite de taille et de contenu est nécessaire.']],
        ['id'=>'US09-Q2','question'=>'Les commentaires d’une demande sont affichés. Quelles informations doivent accompagner chaque commentaire ?','options'=>['Son auteur et sa date.','Le mot de passe de l’auteur.','L’adresse IP de l’auteur.','Le navigateur utilisé.'],'correct'=>0,'explanations'=>['Bonne réponse : l’auteur et la date assurent la traçabilité.','Le mot de passe ne doit jamais être affiché.','L’adresse IP n’est pas utile et pose un problème de confidentialité.','Le navigateur n’est pas pertinent pour le suivi.']],
    ],
    'US10' => [
        ['id'=>'US10-Q1','question'=>'Un responsable souhaite repérer rapidement les demandes urgentes. Quel indicateur est le plus pertinent ?','options'=>['Un indicateur visible dans la liste et le détail.','Une couleur aléatoire pour chaque demande.','Un tri uniquement par date.','Un numéro plus grand que les autres.'],'correct'=>0,'explanations'=>['Bonne réponse : l’urgence doit être visible sans ambiguïté.','Une couleur aléatoire ne transmet aucune information.','Le tri par date ignore l’urgence.','Le numéro n’indique pas l’urgence.']],
        ['id'=>'US10-Q2','question'=>'Le responsable filtre les demandes urgentes. Quel comportement est attendu ?','options'=>['Afficher uniquement les demandes marquées urgentes.','Afficher toutes les demandes par ordre alphabétique.','Supprimer les demandes non urgentes.','Envoyer un e-mail à tous les collaborateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : le filtre doit rester ciblé.','L’ordre alphabétique ne répond pas au besoin.','Supprimer des demandes serait destructif.','Envoyer un e-mail n’est pas le comportement attendu.']],
    ],
    'US11' => [
        ['id'=>'US11-Q1','question'=>'Un responsable filtre les demandes par service et par catégorie. Que doit garantir le système ?','options'=>['La combinaison des deux filtres.','L’application du seul dernier filtre choisi.','La suppression des demandes non filtrées.','L’affichage de toutes les demandes si un filtre est vide.'],'correct'=>0,'explanations'=>['Bonne réponse : les filtres doivent pouvoir être combinés.','Appliquer seulement le dernier filtre perdrait une contrainte.','Supprimer des données n’est pas acceptable.','Afficher toutes les demandes si un filtre est vide rend le filtre inutile.']],
        ['id'=>'US11-Q2','question'=>'Les résultats filtrés sont affichés. Quelle précaution est nécessaire ?','options'=>['Limiter les résultats aux demandes autorisées pour l’utilisateur.','Afficher toutes les demandes de l’entreprise.','Masquer le filtre après utilisation.','Réinitialiser les filtres à chaque chargement.'],'correct'=>0,'explanations'=>['Bonne réponse : les droits d’accès doivent être respectés.','Afficher toutes les demandes violerait les permissions.','Masquer le filtre nuirait à l’utilisabilité.','Réinitialiser automatiquement serait frustrant.']],
    ],
    'US12' => [
        ['id'=>'US12-Q1','question'=>'Un collaborateur signale une demande comme urgente. Quelle information est indispensable ?','options'=>['Une justification non vide.','Le nom du supérieur hiérarchique.','Une adresse e-mail externe.','Un code secret.'],'correct'=>0,'explanations'=>['Bonne réponse : la justification explique l’urgence.','Le supérieur hiérarchique n’est pas requis.','Une adresse externe n’est pas pertinente.','Un code secret n’a pas de sens.']],
        ['id'=>'US12-Q2','question'=>'L’urgence est enregistrée. Où doit-elle être visible ?','options'=>['Dans la liste et le détail de la demande pour le service informatique.','Uniquement dans le chat.','Seulement dans les statistiques.','Dans un fichier exporté.'],'correct'=>0,'explanations'=>['Bonne réponse : l’indicateur doit être visible là où la demande est traitée.','Le chat n’est pas le lieu principal de suivi.','Les statistiques ne suffisent pas pour agir.','Un fichier exporté n’est pas une consultation directe.']],
    ],
    'US13' => [
        ['id'=>'US13-Q1','question'=>'Un technicien consulte l’historique d’une demande. Quelle présentation est attendue ?','options'=>['Les interventions dans l’ordre chronologique, avec date, auteur et action.','Les interventions triées par taille de message.','Les interventions sans date.','Les interventions modifiables par le technicien.'],'correct'=>0,'explanations'=>['Bonne réponse : l’ordre chronologique et la traçabilité sont essentiels.','La taille du message n’est pas pertinente.','Sans date, le suivi devient impossible.','L’historique doit rester en lecture seule.']],
        ['id'=>'US13-Q2','question'=>'L’historique d’une demande est consulté par plusieurs utilisateurs. Quelle règle s’applique ?','options'=>['Il est en lecture seule pour les utilisateurs.','Il peut être modifié par le technicien.','Il peut être supprimé par le collaborateur.','Il est remplacé à chaque nouvelle intervention.'],'correct'=>0,'explanations'=>['Bonne réponse : l’historique doit être conservé.','Le modifier masquerait les actions réelles.','Le supprimer détruirait la traçabilité.','Le remplacer perdrait les interventions précédentes.']],
    ],
    'US14' => [
        ['id'=>'US14-Q1','question'=>'Un responsable consulte des statistiques d’activité. Quels éléments doit-il voir ?','options'=>['Le nombre de demandes par statut et par catégorie sur une période.','Uniquement le nombre total de demandes.','Les demandes sans date.','Les mots de passe des utilisateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : les indicateurs par statut et par catégorie sont attendus.','Le total seul manque de détail.','Les demandes sans date rendraient l’analyse impossible.','Les mots de passe ne doivent jamais être exposés.']],
        ['id'=>'US14-Q2','question'=>'La période d’analyse est choisie par le responsable. Que doit garantir le système ?','options'=>['Les indicateurs sont calculés entre la date de début et la date de fin.','Les indicateurs sont toujours calculés sur tout l’historique.','La période est ignorée si aucune donnée n’existe.','Les données sont supprimées après l’analyse.'],'correct'=>0,'explanations'=>['Bonne réponse : la période paramétrable doit être respectée.','Toujours tout l’historique ignorerait le besoin.','Ignorer la période fausserait l’analyse.','Supprimer les données détruirait l’historique.']],
    ],
    'US15' => [
        ['id'=>'US15-Q1','question'=>'Un collaborateur joint un document à une demande. Quel contrôle est nécessaire ?','options'=>['Vérifier le format et la taille du fichier.','Accepter n’importe quel fichier sans contrôle.','Supprimer le fichier après consultation.','Convertir automatiquement le fichier en texte.'],'correct'=>0,'explanations'=>['Bonne réponse : le format et la taille doivent être contrôlés.','Accepter n’importe quel fichier présente un risque.','Supprimer le fichier empêcherait le suivi.','Convertir automatiquement peut altérer le document.']],
        ['id'=>'US15-Q2','question'=>'Le fichier joint est enregistré. Où doit-il être visible ?','options'=>['Par le technicien dans le détail de la demande.','Uniquement dans le chat.','Seulement dans les statistiques.','Dans un fichier temporaire.'],'correct'=>0,'explanations'=>['Bonne réponse : le technicien doit pouvoir consulter le document.','Le chat n’est pas le lieu de stockage.','Les statistiques ne montrent pas le document.','Un fichier temporaire ne garantit pas la persistance.']],
    ],
    'US16' => [
        ['id'=>'US16-Q1','question'=>'Un incident majeur déclenche une alerte. Que doit indiquer l’alerte ?','options'=>['Le service concerné et l’heure de l’incident.','Le mot de passe de l’administrateur.','Tous les messages du chat.','Le code source de l’application.'],'correct'=>0,'explanations'=>['Bonne réponse : l’alerte doit être actionnable.','Le mot de passe ne doit jamais être affiché.','Tous les messages du chat sont inutiles.','Le code source n’est pas pertinent.']],
        ['id'=>'US16-Q2','question'=>'Le technicien reçoit l’alerte. Quelle action est attendue ?','options'=>['Pouvoir acquitter l’alerte.','Supprimer définitivement l’incident.','Ignorer l’alerte sans trace.','Envoyer l’alerte à tous les collaborateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : l’acquittement permet de suivre la prise en compte.','Supprimer l’incident détruirait l’information.','Ignorer sans trace empêche le suivi.','Envoyer à tous n’est pas nécessaire.']],
    ],
    'US17' => [
        ['id'=>'US17-Q1','question'=>'Un collaborateur demande la suppression de ses données personnelles. Que doit faire le système ?','options'=>['Créer une demande tracée avec sa date.','Supprimer immédiatement toutes les données sans trace.','Ignorer la demande.','Afficher le mot de passe du collaborateur.'],'correct'=>0,'explanations'=>['Bonne réponse : la demande doit être tracée et traitée.','Supprimer immédiatement sans trace n’est pas conforme.','Ignorer la demande violerait le RGPD.','Le mot de passe ne doit jamais être affiché.']],
        ['id'=>'US17-Q2','question'=>'La demande de suppression est enregistrée. Que doit voir le collaborateur ?','options'=>['Un délai de traitement affiché.','Le statut définitif immédiatement.','La liste de tous les utilisateurs.','Les données des autres collaborateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : un délai de traitement doit être affiché.','Le traitement immédiat n’est pas garanti.','La liste de tous les utilisateurs n’est pas pertinente.','Les données des autres ne doivent pas être exposées.']],
    ],
    'US18' => [
        ['id'=>'US18-Q1','question'=>'Un administrateur active un second facteur. Quel contrôle est nécessaire ?','options'=>['L’activation doit être tracée dans l’historique.','Le second facteur doit être désactivé par défaut.','L’échec du second facteur doit être ignoré.','Le mot de passe doit être affiché pendant l’activation.'],'correct'=>0,'explanations'=>['Bonne réponse : l’activation doit être tracée.','Désactiver par défaut rendrait la fonctionnalité inutile.','Ignorer les échecs compromettrait la sécurité.','Le mot de passe ne doit jamais être affiché.']],
        ['id'=>'US18-Q2','question'=>'Un échec du second facteur se produit. Que doit faire le système ?','options'=>['Bloquer la connexion.','Afficher le code attendu.','Créer un compte temporaire.','Ignorer l’échec et autoriser la connexion.'],'correct'=>0,'explanations'=>['Bonne réponse : l’échec doit bloquer la connexion.','Afficher le code attendu compromet la sécurité.','Créer un compte temporaire est inapproprié.','Ignorer l’échec annulerait la protection.']],
    ],
    'US19' => [
        ['id'=>'US19-Q1','question'=>'Un responsable suit les demandes par service. Quel indicateur est attendu ?','options'=>['Un indicateur par service pour une période choisie.','Uniquement le nombre total de demandes.','Les demandes sans service.','Le mot de passe des utilisateurs.'],'correct'=>0,'explanations'=>['Bonne réponse : l’indicateur par service est attendu.','Le total seul manque de détail.','Les demandes sans service rendraient l’analyse impossible.','Les mots de passe ne doivent jamais être exposés.']],
        ['id'=>'US19-Q2','question'=>'La période d’analyse est paramétrable. Que doit garantir le système ?','options'=>['Les données affichées peuvent être exportées.','La période est ignorée si aucune donnée n’existe.','Les données sont supprimées après l’analyse.','Les indicateurs sont toujours calculés sur tout l’historique.'],'correct'=>0,'explanations'=>['Bonne réponse : l’export est prévu.','Ignorer la période fausserait l’analyse.','Supprimer les données détruirait l’historique.','Toujours tout l’historique ignorerait le besoin.']],
    ],
    'US20' => [
        ['id'=>'US20-Q1','question'=>'Un technicien organise une intervention sur un site distant. Quelle information est nécessaire ?','options'=>['Le site de l’intervention est enregistré avec la demande.','Le site est ignoré.','La charge par site est cachée.','Les interventions ne peuvent pas être filtrées.'],'correct'=>0,'explanations'=>['Bonne réponse : le site doit être enregistré.','Ignorer le site empêche l’organisation.','Cacher la charge par site masque l’information.','Le filtrage par site est attendu.']],
        ['id'=>'US20-Q2','question'=>'La charge de travail par site est visible. Que doit éviter le système ?','options'=>['Le chevauchement d’interventions pour un même technicien.','L’affichage du site.','L’enregistrement de la date.','La consultation par le collaborateur.'],'correct'=>0,'explanations'=>['Bonne réponse : le planning doit éviter les chevauchements.','L’affichage du site est nécessaire.','L’enregistrement de la date est nécessaire.','La consultation par le collaborateur est attendue.']],
    ],
    'US21' => [
        ['id'=>'US21-Q1','question'=>'Un collaborateur consulte l’assistance contextualisée. Quel comportement est attendu ?','options'=>['Une aide est proposée selon la catégorie sélectionnée.','La même aide est affichée pour toutes les catégories.','Aucune aide n’est proposée.','L’aide remplace le formulaire de création.'],'correct'=>0,'explanations'=>['Bonne réponse : l’aide doit être contextualisée.','La même aide pour toutes les catégories manque de pertinence.','Aucune aide ne laisse l’utilisateur sans support.','L’aide ne doit pas remplacer le formulaire.']],
        ['id'=>'US21-Q2','question'=>'Le collaborateur consulte l’aide puis crée une demande. Quelle règle s’applique ?','options'=>['Le collaborateur peut créer une demande après avoir consulté l’aide.','La demande est créée automatiquement.','L’aide bloque la création.','Le formulaire est désactivé.'],'correct'=>0,'explanations'=>['Bonne réponse : l’aide accompagne sans bloquer.','La création automatique n’est pas prévue.','L’aide ne doit pas bloquer la création.','Le formulaire doit rester disponible.']],
    ],
    'US22' => [
        ['id'=>'US22-Q1','question'=>'Un technicien recherche une demande par numéro ou mot-clé. Que doit garantir le système ?','options'=>['Les résultats restent limités aux demandes autorisées.','Toutes les demandes de l’entreprise sont affichées.','La recherche ignore les droits.','La recherche supprime les résultats.'],'correct'=>0,'explanations'=>['Bonne réponse : les droits d’accès doivent être respectés.','Afficher toutes les demandes violerait les permissions.','Ignorer les droits est inacceptable.','Supprimer les résultats détruirait l’information.']],
        ['id'=>'US22-Q2','question'=>'La recherche est combinée avec un filtre par service. Quel comportement est attendu ?','options'=>['Les résultats respectent à la fois la recherche et le filtre.','Seul le filtre est appliqué.','Seule la recherche est appliquée.','Les résultats sont réinitialisés.'],'correct'=>0,'explanations'=>['Bonne réponse : la combinaison doit être respectée.','Appliquer seulement le filtre perd la recherche.','Appliquer seulement la recherche perd le filtre.','Réinitialiser les résultats rend la fonction inutile.']],
    ],
    'US23' => [
        ['id'=>'US23-Q1','question'=>'Un technicien planifie une intervention. Quelle information est indispensable ?','options'=>['Une date et un créneau d’intervention.','Uniquement le nom du collaborateur.','Une adresse e-mail externe.','Un mot de passe.'],'correct'=>0,'explanations'=>['Bonne réponse : la date et le créneau sont nécessaires.','Le nom seul ne permet pas de planifier.','Une adresse externe n’est pas pertinente.','Un mot de passe n’a pas de sens.']],
        ['id'=>'US23-Q2','question'=>'Deux interventions sont planifiées pour le même technicien. Que doit garantir le système ?','options'=>['Empêcher les interventions qui se chevauchent.','Autoriser le chevauchement sans avertissement.','Supprimer la première intervention.','Annuler la demande.'],'correct'=>0,'explanations'=>['Bonne réponse : le planning doit éviter les chevauchements.','Autoriser le chevauchement créerait un conflit.','Supprimer la première intervention détruirait l’information.','Annuler la demande n’est pas approprié.']],
    ],
];

/**
 * Banque des défis terminal GNU/Linux simulé pour la déclaration « Prête à valider ».
 *
 * Chaque User Story peut disposer d’un défi terminal : le développeur saisit une
 * commande de test dans le terminal simulé et le moteur PHP la compare aux règles
 * serveur. La commande attendue ne transite jamais par le navigateur : elle est
 * conservée côté serveur avec les variantes acceptées et les indices pédagogiques.
 */
 $CHALLENGE_TERMINALS = [

    'US01' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Se connecter à l’application » prête à valider : contrôlez la réponse HTTP du point d’entrée d’authentification et vérifiez que le service répond correctement.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'curl --help'],
        'command' => 'curl -i http://localhost/api/auth',
        'partials' => ['curl -i', '/api/auth'],
        'indices' => [
            'Le premier fragment doit demander à curl d’inclure les en-têtes HTTP dans la sortie, en plus du corps de la réponse. Il faut donc choisir l’option dédiée à l’affichage des en-têtes, et non une option qui modifie le corps ou le mode de transfert.',
            'La ressource à interroger est le point d’entrée local `http://localhost/api/auth`. Le contrôle concerne à la fois le code de statut HTTP et les en-têtes de la réponse.',
            'Utilisez curl, un client HTTP en ligne de commande. L’option `-i` demande d’afficher les en-têtes HTTP en plus du corps de la réponse. Fournissez ensuite l’URL complète `http://localhost/api/auth` comme argument.',
        ],
    ],

    'US02' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Créer une demande informatique » prête à valider : vérifiez que les données de création respectent le schéma JSON attendu.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'jq --help'],
        'command' => 'jq -e ".title and .description and .category" fixtures/demande.json',
        'partials' => ['jq -e', 'fixtures/demande.json'],
        'indices' => [
            'Le premier fragment doit faire dépendre le code de sortie de jq du résultat de l’expression : le contrôle doit échouer si les propriétés attendues ne sont pas toutes présentes et évaluées comme vraies. Choisissez l’option de jq qui active ce comportement de validation.',
            'Le fichier à examiner est `fixtures/demande.json`. Les trois propriétés à cibler sont `title`, `description` et `category`. Elles doivent être présentes et évaluées comme vraies dans l’expression.',
            'Utilisez jq pour évaluer l’expression .title and .description and .category, placée entre guillemets doubles afin de la transmettre comme un seul argument. L’option -e permet de faire dépendre le code de sortie du résultat de l’expression. Indiquez ensuite le fichier fixtures/demande.json.',
        ],
    ],

    'US03' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Catégoriser une demande » prête à valider : comparez les catégories attendues avec les catégories effectivement disponibles.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'diff --help'],
        'command' => 'diff fixtures/categories_attendues.txt fixtures/categories_obtenues.txt',
        'partials' => ['diff', 'categories_attendues.txt'],
        'indices' => [
            'Le premier fragment doit comparer deux fichiers ligne par ligne afin de faire ressortir les différences entre leurs contenus. Utilisez l’outil diff, dédié à cette comparaison.',
            'Le fichier de référence à utiliser est fixtures/categories_attendues.txt. Il contient la liste des catégories attendues. C’est ce fichier qui doit être fourni comme premier argument à la commande de comparaison.',
            'Utilisez diff pour comparer deux fichiers ligne par ligne. Indiquez d’abord fixtures/categories_attendues.txt, qui contient les catégories de référence, puis fixtures/categories_obtenues.txt, qui contient les catégories à contrôler.',
        ],
    ],

    'US04' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Consulter l’état de ses demandes » prête à valider : vérifiez que les données exposées par l’API ne contiennent que les demandes autorisées pour le collaborateur.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'newman --help'],
        'command' => 'newman run tests/api/request-ownership.postman_collection.json',
        'partials' => ['newman run', 'request-ownership.postman_collection.json'],
        'indices' => [
            'Le premier fragment doit lancer une collection de tests Postman depuis le terminal. Utilisez Newman, l’outil en ligne de commande correspondant, avec sa sous-commande run.',
            'La collection à exécuter se trouve dans `tests/api/request-ownership.postman_collection.json`. Elle regroupe les scénarios Postman consacrés à la vérification de la propriété des demandes.',
            'Utilisez Newman, l’outil en ligne de commande qui exécute les collections Postman. La sous-commande `run` lance une collection. Fournissez ensuite le chemin exact du fichier JSON `tests/api/request-ownership.postman_collection.json`.',
        ],
    ],

    'US05' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Consulter les demandes en attente » prête à valider : vérifiez que les demandes sont triées selon leur priorité puis leur date.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'awk --help'],
	'command' => 'awk -F ";" "NR>1 {print $2 \";\" $3}" fixtures/demandes_attente.csv | sort -t ";" -k1,1 -k2,2',
	'partials' => ['awk -F', 'sort -t ";"'],
        'indices' => [
            'Le premier fragment doit configurer awk pour qu’il découpe chaque ligne CSV selon le séparateur point-virgule. Il faut utiliser l’option qui définit le séparateur de champs, puis lui fournir ce caractère comme valeur.',
            'Après l’extraction des colonnes nécessaires, il faut trier les résultats. Utilisez la commande sort avec l’option -t pour définir le séparateur de champs. Ce séparateur est le point-virgule (;).',
            'Utilisez awk -F ";" pour traiter le fichier fixtures/demandes_attente.csv en utilisant le point-virgule comme séparateur de champs. La condition NR>1 exclut la première ligne. Le bloc {print $2 ";" $3} extrait la priorité et la date. Placez cette commande entre guillemets doubles, puis transmettez son résultat avec un pipe | à sort -t ";" -k1,1 -k2,2, afin de trier d’abord selon la première colonne, puis selon la deuxième.',
        ],
    ],

    'US06' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Modifier le statut d’une demande » prête à valider : vérifiez que les transitions entre statuts respectent les règles définies.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'vendor/bin/behat --help'],
        'command' => 'vendor/bin/behat features/status-transitions.feature',
        'partials' => ['vendor/bin/behat', 'status-transitions.feature'],
        'indices' => [
            'Le premier fragment doit exécuter les tests comportementaux décrits dans les fichiers de spécification Behat. Utilisez l’exécutable Behat du projet, situé dans vendor/bin.',
            'Le scénario à exécuter est défini dans le fichier `features/status-transitions.feature`, situé dans le répertoire `features` à la racine du projet.',
            'Utilisez l’exécutable vendor/bin/behat pour lancer Behat en lui fournissant le fichier features/status-transitions.feature. Behat interprétera les étapes décrites dans ce fichier de spécification et exécutera les tests comportementaux associés.',
        ],
    ],

    'US07' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Affecter une demande à un technicien » prête à valider : contrôlez que seuls les techniciens actifs de la bonne équipe peuvent être affectés.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'sqlite3 --help'],
        'command' => 'sqlite3 fixtures/organisation.db "SELECT id FROM techniciens WHERE actif = 1 AND equipe_id = 3;"',
        'partials' => ['sqlite3', 'WHERE actif = 1'],
        'indices' => [
            'Le premier fragment doit ouvrir une base de données SQLite depuis le terminal afin d’y exécuter une requête SQL. Utilisez le client sqlite3 ; le chemin de la base sera fourni comme argument.',
            'La base de données à consulter est `fixtures/organisation.db`. La table est `techniciens`. Les champs à examiner sont `id`, `actif` et `equipe_id`. Pour filtrer les enregistrements, utilisez une clause WHERE avec les deux conditions suivantes : `actif = 1` et `equipe_id = 3`.',
            'Utilisez la commande `sqlite3` en lui fournissant la base `fixtures/organisation.db`, suivie d’une requête SQL entre guillemets doubles. Sélectionnez la colonne `id` dans la table `techniciens` et ajoutez la condition `WHERE actif = 1 AND equipe_id = 3`. Terminez la requête SQL par un point-virgule, placé à l’intérieur des guillemets doubles.',
        ],
    ],

    'US08' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Recevoir une notification de changement » prête à valider : contrôlez que les événements de notification sont correctement enregistrés et qu’aucun doublon n’est généré.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'mysql --help'],
        'command' => 'mysql -e "SELECT event_id, COUNT(*) FROM notifications GROUP BY event_id HAVING COUNT(*) > 1;"',
        'partials' => ['mysql -e', 'HAVING COUNT(*) > 1'],
        'indices' => [
            'Le premier fragment doit permettre au client MySQL de recevoir une requête SQL directement dans la ligne de commande, sans ouvrir de session interactive. Choisissez l’option qui exécute l’instruction transmise en argument.',
            'La table concernée est `notifications`. Le champ de regroupement est `event_id`. Utilisez la clause HAVING pour ne retenir que les groupes dont le nombre d’occurrences est supérieur à un.',
            'Utilisez le client MySQL avec l’option -e pour transmettre directement la requête SQL entre guillemets doubles. Sélectionnez event_id et COUNT(*) dans la table notifications, regroupez les résultats avec GROUP BY event_id, puis utilisez HAVING COUNT(*) > 1 pour ne conserver que les groupes comportant plusieurs occurrences. Terminez l’instruction SQL par un point-virgule.',
        ],
    ],

    'US09' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Commenter une demande » prête à valider : recherchez les traces de contenus suspects dans les journaux de contrôle de sécurité des commentaires.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'grep --help'],
        'command' => 'grep -Ein "<script|javascript:|onerror=" logs/comment-security.log',
        'partials' => ['grep -Ein', 'comment-security.log'],
        'indices' => [
            'Le premier fragment doit combiner trois comportements de grep : interpréter les motifs comme une expression régulière étendue, ignorer la casse et afficher les numéros de ligne. Il faut utiliser les options correspondantes sous leur forme regroupée.',
            'Le fichier journal à examiner est `logs/comment-security.log`. Les trois motifs recherchés sont `<script`, `javascript:` et `onerror=`. Ils doivent être regroupés dans une même expression de recherche.',
            'Utilisez grep -Ein pour rechercher dans logs/comment-security.log les motifs <script, javascript: et onerror=. Regroupez ces trois motifs dans une expression régulière étendue, entre guillemets doubles, en les séparant par l’alternance |. Les options -E, -i et -n activent respectivement les expressions régulières étendues, l’ignorance de la casse et l’affichage des numéros de ligne.',
        ],
    ],

    'US10' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Visualiser les demandes urgentes » prête à valider : mesurez le temps de réponse du point d’entrée chargé de retourner les demandes urgentes.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'time --help'],
        'command' => 'time curl -s http://localhost/api/requests?priority=urgent',
        'partials' => ['time curl', 'priority=urgent'],
        'indices' => [
            'Le premier fragment doit mesurer le temps d’exécution d’une requête HTTP effectuée avec curl. Utilisez time pour chronométrer l’exécution de curl, plutôt que de mesurer uniquement le temps de réponse indiqué par le serveur.',
            'La ressource à interroger est `http://localhost/api/requests?priority=urgent`. Le paramètre de filtrage est `priority` et sa valeur doit être `urgent`. La requête doit être effectuée sans afficher normalement le corps de la réponse.',
            'Utilisez `time` pour mesurer la durée d’exécution de la commande qui suit. Cette commande est un appel HTTP effectué avec curl. L’option `-s` active le mode silencieux. Placez l’URL complète `http://localhost/api/requests?priority=urgent` après les options de curl.',
        ],
    ],

    'US11' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Filtrer les demandes » prête à valider : vérifiez que les critères combinés produisent bien les résultats attendus.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'phpunit --help'],
        'command' => 'phpunit tests/CombinedFiltersTest.php',
        'partials' => ['phpunit', 'CombinedFiltersTest.php'],
        'indices' => [
            'Le premier fragment doit lancer les tests unitaires écrits avec PHPUnit. Utilisez l’exécutable phpunit pour exécuter le fichier de test fourni ensuite en argument.',
            'Le fichier de test concerné est `tests/CombinedFiltersTest.php`. Il cible précisément les scénarios de combinaison des filtres, notamment ceux portant sur le service et la catégorie.',
            'Utilisez l’exécutable phpunit pour lancer le fichier de tests tests/CombinedFiltersTest.php, qui contient les vérifications relatives à la combinaison des filtres.',
        ],
    ],

    'US12' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Signaler une demande urgente » prête à valider : contrôlez que les données de justification sont présentes dans les demandes signalées.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'psql --help'],
        'command' => 'psql -c "SELECT id FROM demandes WHERE urgente = TRUE AND justification IS NULL;"',
        'partials' => ['psql -c', 'justification IS NULL'],
        'indices' => [
            'Le premier fragment doit transmettre une instruction SQL au client PostgreSQL pour exécution immédiate, sans entrer dans son interface interactive. Choisissez l’option qui reçoit la commande SQL en argument.',
            'La table à interroger est `demandes`. Les champs concernés sont `id`, `urgente` et `justification`. Le résultat doit identifier les demandes dont `urgente` vaut TRUE et dont `justification` ne contient aucune valeur, c’est-à-dire est NULL. Pour vérifier l’absence de justification, utilisez l’opérateur IS NULL sur le champ justification.',
            'Utilisez la commande psql avec l’option -c pour transmettre directement une requête SQL entre guillemets doubles. Sélectionnez id dans la table demandes, puis appliquez les conditions urgente = TRUE AND justification IS NULL. Terminez la requête par un point-virgule.',
        ],
    ],

    'US13' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Consulter l’historique des interventions » prête à valider : vérifiez que les événements de l’historique sont classés dans l’ordre chronologique.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'sort --help'],
        'command' => 'sort -t ";" -k1,1 fixtures/intervention-history.csv',
        'partials' => ['sort -t', 'intervention-history.csv'],
        'indices' => [
            'Le premier fragment doit indiquer à sort que les champs des lignes sont séparés par un point-virgule. Il faut choisir l’option qui définit le délimiteur de champs et lui fournir ce caractère.',
            'Le fichier à traiter est `fixtures/intervention-history.csv`. Les champs sont séparés par des points-virgules. La première colonne, correspondant à la première clé de chaque ligne, doit servir au classement.',
            'Utilisez sort -t ";" -k1,1 pour trier le fichier fixtures/intervention-history.csv. L’option -t ";" définit le point-virgule comme séparateur de champs et -k1,1 sélectionne la première colonne comme clé de tri.',
        ],
    ],

    'US14' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Consulter les statistiques d’activité » prête à valider : vérifiez que le volume de demandes agrégé par statut correspond aux données de référence.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'php --help'],
        'command' => 'php tests/check-activity-statistics.php',
        'partials' => ['php tests', 'check-activity-statistics.php'],
        'indices' => [
            'Le premier fragment doit exécuter un script PHP de contrôle situé dans le répertoire tests du projet. Utilisez l’interpréteur php, suivi du chemin du script dans ce répertoire.',
            'Le script à exécuter est `tests/check-activity-statistics.php`. Il se trouve dans le répertoire `tests` et porte le nom exact `check-activity-statistics.php`.',
            'Utilisez l’interpréteur php pour exécuter directement le script tests/check-activity-statistics.php, situé dans le répertoire tests du projet.',
        ],
    ],

    'US15' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Joindre un document à une demande » prête à valider : contrôlez que les fichiers téléversés possèdent un type MIME autorisé.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'file --help'],
        'command' => 'file --mime-type fixtures/uploads/document-test.pdf',
        'partials' => ['file --mime-type', 'document-test.pdf'],
        'indices' => [
            'Le premier fragment doit demander à file d’afficher le type MIME détecté pour le contenu du fichier, plutôt qu’une description générale ou le type habituellement associé à son extension. Choisissez l’option dédiée au type MIME.',
            'Le fichier à examiner est `fixtures/uploads/document-test.pdf`, dans le sous-répertoire `uploads` du répertoire `fixtures`. Il faut obtenir son type MIME, et non simplement son extension `.pdf`.',
            'Utilisez file --mime-type pour détecter le type MIME du fichier fixtures/uploads/document-test.pdf. L’option --mime-type demande l’affichage du type MIME détecté à partir du contenu du fichier.',
        ],
    ],

    'US16' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Recevoir une alerte en temps réel » prête à valider : examinez les journaux afin de vérifier que les alertes majeures sont bien enregistrées et acquittées.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'tail --help'],
        'command' => 'tail -n 50 logs/major-incidents.log',
        'partials' => ['tail -n 50', 'major-incidents.log'],
        'indices' => [
            'Le premier fragment doit limiter l’affichage à la fin du journal, en demandant précisément les cinquante dernières lignes. Choisissez l’option de tail qui fixe un nombre de lignes, puis indiquez la valeur 50.',
            'Le fichier à consulter est `logs/major-incidents.log`, situé dans le répertoire `logs`. Seule la partie récente du journal est demandée : les cinquante dernières lignes.',
            'Utilisez tail -n 50 pour afficher les cinquante dernières lignes du fichier logs/major-incidents.log. L’option -n définit le nombre de lignes à afficher et sa valeur doit être 50.',
        ],
    ],

    'US17' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Supprimer ses données personnelles » prête à valider : contrôlez que les demandes de suppression sont correctement tracées et que leur intégrité est préservée.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'sha256sum --help'],
        'command' => 'sha256sum fixtures/deletion-requests.csv',
        'partials' => ['sha256sum', 'deletion-requests.csv'],
        'indices' => [
            'Le premier fragment doit calculer une empreinte cryptographique SHA-256 du fichier de suivi. Utilisez l’outil sha256sum, qui produit cette empreinte à partir du fichier fourni en argument.',
            'Le fichier concerné est `fixtures/deletion-requests.csv`, situé dans le répertoire `fixtures`. C’est ce fichier, et non un autre document de suivi, qui doit être utilisé pour le calcul.',
            'Utilisez sha256sum pour calculer l’empreinte cryptographique SHA-256 du fichier fixtures/deletion-requests.csv. Fournissez ce chemin comme argument à la commande.',
        ],
    ],

    'US18' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Renforcer l’authentification » prête à valider : vérifiez que les règles de qualité et de sécurité du code d’authentification sont respectées.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'phpstan --help'],
        'command' => 'vendor/bin/phpstan analyse includes/two-factor.php --level=5',
        'partials' => ['vendor/bin/phpstan analyse', 'two-factor.php'],
        'indices' => [
            'Le premier fragment doit lancer une analyse statique PHP avec PHPStan. Utilisez l’exécutable du projet situé dans vendor/bin, avec la sous-commande analyse.',
            'Le fichier à analyser est `includes/two-factor.php`. L’analyse doit être effectuée avec PHPStan, installé dans le répertoire `vendor/bin`, et avec le niveau de règle demandé fixé à 5.',
            'Utilisez l’exécutable vendor/bin/phpstan avec la sous-commande analyse pour analyser le fichier includes/two-factor.php. Ajoutez l’option --level=5 après le chemin du fichier afin de fixer le niveau d’analyse statique à 5.',
        ],
    ],

    'US19' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Suivre les demandes par service » prête à valider : contrôlez que l’API de statistiques par service renvoie une réponse exploitable.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'http --help'],
        'command' => 'http GET http://localhost/api/statistics/services',
        'partials' => ['http GET', '/api/statistics/services'],
        'indices' => [
            'Le premier fragment doit effectuer une requête HTTP avec HTTPie, dont l’exécutable en ligne de commande est http. Utilisez explicitement la méthode GET pour consulter la ressource, sans modifier les données.',
            'La ressource à interroger est `http://localhost/api/statistics/services`. Le chemin exact de l’API est `/api/statistics/services` et la méthode HTTP attendue est GET.',
            'Utilisez HTTPie, dont la commande en ligne est `http`. Indiquez explicitement la méthode HTTP `GET`, puis fournissez l’URL complète `http://localhost/api/statistics/services` comme argument. HTTPie effectue la requête et affiche la réponse reçue.',
        ],
    ],

    'US20' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Prendre en compte les sites distants » prête à valider : vérifiez que les données de rattachement des demandes à un site sont cohérentes.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'xmllint --help'],
        'command' => 'xmllint --noout --schema schemas/intervention-site.xsd fixtures/interventions.xml',
        'partials' => ['xmllint --noout', 'intervention-site.xsd'],
        'indices' => [
            'Le premier fragment doit contrôler la conformité d’un document XML à un schéma XSD sans afficher le contenu du document. Utilisez xmllint avec l’option --noout, qui désactive cette sortie.',
            'Deux fichiers sont nécessaires : le schéma `schemas/intervention-site.xsd` et le document à valider `fixtures/interventions.xml`. Le premier décrit la structure attendue ; le second contient les données à contrôler.',
            'Utilisez xmllint avec l’option --noout pour ne pas afficher le contenu du document XML. Ajoutez --schema schemas/intervention-site.xsd pour désigner le schéma XSD de référence, puis indiquez le document à valider : fixtures/interventions.xml.',
        ],
    ],

    'US21' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Proposer une assistance contextualisée » prête à valider : vérifiez que les ressources d’aide sont correctement structurées et associées à leur catégorie.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'vendor/bin/pest --help'],
        'command' => 'vendor/bin/pest tests/ContextualHelpTest.php',
        'partials' => ['vendor/bin/pest', 'ContextualHelpTest.php'],
        'indices' => [
            'Le premier fragment doit exécuter un fichier de tests PHP avec Pest. Utilisez l’exécutable Pest installé dans le répertoire vendor/bin du projet.',
            'Le fichier de test à exécuter est `tests/ContextualHelpTest.php`. Il se trouve dans le répertoire `tests` et contient les vérifications associées à l’aide contextuelle.',
            'Utilisez l’exécutable vendor/bin/pest pour exécuter directement le fichier de tests tests/ContextualHelpTest.php. Aucun sous-ordre supplémentaire n’est nécessaire.',
        ],
    ],

    'US22' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Rechercher une demande » prête à valider : contrôlez que la recherche ne retourne pas de demandes auxquelles l’utilisateur n’a pas accès.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'vendor/bin/codecept --help'],
        'command' => 'vendor/bin/codecept run Api RequestSearchCest',
        'partials' => ['vendor/bin/codecept run', 'RequestSearchCest'],
        'indices' => [
            'Le premier fragment doit lancer les tests avec Codeception. Utilisez l’exécutable Codeception situé dans vendor/bin, avec la sous-commande run.',
            'La suite de tests concernée est `Api` et le scénario à exécuter est `RequestSearchCest`. Le projet utilise Codeception, installé dans `vendor/bin`.',
            'Utilisez l’exécutable vendor/bin/codecept avec la sous-commande run, puis indiquez la suite de tests Api et le scénario RequestSearchCest, dans cet ordre.',
        ],
    ],

    'US23' => [
        'instruction' => 'Vérification technique avant de déclarer la User Story « Planifier une intervention » prête à valider : effectuez un test de charge simulé sur le point d’entrée de consultation des disponibilités afin de vérifier son comportement sous plusieurs requêtes.',
        'exploration' => ['pwd', 'ls', 'ls -l', 'ls -la', 'wrk --help'],
        'command' => 'wrk -t2 -c10 -d10s http://localhost/api/planning/availability',
        'partials' => ['wrk -t2', 'planning/availability'],
        'indices' => [
            'Le premier paramètre de wrk doit fixer à 2 le nombre de threads utilisés pour générer la charge. Il faut distinguer ce réglage des 10 connexions simultanées et de la durée de 10 secondes : cherchez l’option qui configure le nombre de threads.',
            'La ressource ciblée est `http://localhost/api/planning/availability`. Le test doit utiliser deux threads, dix connexions simultanées et une durée totale de dix secondes.',
            'Utilisez `wrk`, un outil de benchmark HTTP. L’option `-t` définit le nombre de threads, ici 2 ; `-c` définit le nombre de connexions, ici 10 ; `-d` définit la durée du test, ici 10 secondes. Placez ces trois paramètres avant l’URL complète du point d’entrée à tester.',
        ],
    ],
];

return $CHALLENGE_QUESTIONS;
