<?php
/**
 * Configuration générale de WorkFlow IT.
 *
 * Ce fichier est le seul endroit à modifier pour adapter l'application
 * à une séance ou à un hébergement différent.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Identité de l'application
// ---------------------------------------------------------------------------
const APP_NAME = 'WorkFlow IT';
const APP_VERSION = '1.0.0';

// Fuseau horaire de référence : le serveur reste l'autorité temporelle.
const APP_TIMEZONE = 'Europe/Paris';
date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding('UTF-8');

// Les erreurs détaillées restent dans les journaux du serveur : elles ne
// doivent jamais être affichées aux utilisateurs en production.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ---------------------------------------------------------------------------
// Chemins
// ---------------------------------------------------------------------------
const APP_ROOT = __DIR__;
const DATA_DIR = APP_ROOT . '/datas';
const USERS_FILE = DATA_DIR . '/users.txt';
const SESSION_FILE = DATA_DIR . '/session.json';
const EVENTS_FILE = DATA_DIR . '/events.json';
const HISTORY_FILE = DATA_DIR . '/history.json';
const LOGIN_ATTEMPTS_FILE = DATA_DIR . '/login_attempts.json';
const RATE_LIMIT_FILE = DATA_DIR . '/rate_limit.json';
const PASSWORD_REQUESTS_FILE = DATA_DIR . '/password_requests.json';
const CHALLENGES_FILE = DATA_DIR . '/challenges.json';
const APP_LOCK_FILE = DATA_DIR . '/app.lock';

// ---------------------------------------------------------------------------
// Horaires de la séance
// ---------------------------------------------------------------------------
// La séance est prévue de 13 h 00 à 14 h 55 : 10 minutes de prise en main
// (13 h 00–13 h 10), 90 minutes de simulation active (13 h 10–14 h 40)
// et 15 minutes maximum d'analyse post-session (14 h 40–14 h 55).
// Ces constantes permettent d'adapter l'application à un autre créneau.
const SESSION_OPEN_TIME = '13:00';
const SIM_START_TIME = '13:10';
const SIM_END_TIME = '14:40';

// Date forcée au format AAAA-MM-JJ (utile pour une séance de test).
// Laisser vide en production : la date est alors calculée automatiquement.
const SESSION_DATE_OVERRIDE = '';

// ---------------------------------------------------------------------------
// Paramètres de la simulation
// ---------------------------------------------------------------------------
const DEVELOPER_CAPACITY_HOURS = 8;   // heures simulées par Developer (16 h par équipe de 2)
const TASK_MAX_HOURS = 2;             // durée maximale d'une tâche (anti-triche)
const SWAP_TTL_SECONDS = 300;         // validité d'une proposition d'échange
const CHAT_MAX_LENGTH = 500;          // longueur maximale d'un message
const CHAT_MIN_INTERVAL_SECONDS = 2;  // anti-flood simple
const MAX_EVENTS_PER_TEAM = 7;        // événements répartis sur 85 minutes
const EVENT_MIN_GAP_SECONDS = 300;    // écart minimal entre deux événements
const AUTO_BLOCKAGE_THROTTLE_SECONDS = 1200;  // écart minimal entre deux blocages auto par équipe
const AUTO_BLOCKAGE_PROBABILITY = 2;           // probabilité (%) de déclenchement par tick éligible
const AUTO_BLOCKAGE_GRACE_SECONDS = 600;       // délai après le début avant le premier blocage auto
const POLL_INTERVAL_MS = 3000;        // synchronisation nominale AJAX

// ---------------------------------------------------------------------------
// Sécurité
// ---------------------------------------------------------------------------
const SESSION_NAME = 'workflowit_session';
const SESSION_IDLE_SECONDS = 10800;   // 3 heures
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 300;
const LOGIN_CONFIRM_TTL_SECONDS = 120; // validité de la confirmation de connexion
const API_RATE_LIMIT_PER_SECOND = 25; // seuil de sécurité (limite LWS : 30)
const PASSWORD_REQUEST_COOLDOWN_SECONDS = 600; // anti-spam des demandes d’oubli

// ---------------------------------------------------------------------------
// Divers
// ---------------------------------------------------------------------------
const MAX_JSON_BYTES = 2097152;       // 2 Mo : protège contre les fichiers absurdes
const RESET_WORD = 'REINITIALISER';
