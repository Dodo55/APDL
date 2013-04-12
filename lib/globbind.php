<?php
define("L_FATAL", \APDL\L_FATAL);
define("L_ERROR", \APDL\L_ERROR);
define("L_WARNING", \APDL\L_WARNING);
define("L_INFO", \APDL\L_INFO);
define("L_DUMP", \APDL\L_DUMP);
define("L_DEBUG", \APDL\L_DEBUG);

function apdl_log($message, $level = L_DEBUG, $errorcode = 0) {
    \APDL\Log::log($message, $level, $errorcode);
}

function apdl_dumplog($target = APDL_OUTPUT_SCREEN, $die = false) {
    \APDL\Log::dumplog($target, $die);
}

function apdl_dump($obj) {
    \APDL\dump($obj);
}

function apdl_setvar($var, $val) {
    \APDL\APDL::Setvar($var, $val, false);
}

function apdl_sysvar($var) {
    return \APDL\APDL::Sysvar($var, false);
}

function apdl_die() {
    \APDL\APDL::die_on_fatal();
}

function apdl_url($path) {
    return \APDL\ROUTING::internal_url($path);
}

function apdl_dump_log_to_session() {
    \APDL\Log::dump_to_session();
}

function apdl_runtime() {
    return \APDL\APDL::runtime();
}

function apdl_set_logging($l) {
    \APDL\APDL::$LOGLEVEL = $l;
}

function apdl_clink($c, $args = array()) {
    return \APDL\clink($c, $args);
}

function apdl_webpath($path, $protocol = "http") {
    return \APDL\HTTP::webpath($path, $protocol);
}

function apdl_db_connect($conn, $name = "default", $die_on_fault = true) {
    \APDL\CONNECTION_MANAGER::connect($conn, $name, $die_on_fault);
}

function apdl_db_get_conn($name) {
    return \APDL\CONNECTION_MANAGER::get_conn($name);
}

function apdl_db_set_active($name) {
    return \APDL\CONNECTION_MANAGER::set_active($name);
}

function apdl_db_get_active() {
    return \APDL\CONNECTION_MANAGER::get_active();
}

function apdl_load_module($mod, $mbv = 0,$args) {
    \APDL\MODULESTORE::load_module($mod, $mbv,$args);
}

function apdl_add_route($route, $target) {
    \APDL\ROUTING::add_route($route, $target);
}

function apdl_get_route($target) {
    return \APDL\ROUTING::get_route($target);
}

function apdl_resolve_url($url) {
    return \APDL\ROUTING::resolve_url($url);
}

function apdl_safestr($str){
    return \APDL\safestr($str);
}

function apdl_get_client_ip(){
    return \APDL\get_client_ip();
}

function apdl_register_autoload($class,$file){
    \APDL\APDL::register_class($class,$file);
}

apdl_log("Global bindings are enabled and loaded", L_INFO);