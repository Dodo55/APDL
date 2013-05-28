<?php

namespace APDL;

class DB_RECORD extends BASEOBJECT {

    protected static $__table; //Removed to force redeclaration: $__related = array(), $__expanded = array();
    protected $__dbtable, $__recdata = array(), $__efields = array();
    public $__exists = false, $__key;

    //Override with "call_user_func_array(array($this, 'parent::__construct'), func_get_args());" !
    public function __construct($data = array()) {
        $this->__dbtable = static::$__table;
        if (is_array($data)) {
            $this->__recdata = (array)$data;
        } elseif (func_get_arg(0) != "" || func_get_arg(0) === 0 || func_get_arg(0) == "0") {
            $args = array_replace(array("", "", "", "1"), func_get_args());
            $res = call_user_func_array(array(get_called_class(), "Get"), $args)->One(true);
            $this->__recdata = $res;
            if (is_array($res) && !empty($res)) {
                $this->MarkAsExisting();
            }
        }
        if (property_exists(get_called_class(), "__expanded")) {
            foreach (static::$__expanded as $field => $expdata) {
                $this->EncField($field, $expdata[0], $expdata[1]);
            }
        }
    }

    public function MarkAsExisting() {
        $this->__exists = true;
        if ($this->GetPKField()) {
            $this->__key = $this->{$this->GetPKField()};
        }
    }

    public static function From($table) {
        static::$__table = $table;
    }

