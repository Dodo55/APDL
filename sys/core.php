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
    private static $reserver_vars = array("__encoders");

    /**
     * Set APDL Sysvar
     * @param string $var Sysvar name
     * @param string $val Sysvar value
     */
    public static function Setvar($var, $val, $internalcall = false) {
        if (!in_array($var, self::$reserver_vars) || $internalcall == APDL_INTERNALCALL) {
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

    /**
     * Echo Skeletoned HTML
     *
     * @param array/string $content body/(body,style,title)
     * @param bool $die = FALSE
     * @param bool $obclean = TRUE
     */
    public static function EchoHTML($content, $die = FALSE, $obclean = TRUE) {
        if (is_array($content)) {
            $body = $content["body"];
            $style = $content["style"];
            $title = $content["title"];
            if (isset($content["head"])) {
                $head = $content["head"];
            }
        } else {
            $body = $content;
        }
        $skeleton = file_get_contents(APDL_SYSROOT . "/assets/skeleton.html");
        if (isset($body)) {
            $skeleton = str_replace("<!--{APDL_BODY}-->", $body, $skeleton);
        } else {
            Log::log("EchoHTML(): No body specified", Log::L_WARNING);
        }
        if (isset($head)) {
            $skeleton = preg_replace("#<head>.*?</head>#ims", $head, $skeleton);
        } else {
            if (isset($style)) {
                $skeleton = str_replace("<!--{APDL_STYLE}-->", "<style>" . $style . "</style>", $skeleton);
            }
            if (isset($title)) {
                $skeleton = str_replace("<!--{APDL_TITLE}-->", "<title>" . $title . "</title>", $skeleton);
            }
        }
        if ($obclean) {
            if (ob_get_level() > 0) {
                ob_clean();
            }
        }
        echo ($skeleton);
        if ($die) {
            ob_end_flush();
            die();
        }
    }

    public static function die_on_fatal() {
        if (APDL_DEBUG) {
            Log::dumplog();
        } else {
            if (!sysvar("apdl_dead")) {
                setvar("apdl_dead", true);
                handle_safe_die();
            }
        }
    }
}