<?php

session_start();

date_default_timezone_set($_ENV["TIMEZONE"]);

define("DB__HOST", $_ENV["DB_HOST"]);

define("DB__USER", $_ENV["DB_USER"]);

define("DB__PASS", $_ENV["DB_PASS"]);

define("DB__NAME", $_ENV["DB_NAME"]);

define("APPROOT", dirname(__DIR__) . "/");

define("PUBLICROOT", dirname(dirname(__DIR__)) . "/public/");

define("URLROOT", $_ENV["URL"]);