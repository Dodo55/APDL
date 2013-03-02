<?php
namespace APDL;
define("APDL_LOGLEVEL", L_DEBUG); //Logging level, log entries with higher classification id won't even be stored
define("APDL_SYSMODE", SYSMODE_DEBUG_BACKTRACE); //SYSMODES: SYSMODE_PRODUCTION, SYSMODE_DEBUG, SYSMODE_DEBUG_BACKTRACE
define("APDL_HANDLE_PHPERR", TRUE); //Let APDL handle PHP error reporting

setvar("mysql_charset", "UTF8");
setvar("mysql_collation", "utf8-hungarian-ci");
setvar("mysqli_persistent", true);

function handle_safe_die() {
    //Handle die by FATAL level APDL errors
    //change this if you want to control how the program should die in production mode
    header('HTTP/1.1 500 Internal Server Error');
    die();
}

function rewritepaths($sender,$path) {
    //If some URL rewrite is in effect, you can manipulate the local paths here before transforming them to URL's,
    //replicating the rewrite rules backwards
    return $path; //Always return the path, or else URL generation will break!
}

?>