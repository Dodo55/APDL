<?php

namespace APDL;

class DB_RECORD extends BASEOBJECT {

    protected static $__table;
    protected $__dbtable, $__recdata = array(), $__efields = array();
    public $__exists = false, $__key;

    public function __construct($data) {
        $this->__dbtable = static::$__table;
        if (is_array($data)) {
            $this->__recdata = (array)$data;
        } else {
            $res = call_user_func_array(array(__CLASS__, "Get"), func_get_args())->One(true);

            $this->__recdata = $res;
            if (is_array($res) && !empty($res)) {
                $this->MarkAsExisting();
            }
        }
    }

    public function MarkAsExisting() {
        $this->__exists = true;
        $this->__key = $this->{$this->GetPKField()};
    }

    public static function From($table) {
        static::$__table = $table;
    }

    public static function Get($val = "", $col = false, $order = "", $limit = "") {
        $db = db_get_active();
        if ($db) {
            if ($col == false) {
                $col = $db->get_pkey(static::$__table);
            }
            if ($val) {
                $filter = array("`{0}`='{1}'", array($col, $val));
            } else {
                $filter = "";
            }
            $res = $db->query(db_querybuilder(db_get_active(), "select", static::$__table, array("where" => $filter,
                "order" => $order,
                "limit" => $limit
            )));
        } else {
            $res = FALSE;
        }
        return new DB_RESULT($res, __CLASS__);
    }

    public static function Where($where, $values, $order = "", $limit = "") {
        $db = db_get_active();
        $res = $db->query(db_querybuilder(db_get_active(), "select", static::$__table, array("where" => array($where, $values),
            "order" => $order,
            "limit" => $limit
        )));
        return new DB_RESULT($res, __CLASS__);
    }

    public function &__get($var) {
        if (isset($this->__efields[$var])) {
            return $this->__efields[$var][1];
        }
        return $this->__recdata[$var];
    }

    public function __set($var, $val) {
        if (!is_array($this->__recdata)) {
            log(print_r($this->__recdata, 1), L_FATAL);
        }
        if (db_get_active()->check_field($this->__dbtable, $var)) {
            if (isset($this->__efields[$var])) {
                $this->__efields[$var][1] = $val;
            } else {
                $this->__recdata[$var] = $val;
            }
        } else {
            log("Trying to set non existing field '$var' on record of table $this->__dbtable!", L_WARNING);
        }
    }

    public function GetPKField($manualkey=false) {
        $pk = db_get_active()->get_pkey($this->__dbtable);
        if (!$pk) {
            log("No primary key field in table $this->__dbtable!",L_WARNING);
        }
        return $pk;
    }

    public function Exists() {
        return $this->__exists;
    }

    public function Save() {
        $db = db_get_active();
        foreach ($this->__efields as $field => $data) {
            $encoder = get_encoder($data[0]);
            $this->__recdata[$field] = $encoder->encode($data[1]);
        }
        if ($this->__exists) {
            log("Updating existing record with primary key " . $this->__key . " in table " . $db->prefix_table($this->__dbtable));
            $db->query(db_querybuilder($db, "update", $this->__dbtable, array(
                "values" => $this->__recdata,
                "where" => array(
                    "`" . $this->GetPKField() . "` = '" . $db->escape($this->__key) . "'", ""
                )
            )));
            $this->__key = $this->{$this->GetPKField()};
        } else {
            $db->query(db_querybuilder($db, "insert", $this->__dbtable, $this->__recdata));
            $ak = $db->get_resource()->insert_id;
            if ($ak) {
                $this->{$this->GetPKField()} = $ak;
            }
            $this->__key = $this->{$this->GetPKField()};
            $this->__exists = true;
        }
    }

    public
    function Delete() {
        if ($this->Exists()) {
            $db = db_get_active();
            log("Deleting record with primary key " . $this->__key . " in table " . $db->prefix_table($this->__dbtable));
            $t = $this->__dbtable;
            $db->query(db_querybuilder($db, "delete", $t, array(
                "where" => array(
                    $this->__pkselector(), ""
                )
            )));
            $this->__exists = false;
        } else {
            log("Delete operation called on non existing record", L_ERROR);
        }
    }

    public function __pkselector() {
        return "`" . $this->GetPKField() . "` = '" . db_get_active()->escape($this->__key) . "'";
    }

    public function EncField($field, $default = false, $encoding = "json") {
        $encoder = get_encoder($encoding);

        if ($this->Exists() && $encoder->check($this->__recdata[$field])) {
            $data = $encoder->decode($this->__recdata[$field]);
        } else {
            if ($default === false) {
                $default = new \stdClass();
            }
            $data = $default;
        }
        $this->__efields[$field] = array($encoding, $data);
    }

    public static function Insert($data) {
        $class = __CLASS__;
        $new = new $class($data);
        $new->save();
        return $new;
    }

}

?>