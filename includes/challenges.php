<?php
/**
 * PROMPT DE RECONSTRUCTION DES TABLEAUX DE DÉFIS (pour GitHub / démonstration)
 *
 * Ce fichier contient deux tableaux PHP vides de leur substance ($CHALLENGE_QUESTIONS
 * et $CHALLENGE_TERMINALS) afin de ne pas exposer la banque de défis réelle.
 *
 * OBJECTIF
 * Reconstruire ces deux tableaux avec des contenus COHÉRENTS et FONCTIONNELS pour
 * l'application, mais DIFFÉRENTS de la banque originale, afin qu'aucun étudiant ne
 * puisse réutiliser les réponses de l'application déployée.
 *
 * CONTEXTE APPLICATIF
 * WorkFlow IT est une simulation Scrum (3 équipes, ~85 min). Chaque User Story (US01
 * à US23) possède deux défis techniques déclenchés lorsqu'un Developer déclare la
 * User Story « prête à valider » :
 *   1) un mini-quiz (4 options, 1 correcte) sur les bonnes pratiques de développement
 *      web / sécurité / validation / accès / données ;
 *   2) un défi terminal (commande bash simulée) avec indices progressifs.
 * Les réponses correctes alimentent le calcul du risque client (30 % vs 60 % de refus).
 *
 * CONTRAINTES STRUCTURELLES (à respecter strictement)
 *
 * 1) $CHALLENGE_QUESTIONS : tableau associatif clé = 'US01'..'US23'.
 *    Chaque US contient un tableau indexé de 2 questions. Chaque question :
 *      - 'id'          : chaîne 'USxx-Q1' puis 'USxx-Q2' (xx = numéro de l'US).
 *      - 'question'    : chaîne, question claire en français (1 phrase).
 *      - 'options'     : tableau indexé de 4 chaînes (réponses possibles).
 *      - 'correct'     : entier 0..3, index de la bonne réponse dans 'options'.
 *      - 'explanations': tableau indexé de 4 chaînes, une par option, expliquant
 *                        pourquoi l'option est correcte ou incorrecte.
 *    Règles : count(options) === count(explanations) === 4 ; correct ∈ {0,1,2,3}.
 *
 * 2) $CHALLENGE_TERMINALS : tableau associatif clé = 'US01'..'US23'.
 *    Chaque US contient un tableau associatif avec :
 *      - 'instruction' : chaîne, objectif du défi terminal (1 phrase).
 *      - 'exploration' : tableau indexé de 5 commandes bash plausibles pour
 *                        explorer (ex. pwd, ls, cat, curl --help, jq --help).
 *      - 'command'     : chaîne, commande bash complète et valide à exécuter.
 *      - 'partials'    : tableau indexé de 2 fragments de 'command' (préfixes
 *                        successifs permettant d'obtenir les indices).
 *      - 'indices'     : tableau indexé de 3 chaînes, indices progressifs du moins
 *                        au plus explicite, le 3e donnant la commande exacte.
 *    Règles : count(exploration) === 5 ; count(partials) === 2 ;
 *             count(indices) === 3 ; 'command' contient les deux 'partials'.
 *
 * CONTRAINTES DE CONTENU
 * - Thème : une application web générique de gestion de demandes / tickets
 *   (authentification, validation, catégories, statuts, droits d'accès, historique,
 *   sécurité des données). Ne pas reprendre les textes ni les réponses de la banque
 *   originale.
 * - Chaque question teste une bonne pratique (validation serveur, CSRF, isolation
 *   des données, traçabilité, gestion d'erreurs, etc.). La bonne réponse est
 *   toujours l'option la plus sûre et la plus conforme aux bonnes pratiques.
 * - Les explications sont pédagogiques et cohérentes avec l'option.
 * - Les commandes terminales utilisent des outils courants (curl, jq, grep, find,
 *   ls, cat, awk) sur des fichiers/URL fictifs mais plausibles.
 * - Les 'partials' sont des préfixes réels de 'command' (ex. 'curl -i' et
 *   'http://localhost/api/...').
 * - Les 'indices' guident progressivement vers 'command' sans la révéler au 1er.
 *
 * QUALITÉ ATTENDUE
 * - Aucune erreur de syntaxe PHP (tableaux associatifs valides).
 * - Cohérence : 'correct' pointe bien vers la bonne option.
 * - Variété : ne pas répéter la même question ni la même commande pour toutes les US.
 * - Pas de données sensibles, pas de chemins réels, pas de secrets.
 *
 * RÉSULTAT ATTENDU
 * Deux tableaux PHP ($CHALLENGE_QUESTIONS et $CHALLENGE_TERMINALS) de 23 entrées
 * (US01..US23), structurellement identiques à la banque originale, mais au contenu
 * entièrement renouvelé et non réutilisable pour tricher.
 */

declare(strict_types=1);

$CHALLENGE_QUESTIONS = array (
  'US01' => 
  array (
    0 => 
    array (
      'id' => 'US01-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US01-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US02' => 
  array (
    0 => 
    array (
      'id' => 'US02-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US02-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US03' => 
  array (
    0 => 
    array (
      'id' => 'US03-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US03-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US04' => 
  array (
    0 => 
    array (
      'id' => 'US04-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US04-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US05' => 
  array (
    0 => 
    array (
      'id' => 'US05-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US05-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US06' => 
  array (
    0 => 
    array (
      'id' => 'US06-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US06-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US07' => 
  array (
    0 => 
    array (
      'id' => 'US07-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US07-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US08' => 
  array (
    0 => 
    array (
      'id' => 'US08-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US08-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US09' => 
  array (
    0 => 
    array (
      'id' => 'US09-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US09-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US10' => 
  array (
    0 => 
    array (
      'id' => 'US10-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US10-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US11' => 
  array (
    0 => 
    array (
      'id' => 'US11-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US11-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US12' => 
  array (
    0 => 
    array (
      'id' => 'US12-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US12-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US13' => 
  array (
    0 => 
    array (
      'id' => 'US13-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US13-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US14' => 
  array (
    0 => 
    array (
      'id' => 'US14-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US14-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US15' => 
  array (
    0 => 
    array (
      'id' => 'US15-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US15-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US16' => 
  array (
    0 => 
    array (
      'id' => 'US16-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US16-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US17' => 
  array (
    0 => 
    array (
      'id' => 'US17-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US17-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US18' => 
  array (
    0 => 
    array (
      'id' => 'US18-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US18-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US19' => 
  array (
    0 => 
    array (
      'id' => 'US19-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US19-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US20' => 
  array (
    0 => 
    array (
      'id' => 'US20-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US20-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US21' => 
  array (
    0 => 
    array (
      'id' => 'US21-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US21-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US22' => 
  array (
    0 => 
    array (
      'id' => 'US22-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US22-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
  'US23' => 
  array (
    0 => 
    array (
      'id' => 'US23-Q1',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
    1 => 
    array (
      'id' => 'US23-Q2',
      'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      'options' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
      'correct' => 0,
      'explanations' => 
      array (
        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      ),
    ),
  ),
);

$CHALLENGE_TERMINALS = array (
  'US01' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US02' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US03' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US04' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US05' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US06' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US07' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US08' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US09' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US10' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US11' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US12' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US13' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US14' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US15' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US16' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US17' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US18' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US19' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US20' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US21' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US22' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
  'US23' => 
  array (
    'instruction' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'exploration' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      3 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      4 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'command' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'partials' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
    'indices' => 
    array (
      0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
      2 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    ),
  ),
);

return $CHALLENGE_QUESTIONS;
