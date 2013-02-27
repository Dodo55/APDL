<?php

namespace APDL;

class CONNECTION_MANAGER {

    private static $connections = array(), $active = "";

    public static function connect($conn, $name = "default", $die_on_fault = true) {
        if ($conn->state == 1) {
            log("A(n) $conn->type connection '$name' has been opened sucessfully");
            self::$connections[$name] = $conn;
            if ($name == "default") {
                self::set_active($name);
            }
        } elseif ($die_on_fault) {
            log("Cannot connect to database",L_FATAL);
        }
    }

    public static function get_conn($name) {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        } else {
            log("Requesting invalid DB connection instance: $name", LOG::L_ERROR);
            return false;
        }
    }

    public static function set_active($conn) {
        if (self::get_conn($conn)) {
            self::$active = $conn;
        } else {
            log("Unable to set invalid or unsuccessful DB connection as active: $conn", LOG::L_ERROR);
        }
    }

    public static function get_active() {
        if (self::$active) {
            return self::$connections[self::$active];
        } else {
            log("No active DB connection, unable to execute operation!", LOG::L_ERROR);
            return new DUMMY;
        }
    }

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

abstract class DB_CONNECTION {

    public $state, $type, $prefix, $tablemap, $db;

    public function prefix_table($table) {
        if ($this->prefix) {
            return $this->prefix . "_" . $table;
        }
        return $table;
    }

    public function check_field($table, $field) {
        if (isset($this->tablemap[$table])) {
            if (in_array(strtolower($field), $this->tablemap[$table])) {
                return true;
            }
            return false;
        } else {
            $this->map_table($table);
            return $this->check_field($table, $field);
        }
    }

}

?>