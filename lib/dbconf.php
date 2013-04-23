<?php
namespace APDL;
class DBCONF extends DB_RECORD {
    protected static $__expanded;

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
            log("Database error, cannot load configuration from database", L_ERROR);
        }
    }
}

DBCONF::Expand("val");

log("Database based configuration parser loaded", Log::L_INFO);