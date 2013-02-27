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
            $r->__exists = true;
            $r->__key = $r->{$r->GetPKField()};
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

}

function db_querybuilder($conn, $action, $table, $data = "") {
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

function db_filterbuilder($conn, $arr) {
    $filter = $arr[0];
    $vals = $arr[1];
    return preg_replace_callback("#\{(.+?)\}#", function ($matches) use ($vals, $conn) {
        return $conn->escape($vals[$matches[1]]);
    }, $filter);
}

?>