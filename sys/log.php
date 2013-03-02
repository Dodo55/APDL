<?php
namespace APDL;
/**
 * APDL Logging
 * @class APDL_Log
 * @package APDL-CORE *
 */
class Log {

    //Obsolate
    const L_FATAL = L_FATAL, L_ERROR = L_ERROR, L_WARNING = L_WARNING, L_INFO = L_INFO, L_DUMP = L_DUMP, L_DEBUG = L_DEBUG;

    static private $entries = array();

    static public function log($message, $level = L_DEBUG) {
        if (APDL::$LOGLEVEL >= $level) {
            $entry = (object)array("level" => $level, "message" => $message, "caller" => APDL::$CTRACK);
            if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
                $entry->backtrace = debug_backtrace();
            }
            self::$entries[] = $entry;
        }
        if ($level == L_FATAL) {
            self::$entries[] = (object)array("level" => L_FATAL, "message" => "---FATAL ERROR, EXECUTION STOPPED---", "caller" => "APDL-Core");
            apdl_die();
        }
    }

    static public function PHPFatal() {
        $err = error_get_last();
        if ($err['type'] == E_CORE_ERROR || $err['type'] == E_COMPILE_ERROR) {
            self::PHPErr("Fatal", $err['message'], $err['file'], $err['line']);
        }
        return;
    }

    static public function PHPErr($errno, $error, $file, $line) {
        switch ($errno) {
            case E_DEPRECATED:
                $errlev = "DEPRECATED";
                $level = L_DEBUG;
                break;
            case E_NOTICE:
                $errlev = "NOTICE";
                $level = L_DEBUG;
                break;
            case E_WARNING:
                $errlev = "WARNING";
                $level = L_WARNING;
                break;
            case E_STRICT:
                //Filter e_strict triggered by common static-nonstatic event manager methods
                if (strpos($error, "__bind") || strpos($error, "__fire")) {
                    return TRUE;
                }
                $errlev = "STRICT";
                $level = L_DEBUG;
                break;
            case "Fatal":
                $errlev = "FATAL";
                $level = L_FATAL;
                break;
            default:
                $errlev = "N/A";
                $level = L_WARNING;
                break;
        }
        self::log("<span style='color: #F08'>[PHP: $errlev]</span> " . $error . " at " . $file . " Line: " . $line, $level);
        return TRUE;
    }

    static public function dumplog($target = APDL_OUTPUT_SCREEN, $die = false) {

        if ($target == APDL_OUTPUT_FILE) {
            $output = "";
            $secount = count(self::$entries);
            foreach (self::$entries as $key => $entry) {
                $output .= "[" . $entry->caller . "]\t" . $entry->message;
                if ($key < $secount - 1) {
                    $output .= "\r\n";
                }
            }
            file_put_contents(APDL_SYSROOT . "/logs/" . time() . strstr(microtime(), " ", TRUE) . ".log", $output);
        } else {
            $body = "<div id='log'><img src='" . webpath(APDL_SYSROOT . "/assets/img/apdl.png") . "' style='display: block;margin:auto'/>
            <table>{CELLS}</table></div>";
            $cells = "";
            foreach (self::$entries as $key => $entry) {
                $contents = $entry->message;
                if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
                    $contents .= "<span class='backtrace'>" . print_r($entry->backtrace, 1) . "</span>";
                }
                $cells .= "<tr class='level_$entry->level'><td>[$entry->caller]</td><td>$contents</td></tr>";
            }
            $body = str_replace("{CELLS}", $cells, $body);
            $html = new HTML5;
            $html->css(webpath(APDL_SYSROOT."/assets/css/log.css"));
            if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
                $html->scriptlink(webpath(APDL_SYSROOT . "/assets/js/jquery.js"));
                $html->scriptlink(webpath(APDL_SYSROOT . "/assets/js/backtrace.js"));
            }
            $html->body = $body;
            echo $html->render($die);
        }
    }

}