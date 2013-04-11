<?php
namespace APDL;
define("APDL_INTERNALCALL", "I know what I do. You'll let me do it and don't warn me about any unusual.");
/**
 * @class APDL
 * APDL Core
 */
class APDL {

    const IS_RUNNING = true;
    protected static $BVER, $AUTOLOAD = array();
    public static $ERRORS = array();

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
    private static $reserved_vars = array("__encoders", "__session_logging_active");

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
            Log::log("Setting sysvar $var to value: $vdump");
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
        if (APDL_LOG_FATALS === true) {
            Log::dumplog(APDL_OUTPUT_FILE);
        }
        if (APDL_SYSMODE >= SYSMODE_DEBUG) {
            Log::dumplog(APDL_OUTPUT_SCREEN, OUTPUT_DIE_CLEAN);
        } else {
            if (!APDL::sysvar("__apdl_dead", APDL_INTERNALCALL)) {
                APDL::setvar("__apdl_dead", true, APDL_INTERNALCALL);
                handle_safe_die(array_pop(self::$ERRORS));
            }
        }
    }

    public static function runtime() {
        return substr((float)(@microtime(true) - APDL_START_MT), 0, 5);
    }

    public static function autoload($class) {
        $class = strtoupper($class);
        if (!empty(self::$AUTOLOAD[$class])) {
            $file = self::$AUTOLOAD[$class];
            require($file);
            log("Autoloader loaded class '$class'", L_INFO);
        } else {
            log("Class '$class' is not registered for autoloading!", L_DEBUG);
        }
    }

    public static function register_class($classname, $file) {
        $classname = strtoupper($classname);
        self::$AUTOLOAD[$classname] = $file;
        log("Autoloader registered class '$classname' to be loaded from file '$file'");
    }

    public static function get_binary_version() {
        if (empty(self::$BVER)) {
            self::$BVER = get_binary_version(APDL_VERSION);
        }
        return self::$BVER;
    }
}

spl_autoload_register('\APDL\APDL::autoload');