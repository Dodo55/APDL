<?php

namespace APDL;

class DB_RESULT extends BASEOBJECT {

    private $result, $caller;

    public function __construct($result, $caller) {
        $this->result = $result;
        $this->caller = $caller;
    }

    public function one($raw = false) {
        $res = db_get_active()->fetch($this->result);
        if (is_array($res)) {
            if (!$raw) {
                $ret = new $this->caller($res);
                $ret->MarkAsExisting();
                return $ret;
            } else {
                return $res;
            }
        }
        if (!$raw) {
            return new $this->caller(array());
        } else {
            return array();
        }
    }


    public function multi($index = false) {
        $res = array();
        $db = db_get_active();
        while ($data = $db->fetch($this->result)) {
            $r = new $this->caller($data);
            $r->MarkAsExisting();
            if ($index) {
                $res[$data[$index]] = $r;
            } else {
                $res[] = $r;
            }
        }
        return $res;
    }

    public function count() {
        return $this->result->num_rows;
    }

    public function get_result() {
        return $this->result;
    }

}

function db_querybuilder($conn, $action, $table, $data = array()) {
    $actionmap = array("insert" => "INSERT INTO",
        "select" => "SELECT * FROM",
        "update" => "UPDATE",
        "delete" => "DELETE FROM"
    );
    $query = $actionmap[$action] . " `" . $conn->prefix_table($table) . "` ";
    if ($action == "insert") {
        $cols = "(";
        $vals = ") VALUES (";
        $i = 0;
        foreach ($data as $col => $val) {
            $i++;
            $cols .= "`" . $conn->escape($col) . "`";

            $vals .= "'" . $conn->escape($val) . "'";

            if (count($data) > $i) {
                $vals .= ", ";
                $cols .= ", ";
            }
        }
        $query .= $cols . $vals . ")";
    }

    if ($action == "update") {
        $values = array();
        foreach ($data['values'] as $field => $val) {
            $values[] = "`" . $conn->escape($field) . "`='" . $conn->escape($val) . "'";
        }
        $query .= " SET " . implode(", ", $values) . " ";
    }

    if ($action == "select" || $action == "update" || $action == "delete") {
        if (isset($data['where']) && $data['where']) {
            $query .= "WHERE " . db_filterbuilder($conn, $data['where']);
        }

        if (isset($data['order']) && $data['order']) {
            list($field, $mode) = explode(" ", $data['order']);
            $query .= " ORDER BY `" . $conn->escape($field) . "` " . $conn->escape($mode);
        }
        if (isset($data['limit']) && $data['limit']) {
            $query .= " LIMIT " . $conn->escape($data['limit']);
        }
    }
    return $query;
}

class DB_RELATIONSTORE {
    protected $instances = array();
    protected $class;

    public function getClass() {
        return $this->class;
    }

    public function __construct($class) {
        $this->class = $class;
    }

    public function &get($key) {
        if (empty($this->instances[$key])) {
            $this->instances[$key] = new $this->class($key);
        }
        if (!$this->instances[$key]->Exists()) {
            log("The associated record doesn't exist! (Class: $this->class, Key: $key)", L_WARNING);
        }
        return $this->instances[$key];
    }
}

function db_filterbuilder($conn, $arr) {
    $filter = $arr[0];
    $vals = $arr[1];
    if (is_array($vals)) {
        foreach (array_keys($vals) as $key) {
            $filter = str_ireplace("{" . $key . "}", $conn->escape($vals[$key]), $filter);
        }
        return $filter;
    }
    return $filter;
    /*REGEX Solution proven to be slower
    return preg_replace_callback("#\{(.+?)\}#", function ($matches) use ($vals, $conn) {
        return $conn->escape($vals[$matches[1]]);
    }, $filter);*/
}

?>