<?php
/**
 * Banque alternative de questions techniques pour le challenge de passage à « Terminé ».
 * Variante B : mêmes User Stories et même contrat de données, questions et réponses renouvelées.
 * Les réponses correctes restent côté serveur.
 */

declare(strict_types=1);

$CHALLENGE_QUESTIONS = [
    'US01' => [
        [
            'id' => 'US01-Q1',
            'question' => 'Un compte est verrouillé après plusieurs échecs. Quelle procédure permet une reprise sûre ?',
            'options' => [
                'Communication du mot de passe actuel par courriel.',
                'Désactivation de toutes les protections du compte.',
                'Réinitialisation automatique du mot de passe sans contrôle.',
                'Déverrouillage après vérification de l’identité et selon une procédure tracée.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Communication du mot de passe actuel par courriel.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Désactivation de toutes les protections du compte.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réinitialisation automatique du mot de passe sans contrôle.',
                'Bonne réponse : Déverrouillage après vérification de l’identité et selon une procédure tracée. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
        [
            'id' => 'US01-Q2',
            'question' => 'Une session reste inactive pendant une longue période. Quel mécanisme réduit le risque d’utilisation par un tiers ?',
            'options' => [
                'Expiration de session côté serveur après une durée d’inactivité définie.',
                'Conservation illimitée de la session dans le navigateur.',
                'Simple masquage du bouton de déconnexion.',
                'Enregistrement du mot de passe dans un cookie lisible.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Expiration de session côté serveur après une durée d’inactivité définie. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Conservation illimitée de la session dans le navigateur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Simple masquage du bouton de déconnexion.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Enregistrement du mot de passe dans un cookie lisible.',
            ],
        ],
    ],
    'US02' => [
        [
            'id' => 'US02-Q1',
            'question' => 'Une demande contient une pièce jointe trop volumineuse. Quel comportement est attendu ?',
            'options' => [
                'Refuser le fichier et afficher la limite autorisée.',
                'Ignorer la limite si le demandeur est authentifié.',
                'Enregistrer le fichier puis supprimer son contenu.',
                'Remplacer le fichier par une pièce jointe vide.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Refuser le fichier et afficher la limite autorisée. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Ignorer la limite si le demandeur est authentifié.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Enregistrer le fichier puis supprimer son contenu.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Remplacer le fichier par une pièce jointe vide.',
            ],
        ],
        [
            'id' => 'US02-Q2',
            'question' => 'Le navigateur envoie deux fois la même requête de création après un délai réseau. Quelle protection est pertinente ?',
            'options' => [
                'Supprimer toutes les demandes du même auteur.',
                'Utiliser une clé d’idempotence pour éviter la création de doublons.',
                'Désactiver la validation serveur.',
                'Créer systématiquement deux demandes.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer toutes les demandes du même auteur.',
                'Bonne réponse : Utiliser une clé d’idempotence pour éviter la création de doublons. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Désactiver la validation serveur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Créer systématiquement deux demandes.',
            ],
        ],
    ],
    'US03' => [
        [
            'id' => 'US03-Q1',
            'question' => 'Une catégorie est désactivée alors que des demandes historiques y sont rattachées. Quelle règle préserver ?',
            'options' => [
                'Réaffecter toutes les demandes sans journalisation.',
                'Conserver le libellé historique tout en empêchant les nouvelles affectations.',
                'Supprimer les demandes concernées.',
                'Effacer la catégorie des demandes existantes.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réaffecter toutes les demandes sans journalisation.',
                'Bonne réponse : Conserver le libellé historique tout en empêchant les nouvelles affectations. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les demandes concernées.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Effacer la catégorie des demandes existantes.',
            ],
        ],
        [
            'id' => 'US03-Q2',
            'question' => 'Deux catégories portent des libellés proches. Quel élément doit servir de référence technique ?',
            'options' => [
                'La position de la catégorie dans la liste affichée.',
                'Le texte saisi librement par le demandeur.',
                'Un identifiant stable de catégorie, distinct de son libellé.',
                'La couleur choisie dans l’interface.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La position de la catégorie dans la liste affichée.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le texte saisi librement par le demandeur.',
                'Bonne réponse : Un identifiant stable de catégorie, distinct de son libellé. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La couleur choisie dans l’interface.',
            ],
        ],
    ],
    'US04' => [
        [
            'id' => 'US04-Q1',
            'question' => 'Un collaborateur tente de consulter une demande en modifiant son identifiant dans l’URL. Quel contrôle est indispensable ?',
            'options' => [
                'Masquer uniquement le lien dans l’interface.',
                'Autoriser la lecture si la demande existe.',
                'Vérifier côté serveur que l’utilisateur est autorisé à accéder à cette ressource.',
                'Faire confiance à l’identifiant transmis par le navigateur.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Masquer uniquement le lien dans l’interface.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Autoriser la lecture si la demande existe.',
                'Bonne réponse : Vérifier côté serveur que l’utilisateur est autorisé à accéder à cette ressource. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Faire confiance à l’identifiant transmis par le navigateur.',
            ],
        ],
        [
            'id' => 'US04-Q2',
            'question' => 'Un utilisateur quitte l’entreprise. Que faut-il faire lors de la révocation de ses accès ?',
            'options' => [
                'Rendre toutes ses demandes publiques.',
                'Transférer son mot de passe à son responsable.',
                'Laisser ses sessions actives jusqu’à leur expiration naturelle.',
                'Révoquer ses sessions et empêcher tout nouvel accès selon les règles de conservation.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Rendre toutes ses demandes publiques.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Transférer son mot de passe à son responsable.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Laisser ses sessions actives jusqu’à leur expiration naturelle.',
                'Bonne réponse : Révoquer ses sessions et empêcher tout nouvel accès selon les règles de conservation. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
    ],
    'US05' => [
        [
            'id' => 'US05-Q1',
            'question' => 'Plusieurs demandes ont la même priorité. Quel critère secondaire donne un ordre stable et utile ?',
            'options' => [
                'L’ordre de chargement en mémoire.',
                'La longueur de la description.',
                'Le nom aléatoire du demandeur.',
                'La date de création, de la plus ancienne à la plus récente.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : L’ordre de chargement en mémoire.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La longueur de la description.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le nom aléatoire du demandeur.',
                'Bonne réponse : La date de création, de la plus ancienne à la plus récente. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
        [
            'id' => 'US05-Q2',
            'question' => 'Un technicien ouvre une demande déjà prise en charge. Quelle information évite une reprise concurrente ?',
            'options' => [
                'L’affectation actuelle et le statut de prise en charge.',
                'La couleur de la catégorie.',
                'Le nombre de connexions du demandeur.',
                'Le navigateur utilisé lors de la création.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : L’affectation actuelle et le statut de prise en charge. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La couleur de la catégorie.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le nombre de connexions du demandeur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le navigateur utilisé lors de la création.',
            ],
        ],
    ],
    'US06' => [
        [
            'id' => 'US06-Q1',
            'question' => 'Une demande terminée doit être rouverte. Quelle pratique garantit la traçabilité ?',
            'options' => [
                'Créer une nouvelle transition avec auteur, date et motif.',
                'Effacer l’historique des transitions précédentes.',
                'Remplacer silencieusement l’ancien statut.',
                'Modifier directement la base sans événement.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Créer une nouvelle transition avec auteur, date et motif. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Effacer l’historique des transitions précédentes.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Remplacer silencieusement l’ancien statut.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Modifier directement la base sans événement.',
            ],
        ],
        [
            'id' => 'US06-Q2',
            'question' => 'Une transition « terminée » est demandée alors qu’une intervention obligatoire manque. Que doit faire le système ?',
            'options' => [
                'Supprimer la demande.',
                'Refuser la transition et préciser la condition manquante.',
                'Ignorer les règles métier pour les administrateurs.',
                'Accepter puis corriger le statut ultérieurement.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer la demande.',
                'Bonne réponse : Refuser la transition et préciser la condition manquante. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Ignorer les règles métier pour les administrateurs.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Accepter puis corriger le statut ultérieurement.',
            ],
        ],
    ],
    'US07' => [
        [
            'id' => 'US07-Q1',
            'question' => 'Un technicien est désactivé après avoir reçu une demande. Quelle action est cohérente ?',
            'options' => [
                'Laisser l’affectation active sans signalement.',
                'Conserver l’historique et réaffecter la demande selon une règle autorisée.',
                'Réactiver automatiquement le technicien.',
                'Supprimer la demande et son historique.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Laisser l’affectation active sans signalement.',
                'Bonne réponse : Conserver l’historique et réaffecter la demande selon une règle autorisée. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réactiver automatiquement le technicien.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer la demande et son historique.',
            ],
        ],
        [
            'id' => 'US07-Q2',
            'question' => 'Une affectation est tentée sur un technicien d’une autre équipe. Quel contrôle doit être appliqué ?',
            'options' => [
                'Vérifier uniquement le nom affiché.',
                'Ignorer l’équipe si le technicien est connecté.',
                'Vérifier l’appartenance à l’équipe autorisée côté serveur.',
                'Accepter puis déplacer la demande automatiquement.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Vérifier uniquement le nom affiché.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Ignorer l’équipe si le technicien est connecté.',
                'Bonne réponse : Vérifier l’appartenance à l’équipe autorisée côté serveur. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Accepter puis déplacer la demande automatiquement.',
            ],
        ],
    ],
    'US08' => [
        [
            'id' => 'US08-Q1',
            'question' => 'Un événement de notification est rejoué après une panne. Comment éviter plusieurs notifications identiques ?',
            'options' => [
                'Envoyer à nouveau à chaque tentative sans contrôle.',
                'Changer le destinataire à chaque reprise.',
                'Dédupliquer à partir d’un identifiant d’événement stable.',
                'Supprimer les notifications déjà lues.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Envoyer à nouveau à chaque tentative sans contrôle.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Changer le destinataire à chaque reprise.',
                'Bonne réponse : Dédupliquer à partir d’un identifiant d’événement stable. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les notifications déjà lues.',
            ],
        ],
        [
            'id' => 'US08-Q2',
            'question' => 'Le fournisseur de courriel est temporairement indisponible. Quel traitement est robuste ?',
            'options' => [
                'Bloquer toute création de demande.',
                'Abandonner définitivement sans journal.',
                'Marquer immédiatement l’envoi comme réussi.',
                'Mettre la notification en file avec reprise contrôlée et suivi des erreurs.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Bloquer toute création de demande.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Abandonner définitivement sans journal.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Marquer immédiatement l’envoi comme réussi.',
                'Bonne réponse : Mettre la notification en file avec reprise contrôlée et suivi des erreurs. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
    ],
    'US09' => [
        [
            'id' => 'US09-Q1',
            'question' => 'Un commentaire contient du HTML fourni par un utilisateur. Quelle mesure protège son affichage ?',
            'options' => [
                'Faire confiance aux utilisateurs authentifiés.',
                'Retirer uniquement le mot « script ».',
                'Exécuter le HTML pour conserver la mise en forme.',
                'Encoder les caractères spéciaux au moment du rendu selon le contexte.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Faire confiance aux utilisateurs authentifiés.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Retirer uniquement le mot « script ».',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Exécuter le HTML pour conserver la mise en forme.',
                'Bonne réponse : Encoder les caractères spéciaux au moment du rendu selon le contexte. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
        [
            'id' => 'US09-Q2',
            'question' => 'Un commentaire dépasse la longueur maximale autorisée. Quelle réponse est appropriée ?',
            'options' => [
                'Rejeter la saisie côté serveur avec une erreur de validation explicite.',
                'Accepter une longueur illimitée.',
                'Tronquer silencieusement le texte sans avertir.',
                'Supprimer les commentaires précédents.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Rejeter la saisie côté serveur avec une erreur de validation explicite. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Accepter une longueur illimitée.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Tronquer silencieusement le texte sans avertir.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les commentaires précédents.',
            ],
        ],
    ],
    'US10' => [
        [
            'id' => 'US10-Q1',
            'question' => 'Le tableau des urgences affiche des demandes déjà clôturées. Quelle correction métier faut-il appliquer ?',
            'options' => [
                'Filtrer sur les statuts actifs et la priorité urgente.',
                'Afficher toutes les demandes sans distinction.',
                'Trier uniquement par nom de demandeur.',
                'Masquer la date de clôture.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Filtrer sur les statuts actifs et la priorité urgente. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Afficher toutes les demandes sans distinction.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Trier uniquement par nom de demandeur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Masquer la date de clôture.',
            ],
        ],
        [
            'id' => 'US10-Q2',
            'question' => 'Le volume de demandes urgentes augmente fortement. Quel indicateur aide à prioriser le traitement ?',
            'options' => [
                'La taille moyenne des pièces jointes.',
                'Le nombre de demandes urgentes ouvertes, ventilé par ancienneté.',
                'Le nombre de pages consultées.',
                'Le nombre total de comptes utilisateurs.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La taille moyenne des pièces jointes.',
                'Bonne réponse : Le nombre de demandes urgentes ouvertes, ventilé par ancienneté. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le nombre de pages consultées.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le nombre total de comptes utilisateurs.',
            ],
        ],
    ],
    'US11' => [
        [
            'id' => 'US11-Q1',
            'question' => 'Un filtre par statut et par service est appliqué. Quelle logique correspond à une combinaison restrictive ?',
            'options' => [
                'Ignorer le statut dès qu’un service est choisi.',
                'Ne retourner que les demandes satisfaisant les deux critères.',
                'Appliquer uniquement le dernier filtre saisi.',
                'Retourner les demandes satisfaisant au moins un critère.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Ignorer le statut dès qu’un service est choisi.',
                'Bonne réponse : Ne retourner que les demandes satisfaisant les deux critères. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Appliquer uniquement le dernier filtre saisi.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Retourner les demandes satisfaisant au moins un critère.',
            ],
        ],
        [
            'id' => 'US11-Q2',
            'question' => 'L’utilisateur retire un filtre actif. Que doit faire la liste ?',
            'options' => [
                'Conserver les résultats précédents sans mise à jour.',
                'Réinitialiser la session utilisateur.',
                'Recalculer les résultats avec les seuls critères encore actifs.',
                'Supprimer les critères restants.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Conserver les résultats précédents sans mise à jour.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réinitialiser la session utilisateur.',
                'Bonne réponse : Recalculer les résultats avec les seuls critères encore actifs. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les critères restants.',
            ],
        ],
    ],
    'US12' => [
        [
            'id' => 'US12-Q1',
            'question' => 'Un collaborateur signale une urgence sans fournir de motif. Quelle règle est cohérente ?',
            'options' => [
                'Accepter le signalement sans aucune information.',
                'Transformer la demande en demande clôturée.',
                'Demander un motif obligatoire avant d’enregistrer le signalement.',
                'Inventer automatiquement un motif.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Accepter le signalement sans aucune information.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Transformer la demande en demande clôturée.',
                'Bonne réponse : Demander un motif obligatoire avant d’enregistrer le signalement. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Inventer automatiquement un motif.',
            ],
        ],
        [
            'id' => 'US12-Q2',
            'question' => 'Une urgence est requalifiée comme normale. Quelle information doit rester consultable ?',
            'options' => [
                'Aucune trace pour éviter l’encombrement.',
                'Le mot de passe de la personne ayant décidé.',
                'Uniquement le nouveau niveau de priorité.',
                'La décision de requalification, son auteur, sa date et son motif.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Aucune trace pour éviter l’encombrement.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le mot de passe de la personne ayant décidé.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Uniquement le nouveau niveau de priorité.',
                'Bonne réponse : La décision de requalification, son auteur, sa date et son motif. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
    ],
    'US13' => [
        [
            'id' => 'US13-Q1',
            'question' => 'Deux interventions ont exactement le même horodatage. Quel second critère assure un ordre déterministe ?',
            'options' => [
                'La longueur du commentaire.',
                'Le nom du navigateur.',
                'La couleur de l’interface.',
                'Un identifiant d’événement stable.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La longueur du commentaire.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le nom du navigateur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : La couleur de l’interface.',
                'Bonne réponse : Un identifiant d’événement stable. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
        [
            'id' => 'US13-Q2',
            'question' => 'Un événement historique est corrigé à la suite d’une erreur de saisie. Quelle approche est préférable ?',
            'options' => [
                'Tracer une correction ou un événement rectificatif sans effacer la trace initiale.',
                'Modifier les autres événements pour conserver l’ordre.',
                'Supprimer toute la ligne d’historique.',
                'Effacer l’historique de la demande.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Tracer une correction ou un événement rectificatif sans effacer la trace initiale. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Modifier les autres événements pour conserver l’ordre.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer toute la ligne d’historique.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Effacer l’historique de la demande.',
            ],
        ],
    ],
    'US14' => [
        [
            'id' => 'US14-Q1',
            'question' => 'Une période statistique ne contient aucune demande. Comment présenter le résultat ?',
            'options' => [
                'Afficher zéro pour les volumes et signaler clairement l’absence de données.',
                'Réutiliser les chiffres de la période précédente.',
                'Afficher une moyenne calculée sur une période différente.',
                'Masquer la période sélectionnée.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Afficher zéro pour les volumes et signaler clairement l’absence de données. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réutiliser les chiffres de la période précédente.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Afficher une moyenne calculée sur une période différente.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Masquer la période sélectionnée.',
            ],
        ],
        [
            'id' => 'US14-Q2',
            'question' => 'Le total par statut ne correspond pas au nombre total de demandes. Quel contrôle effectuer ?',
            'options' => [
                'Supprimer les demandes les plus anciennes.',
                'Vérifier que les statuts sont exhaustifs, mutuellement exclusifs et calculés sur le même périmètre.',
                'Remplacer le total par la somme des pages affichées.',
                'Ajouter arbitrairement l’écart au statut le plus fréquent.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les demandes les plus anciennes.',
                'Bonne réponse : Vérifier que les statuts sont exhaustifs, mutuellement exclusifs et calculés sur le même périmètre. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Remplacer le total par la somme des pages affichées.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Ajouter arbitrairement l’écart au statut le plus fréquent.',
            ],
        ],
    ],
    'US15' => [
        [
            'id' => 'US15-Q1',
            'question' => 'Un fichier téléversé possède une extension PDF mais un contenu exécutable. Quel contrôle est nécessaire ?',
            'options' => [
                'Autoriser le fichier si sa taille est faible.',
                'Contrôler le contenu réel, le type MIME et appliquer les règles de stockage sécurisé.',
                'Renommer le fichier en .pdf sans vérification.',
                'Se fier uniquement à l’extension du nom.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Autoriser le fichier si sa taille est faible.',
                'Bonne réponse : Contrôler le contenu réel, le type MIME et appliquer les règles de stockage sécurisé. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Renommer le fichier en .pdf sans vérification.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Se fier uniquement à l’extension du nom.',
            ],
        ],
        [
            'id' => 'US15-Q2',
            'question' => 'Un utilisateur tente de télécharger une pièce jointe d’une demande qui ne lui appartient pas. Quelle règle appliquer ?',
            'options' => [
                'Rendre les URL de fichiers publiques.',
                'Autoriser le téléchargement si le nom est connu.',
                'Vérifier les droits sur la demande avant de délivrer le fichier.',
                'Vérifier uniquement que le fichier existe.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Rendre les URL de fichiers publiques.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Autoriser le téléchargement si le nom est connu.',
                'Bonne réponse : Vérifier les droits sur la demande avant de délivrer le fichier. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Vérifier uniquement que le fichier existe.',
            ],
        ],
    ],
    'US16' => [
        [
            'id' => 'US16-Q1',
            'question' => 'Une alerte critique est envoyée à plusieurs destinataires. Quel mécanisme permet de savoir qui l’a prise en compte ?',
            'options' => [
                'Considérer l’envoi comme un acquittement.',
                'Envoyer l’alerte uniquement à son créateur.',
                'Enregistrer un acquittement associé à l’utilisateur et à l’horodatage.',
                'Supprimer l’alerte après le premier affichage.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Considérer l’envoi comme un acquittement.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Envoyer l’alerte uniquement à son créateur.',
                'Bonne réponse : Enregistrer un acquittement associé à l’utilisateur et à l’horodatage. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer l’alerte après le premier affichage.',
            ],
        ],
        [
            'id' => 'US16-Q2',
            'question' => 'Le canal temps réel est momentanément coupé. Comment éviter de perdre les alertes ?',
            'options' => [
                'Considérer tous les clients comme informés.',
                'Supprimer les alertes non distribuées.',
                'Ne conserver les alertes qu’en mémoire navigateur.',
                'Persister les alertes et permettre leur récupération à la reconnexion.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Considérer tous les clients comme informés.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les alertes non distribuées.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Ne conserver les alertes qu’en mémoire navigateur.',
                'Bonne réponse : Persister les alertes et permettre leur récupération à la reconnexion. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
    ],
    'US17' => [
        [
            'id' => 'US17-Q1',
            'question' => 'Une demande de suppression concerne des données soumises à une obligation légale de conservation. Quelle réponse est adaptée ?',
            'options' => [
                'Rendre les données accessibles à tous les administrateurs.',
                'Refuser toute demande sans explication.',
                'Supprimer systématiquement toute trace sans exception.',
                'Appliquer les règles de conservation, limiter les accès et informer sur les données non supprimables immédiatement.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Rendre les données accessibles à tous les administrateurs.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Refuser toute demande sans explication.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer systématiquement toute trace sans exception.',
                'Bonne réponse : Appliquer les règles de conservation, limiter les accès et informer sur les données non supprimables immédiatement. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
        [
            'id' => 'US17-Q2',
            'question' => 'Une suppression est exécutée. Quel élément permet d’en prouver le traitement sans conserver inutilement les données supprimées ?',
            'options' => [
                'Une trace minimale de traitement conforme aux règles de conservation.',
                'Le mot de passe de l’utilisateur.',
                'Une copie intégrale des données dans un fichier public.',
                'Aucune trace de l’opération.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Une trace minimale de traitement conforme aux règles de conservation. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le mot de passe de l’utilisateur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Une copie intégrale des données dans un fichier public.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Aucune trace de l’opération.',
            ],
        ],
    ],
    'US18' => [
        [
            'id' => 'US18-Q1',
            'question' => 'Un utilisateur active une application d’authentification comme second facteur. Quelle précaution est essentielle lors de l’enrôlement ?',
            'options' => [
                'Vérifier le code généré avant d’activer définitivement le second facteur.',
                'Afficher le secret d’enrôlement dans les journaux.',
                'Activer le second facteur avant toute vérification.',
                'Réutiliser le même secret pour tous les comptes.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Vérifier le code généré avant d’activer définitivement le second facteur. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Afficher le secret d’enrôlement dans les journaux.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Activer le second facteur avant toute vérification.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réutiliser le même secret pour tous les comptes.',
            ],
        ],
        [
            'id' => 'US18-Q2',
            'question' => 'Un utilisateur perd son second facteur. Quelle procédure est la plus sûre ?',
            'options' => [
                'Envoyer un code permanent par courriel non vérifié.',
                'Suivre une procédure de récupération avec vérification d’identité et révocation des facteurs perdus.',
                'Communiquer le secret de secours au support sans contrôle.',
                'Désactiver le second facteur pour tous les comptes.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Envoyer un code permanent par courriel non vérifié.',
                'Bonne réponse : Suivre une procédure de récupération avec vérification d’identité et révocation des facteurs perdus. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Communiquer le secret de secours au support sans contrôle.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Désactiver le second facteur pour tous les comptes.',
            ],
        ],
    ],
    'US19' => [
        [
            'id' => 'US19-Q1',
            'question' => 'Un service n’a reçu aucune demande pendant la période choisie. Comment le graphique doit-il traiter ce service ?',
            'options' => [
                'Lui attribuer la moyenne des autres services.',
                'Le représenter avec une valeur nulle si le périmètre exige tous les services.',
                'Réutiliser sa valeur du mois précédent.',
                'Le supprimer systématiquement du rapport.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Lui attribuer la moyenne des autres services.',
                'Bonne réponse : Le représenter avec une valeur nulle si le périmètre exige tous les services. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réutiliser sa valeur du mois précédent.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Le supprimer systématiquement du rapport.',
            ],
        ],
        [
            'id' => 'US19-Q2',
            'question' => 'Un responsable exporte les statistiques par service. Quelle propriété doit être préservée ?',
            'options' => [
                'Un périmètre différent choisi automatiquement.',
                'Uniquement la première page du résultat.',
                'Le même périmètre de dates et les mêmes filtres que ceux affichés.',
                'Les données personnelles non nécessaires.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Un périmètre différent choisi automatiquement.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Uniquement la première page du résultat.',
                'Bonne réponse : Le même périmètre de dates et les mêmes filtres que ceux affichés. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Les données personnelles non nécessaires.',
            ],
        ],
    ],
    'US20' => [
        [
            'id' => 'US20-Q1',
            'question' => 'Un site distant est renommé. Comment préserver les rapports historiques ?',
            'options' => [
                'Remplacer les identifiants dans toutes les archives sans trace.',
                'Supprimer les interventions passées.',
                'Conserver un identifiant de site stable et historiser le libellé si nécessaire.',
                'Créer un nouveau site sans lien avec l’ancien.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Remplacer les identifiants dans toutes les archives sans trace.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer les interventions passées.',
                'Bonne réponse : Conserver un identifiant de site stable et historiser le libellé si nécessaire. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Créer un nouveau site sans lien avec l’ancien.',
            ],
        ],
        [
            'id' => 'US20-Q2',
            'question' => 'Une intervention est affectée à un site qui n’existe plus. Quel comportement est pertinent ?',
            'options' => [
                'Supprimer l’intervention.',
                'Accepter la référence inconnue.',
                'Créer automatiquement un site avec un nom vide.',
                'Signaler la référence invalide et proposer un site actif autorisé.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer l’intervention.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Accepter la référence inconnue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Créer automatiquement un site avec un nom vide.',
                'Bonne réponse : Signaler la référence invalide et proposer un site actif autorisé. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
    ],
    'US21' => [
        [
            'id' => 'US21-Q1',
            'question' => 'Aucune ressource d’aide n’est associée à la catégorie choisie. Que doit présenter l’application ?',
            'options' => [
                'Un formulaire prérempli avec des données inventées.',
                'Un écran vide sans explication.',
                'Une ressource d’une autre catégorie sans avertissement.',
                'Un message indiquant qu’aucune aide spécifique n’est disponible et un accès à l’aide générale.',
            ],
            'correct' => 3,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Un formulaire prérempli avec des données inventées.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Un écran vide sans explication.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Une ressource d’une autre catégorie sans avertissement.',
                'Bonne réponse : Un message indiquant qu’aucune aide spécifique n’est disponible et un accès à l’aide générale. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
            ],
        ],
        [
            'id' => 'US21-Q2',
            'question' => 'Le contenu d’aide est mis à jour. Quand la nouvelle version doit-elle être visible ?',
            'options' => [
                'Après publication, selon la règle de cache définie, sans nécessiter une modification du formulaire.',
                'Seulement pour les nouveaux comptes.',
                'Uniquement après réinstallation du navigateur.',
                'Jamais pour les utilisateurs déjà connectés.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Après publication, selon la règle de cache définie, sans nécessiter une modification du formulaire. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Seulement pour les nouveaux comptes.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Uniquement après réinstallation du navigateur.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Jamais pour les utilisateurs déjà connectés.',
            ],
        ],
    ],
    'US22' => [
        [
            'id' => 'US22-Q1',
            'question' => 'Une recherche contient des caractères accentués. Quel comportement améliore la pertinence ?',
            'options' => [
                'Normaliser correctement les chaînes pour comparer les termes sans perdre les droits d’accès.',
                'Désactiver les filtres de permission.',
                'Supprimer tous les caractères accentués du contenu stocké.',
                'Retourner toutes les demandes en cas de caractère spécial.',
            ],
            'correct' => 0,
            'explanations' => [
                'Bonne réponse : Normaliser correctement les chaînes pour comparer les termes sans perdre les droits d’accès. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Désactiver les filtres de permission.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer tous les caractères accentués du contenu stocké.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Retourner toutes les demandes en cas de caractère spécial.',
            ],
        ],
        [
            'id' => 'US22-Q2',
            'question' => 'La recherche ne trouve aucun résultat. Quelle réponse est la plus utile ?',
            'options' => [
                'Réafficher les résultats d’une recherche précédente.',
                'Afficher un état vide explicite et permettre d’ajuster les critères.',
                'Créer automatiquement une demande.',
                'Afficher une erreur serveur générique.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Réafficher les résultats d’une recherche précédente.',
                'Bonne réponse : Afficher un état vide explicite et permettre d’ajuster les critères. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Créer automatiquement une demande.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Afficher une erreur serveur générique.',
            ],
        ],
    ],
    'US23' => [
        [
            'id' => 'US23-Q1',
            'question' => 'Une intervention est déplacée à un autre créneau. Quelle vérification doit précéder l’enregistrement ?',
            'options' => [
                'Accepter le déplacement sans contrôle.',
                'Contrôler à nouveau les conflits de disponibilité sur le créneau cible.',
                'Supprimer toutes les autres interventions du technicien.',
                'Vérifier uniquement l’ancien créneau.',
            ],
            'correct' => 1,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Accepter le déplacement sans contrôle.',
                'Bonne réponse : Contrôler à nouveau les conflits de disponibilité sur le créneau cible. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Supprimer toutes les autres interventions du technicien.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Vérifier uniquement l’ancien créneau.',
            ],
        ],
        [
            'id' => 'US23-Q2',
            'question' => 'Une intervention nécessite un technicien et un équipement disponibles. Quelle règle appliquer ?',
            'options' => [
                'Vérifier uniquement la disponibilité du technicien.',
                'Autoriser le chevauchement si le site est identique.',
                'Vérifier la disponibilité simultanée des deux ressources sur toute la durée.',
                'Vérifier uniquement la date de début.',
            ],
            'correct' => 2,
            'explanations' => [
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Vérifier uniquement la disponibilité du technicien.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Autoriser le chevauchement si le site est identique.',
                'Bonne réponse : Vérifier la disponibilité simultanée des deux ressources sur toute la durée. Cette règle protège la cohérence fonctionnelle, la sécurité ou la traçabilité attendue.',
                'Cette proposition ne respecte pas la règle métier ou le contrôle attendu : Vérifier uniquement la date de début.',
            ],
        ],
    ],
];

/**
 * Banque alternative des défis terminal GNU/Linux simulé pour la déclaration « Prête à valider ».
 * Les commandes attendues et leurs indices sont propres à cette variante.
 */
$CHALLENGE_TERMINALS = [
    'US01' => [
        'instruction' => 'Contrôlez les en-têtes de sécurité renvoyés par le point d’entrée de session.',
        'exploration' => [
            'curl --help',
            'curl -I http://localhost/api/session',
        ],
        'command' => 'curl -I http://localhost/api/session',
        'partials' => [
            'curl -I',
            '/api/session',
        ],
        'indices' => [
            'Utilisez curl avec l’option -I pour demander uniquement les en-têtes HTTP.',
            'La ressource à interroger est http://localhost/api/session.',
            'La commande complète associe curl, -I et l’URL locale du point d’entrée de session.',
        ],
    ],
    'US02' => [
        'instruction' => 'Vérifiez que le fichier d’exemple contient une propriété identifiant non vide.',
        'exploration' => [
            'jq --help',
            'cat fixtures/demande.json',
        ],
        'command' => 'jq -e ".id != null and .id != """ fixtures/demande.json',
        'partials' => [
            'jq -e',
            'fixtures/demande.json',
        ],
        'indices' => [
            'L’option -e fait dépendre le code de sortie du résultat de l’expression jq.',
            'Le contrôle porte sur la propriété id du fichier fixtures/demande.json.',
            'Évaluez .id != null and .id != "" avec jq -e sur le fichier indiqué.',
        ],
    ],
    'US03' => [
        'instruction' => 'Repérez les catégories présentes dans le fichier de référence sans doublons.',
        'exploration' => [
            'sort --help',
            'uniq --help',
        ],
        'command' => 'sort -u fixtures/categories_obtenues.txt',
        'partials' => [
            'sort -u',
            'categories_obtenues.txt',
        ],
        'indices' => [
            'L’option -u de sort trie les lignes et ne conserve qu’une occurrence de chaque valeur.',
            'Le fichier à traiter est fixtures/categories_obtenues.txt.',
            'Exécutez sort -u sur le fichier des catégories obtenues.',
        ],
    ],
    'US04' => [
        'instruction' => 'Exécutez le scénario de contrôle d’accès aux demandes avec le client HTTP de test.',
        'exploration' => [
            'newman --help',
            'ls tests/api',
        ],
        'command' => 'newman run tests/api/request-access.postman_collection.json',
        'partials' => [
            'newman run',
            'request-access.postman_collection.json',
        ],
        'indices' => [
            'Utilisez Newman avec la sous-commande run pour exécuter une collection Postman.',
            'La collection alternative est tests/api/request-access.postman_collection.json.',
            'Lancez newman run suivi du chemin complet de la collection.',
        ],
    ],
    'US05' => [
        'instruction' => 'Extrayez les colonnes priorité et date, puis triez-les dans l’ordre décroissant de priorité.',
        'exploration' => [
            'awk --help',
            'sort --help',
        ],
        'command' => 'awk -F ";" "NR>1 {print $2 ";" $3}" fixtures/demandes_attente.csv | sort -t ";" -k1,1r -k2,2',
        'partials' => [
            'awk -F',
            'sort -t',
        ],
        'indices' => [
            'Le fichier CSV utilise le point-virgule comme séparateur ; awk doit ignorer la ligne d’en-tête.',
            'Extrayez les colonnes 2 et 3, puis transmettez-les à sort avec le même séparateur.',
            'Le tri doit être décroissant sur la première colonne extraite (-k1,1r), puis croissant sur la seconde (-k2,2).',
        ],
    ],
    'US06' => [
        'instruction' => 'Lancez les tests dédiés aux transitions de statut avec PHPUnit.',
        'exploration' => [
            'vendor/bin/phpunit --help',
            'ls tests',
        ],
        'command' => 'vendor/bin/phpunit tests/RequestStatusTest.php',
        'partials' => [
            'vendor/bin/phpunit',
            'RequestStatusTest.php',
        ],
        'indices' => [
            'Utilisez l’exécutable PHPUnit installé dans vendor/bin.',
            'Le fichier de test à lancer est tests/RequestStatusTest.php.',
            'Exécutez vendor/bin/phpunit avec le chemin du fichier de test.',
        ],
    ],
    'US07' => [
        'instruction' => 'Listez les techniciens actifs de l’équipe 2 dans la base SQLite de démonstration.',
        'exploration' => [
            'sqlite3 --help',
            'ls fixtures',
        ],
        'command' => 'sqlite3 fixtures/organisation.db "SELECT id FROM techniciens WHERE actif = 1 AND equipe_id = 2;"',
        'partials' => [
            'sqlite3 fixtures/organisation.db',
            'equipe_id = 2',
        ],
        'indices' => [
            'Ouvrez la base fixtures/organisation.db avec sqlite3.',
            'La requête doit sélectionner les techniciens actifs appartenant à l’équipe 2.',
            'Exécutez la requête SQL complète dans sqlite3, en terminant par un point-virgule.',
        ],
    ],
    'US08' => [
        'instruction' => 'Recherchez les événements de notification dont le traitement est encore en attente.',
        'exploration' => [
            'mysql --help',
        ],
        'command' => 'mysql -e "SELECT event_id FROM notifications WHERE status = \'pending\';"',
        'partials' => [
            'mysql -e',
            'status = \'pending\'',
        ],
        'indices' => [
            'Utilisez mysql avec l’option -e pour exécuter une requête SQL.',
            'La condition attendue porte sur status = pending.',
            'Sélectionnez event_id dans notifications avec la condition indiquée.',
        ],
    ],
    'US09' => [
        'instruction' => 'Recherchez dans le journal les tentatives d’injection de balises iframe.',
        'exploration' => [
            'grep --help',
        ],
        'command' => 'grep -Ein "<iframe|</iframe" logs/comment-security.log',
        'partials' => [
            'grep -Ein',
            'comment-security.log',
        ],
        'indices' => [
            'Utilisez grep avec -E pour les expressions régulières étendues et -i pour ignorer la casse.',
            'Le journal à analyser est logs/comment-security.log.',
            'Recherchez les motifs <iframe et </iframe dans ce fichier.',
        ],
    ],
    'US10' => [
        'instruction' => 'Mesurez le temps total de réponse de l’API qui retourne les demandes critiques.',
        'exploration' => [
            'curl --help',
        ],
        'command' => 'curl -s -o /dev/null -w "%{time_total}\\n" "http://localhost/api/requests?priority=critical"',
        'partials' => [
            'curl -s -o /dev/null',
            'time_total',
            'priority=critical',
        ],
        'indices' => [
            'Utilisez curl en mode silencieux et redirigez le corps vers /dev/null.',
            'L’option -w permet d’afficher la variable time_total.',
            'Interrogez l’URL locale avec priority=critical et affichez le temps total.',
        ],
    ],
    'US11' => [
        'instruction' => 'Exécutez le test automatisé qui vérifie le cumul des filtres de date et de statut.',
        'exploration' => [
            'phpunit --help',
        ],
        'command' => 'vendor/bin/phpunit tests/DateStatusFiltersTest.php',
        'partials' => [
            'vendor/bin/phpunit',
            'DateStatusFiltersTest.php',
        ],
        'indices' => [
            'Utilisez l’exécutable PHPUnit situé dans vendor/bin.',
            'Le fichier ciblé est tests/DateStatusFiltersTest.php.',
            'Lancez PHPUnit sur ce fichier précis.',
        ],
    ],
    'US12' => [
        'instruction' => 'Recherchez les demandes urgentes dont le motif de justification est absent.',
        'exploration' => [
            'psql --help',
        ],
        'command' => 'psql -c "SELECT id FROM demandes WHERE urgente = TRUE AND (justification IS NULL OR justification = \'\');"',
        'partials' => [
            'psql -c',
            'justification IS NULL',
        ],
        'indices' => [
            'Utilisez psql avec -c pour exécuter une requête SQL.',
            'La sélection porte sur les demandes urgentes avec justification NULL ou vide.',
            'Exécutez la requête complète sur la table demandes.',
        ],
    ],
    'US13' => [
        'instruction' => 'Affichez les dix événements les plus récents du journal des interventions.',
        'exploration' => [
            'tail --help',
        ],
        'command' => 'tail -n 10 fixtures/intervention-history.csv',
        'partials' => [
            'tail -n 10',
            'intervention-history.csv',
        ],
        'indices' => [
            'Utilisez tail avec -n pour choisir le nombre de lignes affichées.',
            'Le fichier est fixtures/intervention-history.csv.',
            'Affichez les dix dernières lignes de ce fichier.',
        ],
    ],
    'US14' => [
        'instruction' => 'Lancez le contrôle qui compare les statistiques de demandes par priorité aux données de référence.',
        'exploration' => [
            'php --help',
        ],
        'command' => 'php tests/check-priority-statistics.php',
        'partials' => [
            'php tests/',
            'check-priority-statistics.php',
        ],
        'indices' => [
            'Exécutez un script PHP en passant son chemin à l’interpréteur php.',
            'Le script de contrôle est tests/check-priority-statistics.php.',
            'La commande complète est php suivie du chemin du script.',
        ],
    ],
    'US15' => [
        'instruction' => 'Identifiez le type MIME réel du fichier de test image téléversé.',
        'exploration' => [
            'file --help',
        ],
        'command' => 'file --mime-type fixtures/uploads/avatar-test.png',
        'partials' => [
            'file --mime-type',
            'avatar-test.png',
        ],
        'indices' => [
            'Utilisez file avec l’option --mime-type pour afficher le type MIME.',
            'Le fichier à examiner est fixtures/uploads/avatar-test.png.',
            'Exécutez file --mime-type sur ce chemin.',
        ],
    ],
    'US16' => [
        'instruction' => 'Affichez les vingt dernières lignes du journal des alertes critiques.',
        'exploration' => [
            'tail --help',
        ],
        'command' => 'tail -n 20 logs/critical-alerts.log',
        'partials' => [
            'tail -n 20',
            'critical-alerts.log',
        ],
        'indices' => [
            'L’option -n de tail fixe le nombre de lignes à afficher.',
            'Le journal est logs/critical-alerts.log.',
            'Affichez les vingt dernières lignes avec tail -n 20.',
        ],
    ],
    'US17' => [
        'instruction' => 'Calculez une empreinte SHA-256 du fichier de suivi des demandes d’effacement.',
        'exploration' => [
            'sha256sum --help',
        ],
        'command' => 'sha256sum fixtures/erasure-requests.csv',
        'partials' => [
            'sha256sum',
            'erasure-requests.csv',
        ],
        'indices' => [
            'Utilisez sha256sum pour calculer une empreinte SHA-256.',
            'Le fichier cible est fixtures/erasure-requests.csv.',
            'La commande complète applique sha256sum au fichier indiqué.',
        ],
    ],
    'US18' => [
        'instruction' => 'Analysez le module de récupération du second facteur avec PHPStan au niveau 6.',
        'exploration' => [
            'vendor/bin/phpstan --help',
        ],
        'command' => 'vendor/bin/phpstan analyse includes/two-factor-recovery.php --level=6',
        'partials' => [
            'vendor/bin/phpstan analyse',
            'two-factor-recovery.php',
        ],
        'indices' => [
            'Utilisez phpstan analyse sur le module concerné.',
            'Le fichier à analyser est includes/two-factor-recovery.php.',
            'Ajoutez --level=6 à la commande complète.',
        ],
    ],
    'US19' => [
        'instruction' => 'Interrogez le point d’entrée HTTP qui fournit les statistiques de demandes par équipe.',
        'exploration' => [
            'http --help',
        ],
        'command' => 'http GET http://localhost/api/statistics/teams',
        'partials' => [
            'http GET',
            'statistics/teams',
        ],
        'indices' => [
            'Utilisez HTTPie avec la méthode GET.',
            'Le point d’entrée est http://localhost/api/statistics/teams.',
            'La commande complète est http GET suivi de l’URL.',
        ],
    ],
    'US20' => [
        'instruction' => 'Validez le document XML des interventions avec le schéma de contrôle des sites.',
        'exploration' => [
            'xmllint --help',
        ],
        'command' => 'xmllint --noout --schema schemas/intervention-location.xsd fixtures/interventions.xml',
        'partials' => [
            'xmllint --noout --schema',
            'intervention-location.xsd',
        ],
        'indices' => [
            'Utilisez xmllint avec --noout et --schema.',
            'Le schéma est schemas/intervention-location.xsd et le document fixtures/interventions.xml.',
            'Passez d’abord le schéma, puis le fichier XML à valider.',
        ],
    ],
    'US21' => [
        'instruction' => 'Exécutez les tests du moteur qui sélectionne l’aide selon le type de demande.',
        'exploration' => [
            'vendor/bin/pest --help',
        ],
        'command' => 'vendor/bin/pest tests/HelpByCategoryTest.php',
        'partials' => [
            'vendor/bin/pest',
            'HelpByCategoryTest.php',
        ],
        'indices' => [
            'Utilisez Pest depuis vendor/bin.',
            'Le fichier de test est tests/HelpByCategoryTest.php.',
            'Exécutez vendor/bin/pest avec le chemin du test.',
        ],
    ],
    'US22' => [
        'instruction' => 'Lancez le scénario API qui vérifie le filtrage des résultats selon les droits du demandeur.',
        'exploration' => [
            'vendor/bin/codecept --help',
        ],
        'command' => 'vendor/bin/codecept run Api AuthorizedSearchCest',
        'partials' => [
            'vendor/bin/codecept run',
            'AuthorizedSearchCest',
        ],
        'indices' => [
            'Utilisez Codeception avec la sous-commande run.',
            'La suite est Api et le scénario est AuthorizedSearchCest.',
            'L’ordre des arguments est run, Api, puis AuthorizedSearchCest.',
        ],
    ],
    'US23' => [
        'instruction' => 'Effectuez un test de charge court sur le point d’entrée qui retourne les créneaux disponibles.',
        'exploration' => [
            'wrk --help',
        ],
        'command' => 'wrk -t3 -c8 -d15s http://localhost/api/planning/slots',
        'partials' => [
            'wrk -t3',
            '-c8 -d15s',
            'planning/slots',
        ],
        'indices' => [
            'Avec wrk, -t définit le nombre de threads : ici 3.',
            '-c fixe 8 connexions simultanées et -d fixe une durée de 15 secondes.',
            'La cible est http://localhost/api/planning/slots ; placez-la après les options.',
        ],
    ],
];

return $CHALLENGE_QUESTIONS;
