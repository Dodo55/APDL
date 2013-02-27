<?php
namespace APDL;
define("APDL_LOGLEVEL", L_DEBUG);

setvar("mysql_charset", "UTF8");
setvar("mysql_collation", "utf8-hungarian-ci");
setvar("mysqli_persistent", true);

function apdl_handle_safe_die() {
    //Handle die by FATAL level APDL errors
    //change this if you want to control how the program should die in production mode
    header('HTTP/1.1 500 Internal Server Error');
    die();
}

?>