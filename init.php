<?php

/**
 * Advanced PHP Development Library
 */

namespace APDL;

//Start timer
define("APDL_START_MT", @microtime(true));

//Error reporting off by default
ini_set('display_errors', '0');
error_reporting(0);

//Basic constants
define("APDL_SYSROOT", realpath(__DIR__)); //DO NOT DISTURB
define("APDL_VERSION", "experimental r12");

//Load system core
require_once(APDL_SYSROOT . "/sys/const.php");
require_once(APDL_SYSROOT . "/sys/core.php");
require_once(APDL_SYSROOT . "/sys/log.php");
require_once(APDL_SYSROOT . "/sys/fbind.php");
//TODO: Minimize IO, group these includes in one file for production releases

//Load base configuration
require_once(APDL_SYSROOT . "/conf/base.php");

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

//Set logging level based on configuration
set_logging(APDL_LOGLEVEL);

//Set codetracker to APDL-Init (used for logging)
set_codetracker("APDL-Init");

//Load libs
log("***APDL " . APDL_VERSION . "***", L_INFO);
log("Init @" . APDL_SERVER_HOST . " T=" . time() . ", S=" . APDL_INDEX_FILE . ", DR=" . APDL_HTTP_DOCROOT, L_INFO);
log("Sysmode is: " . APDL_SYSMODE, L_INFO);
log("Logging level is: " . APDL_LOGLEVEL, L_INFO);
log("HTTP Request is: " . APDL_HTTP_REQUEST, L_INFO);
log("Loading base libraries...", L_INFO);

require_once(APDL_SYSROOT . "/lib/base.php");
log("Base Object loaded", Log::L_INFO);

require_once(APDL_SYSROOT . "/lib/http.php");
log("HTTP Library loaded", Log::L_INFO);
HTTP::__bind("rewritepaths", "\\apdl\\rewritepaths");
define("APDL_HTTP_WEBROOT", HTTP::find_webroot());

log("Detected Webroot is: " . APDL_HTTP_WEBROOT, L_INFO);

require_once(APDL_SYSROOT . "/lib/encoding.php");
log("Encoders Loaded", Log::L_INFO);

require_once(APDL_SYSROOT . "/lib/dtc.php");
log("Data Containers loaded", Log::L_INFO);

require_once(APDL_SYSROOT . "/lib/db/db.php");
log("Database layer loaded", Log::L_INFO);

require_once(APDL_SYSROOT . "/lib/output.php");
log("Output generator & HTML5 output class loaded", Log::L_INFO);

require_once(APDL_SYSROOT . "/lib/routing.php");
log("Routing loaded", Log::L_INFO);

require_once(APDL_SYSROOT . "/lib/controller.php");
log("Controller class loaded", Log::L_INFO);


set_codetracker("Global");