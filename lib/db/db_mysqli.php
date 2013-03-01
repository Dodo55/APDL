<?php

namespace APDL;

/**
 * MySQLi Interface for APDL DB LAYER
 */
class DB_MYSQLi extends DB_CONNECTION {

    public $type = "MySQLi", $state = 0, $prefix = "";
    private $conn, $pkcache;

    public function __construct($host, $user, $pass, $db, $prefix = "") {
        $connect_persistent = sysvar("mysqli_persistent");
        if ($connect_persistent) {
            $host = "p:" . $host;
        }
        $conn = new \mysqli($host, $user, $pass, $db);
        if ($connect_persistent && !$conn->ping()) {
            $conn = new \mysqli($host, $user, $pass, $db);
        }
        if ($conn->connect_errno) {
            log("MySQLi connection error: " . $conn->connect_error, Log::L_ERROR);
            $this->state = 0;
        } else {
            $conn->set_charset(sysvar("mysql_charset"));
            $conn->query("SET CHARACTER SET '" . sysvar("mysql_charset") . "' COLLATE '" . sysvar("mysql_collation") . "'");
            $this->state = 1;
            $this->conn = $conn;
            $this->prefix = $prefix;
            $this->db = $db;
        }
    }

    public function query($query) {
        $conn = $this->conn;
        log("MySQLi Query: " . $query);
        $result = $conn->query($query);
        if ($conn->errno) {
            log("MySQLi error: " . $conn->error, Log::L_ERROR);
        }
        return $result;
    }

    public function fetch($result) {
        if ($result != false) {
            return $result->fetch_assoc();
        } else {
            log("MySQLi error: trying to fetch invalid results", Log::L_ERROR);
            return false;
        }
    }

    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }

    public function get_resource() {
        return $this->conn;
    }

    public function get_pkey($table) {
        if (!isset($this->pkcache[$table]) || !$this->pkcache[$table]) {
            $res = $this->fetch($this->query("SHOW KEYS FROM {$this->prefix_table($table)} WHERE Key_name = 'PRIMARY'"));
            $this->pkcache[$table] = $res['Column_name'];
        }

        return $this->pkcache[$table];
    }



    protected function map_table($table) {
        $q = $this->query("SELECT group_concat(column_name order by ordinal_position) FROM information_schema.columns WHERE table_schema = '" . $this->db . "' AND table_name = '" . $this->prefix_table($this->escape($table)) . "'");
        if ($q != false) {
            $r = $q->fetch_row();
            $this->tablemap[$table] = array_map('strtolower', explode(",", $r[0]));
            log("Mapping table '$table'");
        } else {
            log("MySQLi error: Can't map table '$table', it does not exist", L_ERROR);
        }
    }


}

?>