    public static function Get($val = "", $col = false, $order = "", $limit = "") {
        $db = db_get_active();
        if ($db) {
            if ($col == false) {
                $pk = $db->get_pkey(static::$__table);
                if ($pk) {
                    $col = $pk;
                } elseif ($val) {
                    return new DUMMY("No primary key column in table '" . static::$__table . "', cannot query without specifying selector column!", L_ERROR);
                }
            }
            if ($val || $val === 0 || $val == "0") {
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
        return new DB_RESULT($res, get_called_class());
    }

    public static function All($order = "", $limit = "", $index = false) {
        return static::Get("", false, $order, $limit)->multi();
    }

    public static function Count() {
        $conn = db_get_active();
        $res = array_values($conn->fetch($conn->query("SELECT COUNT(*) from " . $conn->prefix_table(static::$__table))));
        return $res[0];
    }

    public static function Where($where, $values, $order = "", $limit = "") {
        $db = db_get_active();
        if ($db) {
            $res = $db->query(db_querybuilder(db_get_active(), "select", static::$__table, array("where" => array($where, $values),
                "order" => $order,
                "limit" => $limit
            )));
            return new DB_RESULT($res, get_called_class());
        } else {
            return FALSE;
        }
    }

    public function &__get($var) {
        if (isset(static::$__related) && !empty(static::$__related[$var])) {
            return static::$__related[$var]->get($this->__recdata[$var]);
        }
        if (isset($this->__efields[$var])) {
            return $this->__efields[$var][1];
        } elseif (db_get_active()->check_field($this->__dbtable, $var)) {
            return $this->__recdata[$var];
        }
        log("No column '$var' in table $this->__dbtable", L_ERROR);
        $ret = false;
        return $ret;
    }

    public function __set($var, $val) {
        if (db_get_active()->check_field($this->__dbtable, $var)) {
            if (property_exists(get_called_class(), "__related") && isset(static::$__related[$var]) && is_object($val) && is_subclass_of($val, "\\APDL\\DB_RECORD")) {
                if (get_class($val) == static::$__related[$var]->getClass()) {
                    $this->__recdata[$var] = $val->__key;
                } else {
                    log("Trying to assign a wrong type of related record! Expected: " . static::$__related[$var]->getClass() .
                        " Got: " . get_class($val), L_ERROR);
                }
            } elseif (isset($this->__efields[$var])) {
                $this->__efields[$var][1] = $val;
            } else {
                $this->__recdata[$var] = $val;
            }
        } else {
            log("Trying to set non existing field '$var' on record of table $this->__dbtable!", L_WARNING);
        }
    }

    public function Fill($input) {
        if (is_array($input)) {
            foreach ($input as $var => $val) {
                if (db_get_active()->check_field($this->__dbtable, $var)) {
                    if (isset($this->__efields[$var])) {
                        $this->__efields[$var][1] = $val;
                    } else {
                        $this->__recdata[$var] = $val;
                    }
                }
            }
        } else {
            log("Trying to fill record with invalid input (input must be an associative array)");
        }
    }

    public
    function &__exposedata() {
        return $this->__recdata;
    }

    public
    function GetPKField() {
        return db_get_active()->get_pkey($this->__dbtable);
    }

    public static function SGetPKField() {
        return db_get_active()->get_pkey(static::$__table);
    }

    public
    function Exists() {
        return $this->__exists;
    }

    public function Copy() {
        $copy = new static($this->__recdata);
        if ($pk = $this->GetPKField()) {
            $copy->$pk = null;
        }
        return $copy;
    }

    public
    function Save() {
        $db = db_get_active();
        foreach ($this->__efields as $field => $data) {
            $encoder = get_encoder($data[0]);
            $this->__recdata[$field] = $encoder->encode($data[1]);
        }
        if ($this->__exists) {
            log("Updating existing record with primary key '" . $this->__key . "' in table " . $db->prefix_table($this->__dbtable));
            $db->query(db_querybuilder($db, "update", $this->__dbtable, array(
                "values" => $this->__recdata,
                "where" => array(
                    $this->GetRecordSelector(), ""
                )
            )));
            if ($this->GetPKField()) {
                $this->__key = $this->{$this->GetPKField()};
            }
        } else {
            $db->query(db_querybuilder($db, "insert", $this->__dbtable, $this->__recdata));
            $ak = $db->get_resource()->insert_id;
            if ($ak) {
                $this->{$this->GetPKField()} = $ak;
            }
            $this->MarkAsExisting();
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
                    $this->GetRecordSelector(), ""
                )
            )));
            $this->__exists = false;
        } else {
            log("Delete operation called on non existing record", L_ERROR);
        }
    }

    public
    function GetRecordSelector() {
        if ($this->GetPKField()) {
            return "`" . $this->GetPKField() . "` = '" . db_get_active()->escape($this->__key) . "'";
        } else {
            log("No primary key column in table '$this->__dbtable', using whole record data as selector!", L_WARNING);
            $selector = array();
            foreach ($this->__recdata as $col => $val) {
                $selector[] = "`" . $col . "` = '" . db_get_active()->escape($val) . "'";
            }
            return implode(" AND ", $selector);
        }
    }

    public
    function EncField($field, $default = false, $encoding = "json") {
        $encoder = get_encoder($encoding);

        if (!empty($this->__recdata[$field]) && $encoder->check($this->__recdata[$field])) {
            $data = $encoder->decode($this->__recdata[$field]);
        } else {
            if ($default === false) {
                $default = new \stdClass();
            }
            $data = $default;
        }
        $this->__efields[$field] = array($encoding, $data);
    }

    public static function Relate($field, $class) {
        if (!property_exists(get_called_class(), "__related")) {
            log("To use relations on this class('" . get_called_class() . "'), 'protected static \$__related' must be declared in it!", L_ERROR);
            return false;
        }
        if (is_subclass_of(new $class, "\\APDL\\DB_RECORD")) {
            if ($class::SGetPKField()) {
                static::$__related[$field] = new DB_RELATIONSTORE($class);
                return true;
            } else {
                log("Classes pointing to tables without a primary key cannot be used as relation mapping targets!", L_ERROR);
                return false;
            }
        } else {
            log("$class is not a subclass of \\APDL\\DB_RECORD and because of this cannot be used as a relation mapping target class!", L_ERROR);
            return false;
        }
    }

    public static function Expand($field, $default = false, $encoding = "json") {
        if (!property_exists(get_called_class(), "__expanded")) {
            log("To use expanded fields in this class('" . get_called_class() . "'), 'protected static \$__expanded' must be declared in it!", L_ERROR);
        } else {
            static::$__expanded[$field] = array($default, $encoding);
        }
        return true;
    }

    public
    static function Insert($data) {
        $class = get_called_class();
        $new = new $class($data);
        $new->save();
        return $new;
    }
}

//Trigger autoload of db_utils
$null = new DB_RESULT("", "");

?>