<?php
namespace APDL;
define("APDL_INTERNALCALL", "I know what I do. You'll let me do it and don't warn me about any unusual.");
/**
 * @class APDL
 * APDL Core
 */
class APDL {

    /**
     * @static
     * @var string CODE TRACKER GLOBAL VAR
     */
    public static $CTRACK;

    /**
     * @static string CODE TRACKER GLOBAL VAR
     */
    public static $LOGLEVEL;

    /**
     * @static
     * @var array APDL Sysvar Array
     */
    private static $vars;

    /**
     * @static
     * @var array Reserved VAR names
     */
    private static $reserved_vars = array("__encoders");

    /**
     * Set APDL Sysvar
     * @param string $var Sysvar name
     * @param string $val Sysvar value
     */
    public static function Setvar($var, $val, $internalcall = false) {
        if (!in_array($var, self::$reserved_vars) || $internalcall == APDL_INTERNALCALL) {
            self::$vars[$var] = $val;
        } else {
            log("Trying to overwrite reserved sysvar $var!", L_ERROR);
        }
        //Check loglevel to skip typechecks if loglevel too low
        if (APDL_LOGLEVEL >= L_DEBUG && $internalcall != APDL_INTERNALCALL) {
            if (is_string($val) || is_scalar($val)) {
                $vdump = $val;
            } else {
                $vdump = "[" . gettype($val) . "]";
            }
            log("Setting sysvar $var to value: $vdump");
        }
    }

    /**
     * Get APDL Sysvar
     * @param string $var Sysvar name     *
     * @return mixed
     */
    public static function Sysvar($var, $internalcall = false) {
        if ($var == "") {
            Log::log("Empty sysvar request!", Log::L_WARNING);
        }
        if (!isset(self::$vars[$var])) {
            if ($internalcall != APDL_INTERNALCALL) {
                Log::log("Requested sysvar($var) is not set!", Log::L_WARNING);
            }
            return null;
        }
        return self::$vars[$var];
    }


    public static function die_on_fatal() {
        if (APDL_SYSMODE >= APDL_DEBUG) {
            Log::dumplog(APDL_OUTPUT_SCREEN, OUTPUT_DIE);
        } else {
            if (!sysvar("apdl_dead")) {
                setvar("apdl_dead", true);
                handle_safe_die();
            }
        }
    }
}