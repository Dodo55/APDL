<?php
namespace APDL;

//TODO: SERVER IP
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    define("APDL_SERVER_HOST", $_SERVER['HTTP_X_FORWARDED_HOST']);
} else {
    define("APDL_SERVER_HOST", $_SERVER['HTTP_HOST']);
}

define("APDL_HTTP_REQUEST", $_SERVER['REQUEST_URI']);
define("APDL_HTTP_DOCROOT", rtrim($_SERVER['DOCUMENT_ROOT'],"/"));
define("APDL_INDEX_FILE", $_SERVER['SCRIPT_FILENAME']);
define("APDL_OUTPUT_SCREEN", 0);
define("APDL_OUTPUT_FILE", 1);
define("APDL_OUTPUT_SESSION", 2);

define("APDL_E_MODULE_NOT_FOUND", 1);
define("APDL_E_MODULE_OUTDATED", 2);
define("APDL_E_CORE_OUTDATED", 3);
define("APDL_E_DB", 4);
define("APDL_E_PHP", 5);
define("APDL_E_TYPEERROR", 5);
define("APDL_E_CONTROLLER_NOT_EXISTS", 6);

const L_FATAL = 1;
const L_ERROR = 2;
const L_WARNING = 3;
const L_INFO = 4;
const L_DUMP = 5;
const L_DEBUG = 6;
const OUTPUT_RETURN = 0;
const OUTPUT_DIE = 1;
const OUTPUT_DIE_CLEAN = 2;
const SYSMODE_PRODUCTION = 1;
const SYSMODE_DEBUG = 2;
const SYSMODE_DEBUG_BACKTRACE = 3;