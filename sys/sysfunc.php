<?php
/*
*   Bind common core class functions to standalone functions
*/
namespace APDL {
    function setvar($var, $val) {
        APDL::Setvar($var, $val, false);
    }

    function sysvar($var) {
        return APDL::Sysvar($var, false);
    }

    function log($message, $level = Log::L_DEBUG, $errorcode = 0) {
        Log::log($message, $level, $errorcode);
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

    function dump_log_to_session() {
        Log::dump_to_session();
    }

    function runtime() {
        return APDL::runtime();
    }

    function internal_url($path) {
        return ROUTING::internal_url($path);
    }

    function webpath($localpath, $protocol = "http") {
        return HTTP::webpath($localpath, $protocol);
    }

    function db_connect($conn, $name = "default", $die_on_fault = true) {
        CONNECTION_MANAGER::connect($conn, $name, $die_on_fault);
    }

    function db_get_conn($name) {
        return CONNECTION_MANAGER::get_conn($name);
    }

    function db_set_active($name) {
        return CONNECTION_MANAGER::set_active($name);
    }

    function db_get_active() {
        return CONNECTION_MANAGER::get_active();
    }

    function register_encoder($name, $class) {
        $encoders = APDL::Sysvar("__encoders", APDL_INTERNALCALL);
        if (!is_array($encoders)) {
            $encoders = array();
        }
        $encoders[$name] = new $class;
        APDL::Setvar("__encoders", $encoders, APDL_INTERNALCALL);
        log("Encoder $class registered", L_INFO);
    }

    function get_encoder($name) {
        $encoders = sysvar("__encoders");
        if (!isset($encoders[$name]) || !is_object($encoders[$name])) {
            log("Requesting invalid encoder!", L_FATAL);
        }
        return $encoders[$name];
    }

    function load_module($mod, $mbv = 0) {
        MODULESTORE::load_module($mod, $mbv);
    }

}