<?php
namespace APDL;
class DBCONF extends DB_RECORD {
    public function __construct($data=array()){
        call_user_func_array(array($this, 'parent::__construct'), func_get_args());
        $this->EncField("val");
    }
    public static function load($table = false) {
        if (!$table) {
            $table = sysvar("dbconf_table");
        }
        static::From($table);
        $vars = static::All();
        if (is_array($vars)) {
            foreach ($vars as $var) {
                setvar($var->var, $var->val);
            }
        } else {
            log("Database error, cannot load configuration from database",L_ERROR);
        }
    }
}
log("Database based configuration parser loaded", Log::L_INFO);