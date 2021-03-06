<?php

/**
 * Advanced PHP Development Library
 */

namespace APDL;

//Protect file from including it multiple times
if (defined("APDL_MUST_RUN")) {
    log("Trying to include APDL loader, but it has been included already!", L_WARNING);
    return;
}

//Start timer
define("APDL_START_MT", @microtime(true));

//Constant for easy file protection (!APDL_MUST_RUN or die())
//It is false and checked negatively as undefined constants are strings=>true
define("APDL_MUST_RUN", false);

//Error reporting ON at startup
ini_set('display_errors', '1');
error_reporting(E_ALL);

//Basic constants
if (defined("APDL_SYMLINKED_ROOT")) {
    //DEFINE THIS BEFORE INCLUDING APDL IF THE WEBPATHS OF APDL WEB ASSETS ARE MESSED UP BY SYMLINK RESOLVING
     define("APDL_SYSROOT",APDL_SYMLINKED_ROOT); 
} else {
    define("APDL_SYSROOT", realpath(__DIR__)); //DO NOT DISTURB
}
define("APDL_VERSION", "alpha 0.0.5-3");

//Load system core
require(APDL_SYSROOT . "/sys/const.php");
require(APDL_SYSROOT . "/sys/sysfunc.php");
require(APDL_SYSROOT . "/sys/core.php");
require(APDL_SYSROOT . "/sys/log.php");

define("APDL_DEFAULT_CONFIG_FILE", APDL_SYSROOT . "/conf.php");
define("APDL_DEFAULT_ROUTES_FILE", APDL_SYSROOT . "/routes.php");

function load_config($config) {
    //Load configuration
    if (file_exists($config)) {
        require($config);
    } else {
        die("Config file doesn't exist!");
    }
}

function init() {
    //E_ALL if DEBUG mode is on
    if (APDL_SYSMODE >= SYSMODE_DEBUG) {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
    }

    //Enable PHP error handling by APDL if enabled
    if (APDL_HANDLE_PHPERR) {
        set_error_handler("\APDL\Log::PHPErr");
        register_shutdown_function("\APDL\Log::PHPFatal");
    }

    log("The time is: " . date('Y.m.d H:i') . " (" . time() . ") in default timezone " . sysvar("default_timezone"), L_INFO);
    log("System mode: " . APDL_SYSMODE . ", logging level: " . APDL::$LOGLEVEL, L_INFO);
    log("HTTP request: " . APDL_HTTP_REQUEST, L_INFO);
    log("Loading base libraries...", L_INFO);

    //Load base libs
    require(APDL_SYSROOT . "/lib/base.php");
    require(APDL_SYSROOT . "/lib/utils.php");
    require(APDL_SYSROOT . "/lib/encoding.php");
    define("APDL_BINARY_VERSION", \APDL\APDL::get_binary_version());

    //Load global bindings if enabled
    if (APDL_GLOBAL === true) {
        require(APDL_SYSROOT . "/lib/globbind.php");
    }

    //Register classes
    log("Registering system classes...", L_INFO);
    APDL::register_class("APDL\\HTTP", APDL_SYSROOT . "/lib/http.php");
    APDL::register_class("APDL\\CONNECTION_MANAGER", APDL_SYSROOT . "/lib/db/db.php");
    APDL::register_class("APDL\\DB_CONNECTION", APDL_SYSROOT . "/lib/db/db.php");
    APDL::register_class("APDL\\DB_MySQLi", APDL_SYSROOT . "/lib/db/db.php");
    APDL::register_class("APDL\\DB_RECORD", APDL_SYSROOT . "/lib/db/db.php");
    APDL::register_class("APDL\\DB_RESULT", APDL_SYSROOT . "/lib/db/db.php");
    APDL::register_class("APDL\\DB_RELATIONSTORE", APDL_SYSROOT . "/lib/db/db.php");
    APDL::register_class("APDL\\OUTPUT", APDL_SYSROOT . "/lib/output.php");
    APDL::register_class("APDL\\HTML5", APDL_SYSROOT . "/lib/output.php");
    APDL::register_class("APDL\\JSON", APDL_SYSROOT . "/lib/output.php");
    APDL::register_class("APDL\\ROUTING", APDL_SYSROOT . "/lib/routing.php");
    APDL::register_class("APDL\\CONTROLLER", APDL_SYSROOT . "/lib/controller.php");
    APDL::register_class("APDL\\ContollerBase", APDL_SYSROOT . "/lib/controller.php");
    APDL::register_class("APDL\\MODULESTORE", APDL_SYSROOT . "/lib/mod.php");
    APDL::register_class("APDL\\MODULE", APDL_SYSROOT . "/lib/mod.php");
    APDL::register_class("APDL\\DBCONF", APDL_SYSROOT . "/lib/dbconf.php");

    //Preload output library to avoid some issue with error handling
    OUTPUT::__ping();

    define("APDL_INITIALIZED", true);
}

function load($config = APDL_DEFAULT_CONFIG_FILE, $routes = APDL_DEFAULT_ROUTES_FILE) {
    $ctmem = APDL::$CTRACK != "" ? APDL::$CTRACK : "Global";
    set_codetracker("APDL-Init");

    if (!defined("APDL_INITIALIZED")) {
        load_config($config);
    } else {
        log("Reloading system...", L_INFO);
    }

    //Set logging level
    $ll = sysvar("logging_level_override", APDL_INTERNALCALL) ? sysvar("logging_level_override", APDL_INTERNALCALL) : APDL_LOGLEVEL;
    set_logging($ll);

    //Set timezone
    date_default_timezone_set(sysvar("default_timezone"));

    if (!defined("APDL_INITIALIZED")) {
        log("***APDL " . APDL_VERSION . "***", L_INFO);
        log("Initializing at server " . APDL_SERVER_HOST . " by script " . APDL_INDEX_FILE, L_INFO);
        init();
        load_config($routes);
    } else {
        log("Logging level set to:" . $ll, L_INFO);
        log("Setting timezone to: " . sysvar("default_timezone"), L_INFO);
    }

    if (!defined("APDL_HTTP_WEBROOT")) {
        define("APDL_HTTP_WEBROOT", HTTP::find_webroot());
        log("Detected webroot is: " . APDL_HTTP_WEBROOT, L_INFO);
    } else {
        log("Using preconfigured webroot: " . APDL_HTTP_WEBROOT, L_INFO);
    }
    if (!defined("APDL_HTTP_SELFURL")) {
        define("APDL_HTTP_SELFURL", APDL_PROTOCOL . "://" . APDL_SERVER_HOST . APDL_HTTP_REQUEST);
    }

    set_codetracker($ctmem);
}
