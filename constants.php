<?php

/**
 * Application's constants
 */

// Project constants
const PROJECT_NAME = 'Kriit';
const PROJECT_SESSION_ID = 'SESSID_KRIIT'; // For separating sessions of multiple Halo projects running on same server
const DEFAULT_CONTROLLER = 'subjects';
const DEVELOPER_EMAIL = 'henno@diarainfra.com'; // Where to send errors
const FORCE_HTTPS = false; // Force HTTPS connections
const DEFAULT_TIMEZONE = 'Europe/Tallinn';
const ENV_DEVELOPMENT = 0;
const ENV_PRODUCTION = 1;
const ACTIVITY_LOGIN = 1;
const ACTIVITY_LOGOUT = 2;
const ACTIVITY_START_TIMER = 3;
const ACTIVITY_SOLVED_EXERCISE = 4;
const ACTIVITY_ALL_SOLVED = 5;
const ACTIVITY_SOLVED_AGAIN_THE_SAME_EXERCISE = 6;
const ACTIVITY_TIME_UP = 7;
const ACTIVITY_START_EXERCISE = 8;
const ACTIVITY_CREATE_GROUP = 9;
const ACTIVITY_CREATE_SUBJECT = 10;
const ACTIVITY_CREATE_ASSIGNMENT = 11;
const ACTIVITY_UPDATE_ASSIGNMENT = 12;
const ACTIVITY_DELETE_ASSIGNMENT = 13;
const ACTIVITY_ADD_USER = 14;
const ACTIVITY_UPDATE_USER = 15;
const ACTIVITY_DELETE_USER = 16;
const ACTIVITY_SUBMIT_ASSIGNMENT = 17;

const IS_ID = 1;
const IS_INT = 2;
const IS_0OR1 = 3;
const IS_ARRAY = 4;
const IS_STRING = 5;
const IS_DATE = 6;

const ASSIGNMENT_STATUS_NOT_SUBMITTED = 1;
const ASSIGNMENT_STATUS_WAITING_FOR_REVIEW = 2;
const ASSIGNMENT_STATUS_GRADED = 3;

const PERMISSION_LEVEL_STUDENT = 1;
const PERMISSION_LEVEL_TEACHER = 2;
