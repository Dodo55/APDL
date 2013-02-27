<?php
namespace APDL;

/*
*   Bind common core class functions to standalone functions
*/

function setvar($var, $val) {
    APDL::Setvar($var, $val,false);
}

function sysvar($var) {
    return APDL::Sysvar($var,false);
}

function log($message, $level = Log::L_DEBUG) {
    Log::log($message, $level);
}

function dump($obj) {
    Log::log("<pre>".print_r($obj,1)."</pre>",L_DUMP);
}

function set_codetracker($s) {
    APDL::$CTRACK = $s;
}

function set_logging($l) {
    APDL::$LOGLEVEL = $l;
}

function apdl_die() {
    APDL::die_on_fatal();
}