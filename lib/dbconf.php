<?php
namespace APDL;
class DBCONF extends DB_RECORD {
    public static function load($table = false) {
        if (!$table) {
            $table = sysvar("dbconf_table");
        }
        self::From($table);
        $vars = self::Get()->Multi();
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