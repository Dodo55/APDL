<?php
/*
 * APDL Configuration
 */

namespace APDL; //DO NOT DISTURB

//Do not comment out defines except APDL_HTTP_WEBROOT!
//Use true/false instead of 0/1!

define("APDL_LOGLEVEL", L_DEBUG); //Logging level, log entries with higher classification id won't even be stored
define("APDL_LOG_TRACKTIME", true); //Track internal time of log events
define("APDL_LOG_FATALS", true); //Log fatal errors to logs/ with debug_backtrace()
define("APDL_HANDLE_PHPERR", true); //Let APDL handle PHP error reporting (if enabled by sysmode)

/*
 * System running modes
 * SYSMODE_PRODUCTION: PHP error reporting disabled, fatal errors are handled by apdl_safe_die()
 * SYSMODE_DEBUG: PHP error reporting enabled, APDL log is dumped with html output on fatal errors
 * SYSMODE_DEBUG_BACKTRACE: SYSMODE_DEBUG + debug_backtrace() on all log entries
 */
define("APDL_SYSMODE", SYSMODE_DEBUG);

//Expose APDL functions (with the apdl_ prefix) and some constants to the global namespace
define("APDL_GLOBAL", true);

//Protocol for URL generation
define("APDL_PROTOCOL","http");

//Uncomment to set webroot manually if auto-detected url is not proper. No trailing slash!
//define("APDL_HTTP_WEBROOT","http://set.it.here/if/needed");
//Alternatively you can use rewritepaths() at the bottom of the file to keep using auto-detection with custom rules

//Timezone
APDL::setvar("default_timezone", "Europe/Budapest"); //Set timezone

//MySQL Options
APDL::setvar("mysql_charset", "UTF8");
APDL::setvar("mysql_collation", "utf8-hungarian-ci");
APDL::setvar("mysqli_persistent", true);

//URL Handling Options
APDL::setvar("url_rewrite", true); //Enable/disable URL rewriting
APDL::setvar("url_route_key", "p"); //Set GET key of url route

//Routing
APDL::setvar("routing_load_from_file", true); //Load rules from routes.php

//Set the table of the DB based variables. Will be prefixed if a table prefix is set
APDL::setvar("dbconf_table", "apdlvars");

/*
 * Callbacks
 */

function handle_safe_die(ERROR $error) {
    //Handle die by FATAL level APDL errors
    //change this if you want to control how the program should die in production mode
    //param $error is an ERROR object with $logentry and $code
    header('HTTP/1.1 500 Internal Server Error');
    die();
}

function rewritepaths($path) {
    //If some URL rewrite is in effect, you can manipulate the local paths here before transforming them to URL's,
    //replicating the rewrite rules backwards
    return false; //Return false if not used, otherwise return the rewritten url
}