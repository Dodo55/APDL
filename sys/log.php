<?php
namespace APDL;
/**
 * APDL Logging
 * @class APDL_Log
 * @package APDL-CORE *
 */

class ERROR {
    public $logentry, $code;

    public function __construct(&$logentry, $code) {
        $this->logentry = $logentry;
        $this->code = $code;
    }
}

class Log {

    //Obsolate
    const L_FATAL = L_FATAL, L_ERROR = L_ERROR, L_WARNING = L_WARNING, L_INFO = L_INFO, L_DUMP = L_DUMP, L_DEBUG = L_DEBUG;

    static private $entries = array();

    static public function log($message, $level = L_DEBUG, $code = 0) {
        if (APDL::$LOGLEVEL >= $level) {
            if (APDL_LOG_TRACKTIME === true) {
                $message = "[" . runtime() . "]\t" . $message;
            }
            $entry = (object)array("level" => $level, "message" => $message, "caller" => APDL::$CTRACK);
            if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
                $entry->backtrace = debug_backtrace();
            }
            self::$entries[] = $entry;
            if ($code) {
                APDL::$ERRORS[] = new ERROR($entry, $code);
            }
        }
        if ($level == L_FATAL) {
            $entry = (object)array("level" => L_FATAL, "message" => "---FATAL ERROR, STOPPING EXECUTION---", "caller" => "APDL-Core");
            if (APDL_LOG_FATALS === true) {
                $entry->backtrace = debug_backtrace();
            }
            self::$entries[] = $entry;
            APDL::die_on_fatal();
        }
    }

    static public function PHPFatal() {
        $err = error_get_last();
        if ($err['type'] == E_CORE_ERROR || $err['type'] == E_COMPILE_ERROR || $err['type'] == E_ERROR) {
            self::PHPErr("Fatal", $err['message'], $err['file'], $err['line']);
        }
        return false;
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
        self::log("<span style='color: #F08'>[PHP: $errlev]</span> " . $error . " at " . $file . " Line: " . $line, $level, APDL_E_PHP);
        return TRUE;
    }

    static public function dumplog($target = APDL_OUTPUT_SCREEN, $die = false) {
        if ($target == APDL_OUTPUT_FILE) {
            $output = self::generate_simple();
            if (APDL_SYSMODE == SYSMODE_PRODUCTION) {
                $lsd = "production";
            } else {
                $lsd = "debug";
            }
            file_put_contents(APDL_SYSROOT . "/logs/" . $lsd . "/" . microtime(true) . "-" . get_client_ip() . ".log", $output);
        } else {
            if (class_exists("APDL\\HTML5")) {
                $body = "";
                if ($target == APDL_OUTPUT_SCREEN) {
                    $img = "";
                    if (class_exists("APDL\\HTTP")) {
                        $img = HTTP::webpath(APDL_SYSROOT . "/assets/img/apdl.png");
                    }
                    $body .= "<div id='apdl_log'><img src='" . $img . "' style='display: block;margin:auto'/>";
                }
                $body .= "<table>{CELLS}</table>";
                if ($target == APDL_OUTPUT_SCREEN) {
                    $body .= "</div>";
                }
                $cells = "";
                foreach (self::$entries as $key => $entry) {
                    $contents = $entry->message;
                    if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
                        $contents .= "<span class='backtrace'>" . htmlspecialchars(print_r($entry->backtrace, 1)) . "</span>";
                    }
                    $cells .= "<tr class='level_$entry->level'><td>[$entry->caller]</td><td>" . $contents . "</pre></td></tr>";
                }
                $body = str_replace("{CELLS}", $cells, $body);
                if ($target == APDL_OUTPUT_SCREEN) {
                    $html = new HTML5;
                    $html->css(webpath(APDL_SYSROOT . "/assets/css/log.css"));
                    if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
                        $html->scriptlink(webpath(APDL_SYSROOT . "/assets/js/jquery.js"));
                        $html->scriptlink(webpath(APDL_SYSROOT . "/assets/js/backtrace.js"));
                    }
                    $html->body = $body;
                    echo $html->render($die);
                } elseif ($target == APDL_OUTPUT_SESSION) {
                    $_SESSION["APDL-LD-" . HTTP::get_request_key()] = array("log" => $body, "runtime" => substr((float)(@microtime(true) - APDL_START_MT), 0, 5) . "s");
                }
            } else {
                ob_get_clean();
                die("<pre>" . self::generate_simple() . "</pre>");
            }

        }
    }

    public static function generate_simple() {
        $output = "";
        $secount = count(self::$entries);
        foreach (self::$entries as $key => $entry) {
            $message = $entry->message;
            if ($entry->level == L_FATAL && !empty($entry->backtrace)) {
                $message .= "\r\n" . print_r($entry->backtrace, 1);
            }
            $output .= "[" . $entry->caller . "]\t" . $message;
            if ($key < $secount - 1) {
                $output .= "\r\n\r\n";
            }
        }
        return $output;
    }

    public static function dump_to_session() {
        if (APDL::Sysvar("__session_logging_active", APDL_INTERNALCALL)) {
            self::dumplog(APDL_OUTPUT_SESSION);
        }
    }

}