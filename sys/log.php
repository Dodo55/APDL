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
            self::$entries[] = (object)array("level" => $level, "message" => $message, "caller" => APDL::$CTRACK);
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
                break;
        }
        self::log("[PHP: $errlev] " . $error . " at " . $file . " Line: " . $line, $level);
        return TRUE;
    }

    static public function dumplog($target = APDL_OUTPUT_SCREEN) {

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
            $style = "body{background:black;color:white;font-family:monospace;}
                     #log{width:870px; margin: auto; border:1px solid white; background: #001020;
                        padding:15px;margin-top: 50px;}
                     table{border:0}
                     td:first-child{min-width: 150px;}
                     td{vertical-align: top}
                     .level_1 td{color: red; font-weight: bold;}
                     .level_2 td{color: #F60;}
                     .level_3 td{color: yellow;}
                     .level_4 td{color: white;}
                     .level_5 td{color: #0AF;}
                     .level_6 td{color: lime;}
                        ";
            $body = "<div id='log'><img src='" . webpath(APDL_SYSROOT . "/assets/apdl.png") . "' style='display: block;margin:auto'/>
            <table>{CELLS}</table></div>";
            $cells = "";
            foreach (self::$entries as $key => $entry) {
                $cells .= "<tr class='level_$entry->level'><td>[$entry->caller]</td><td>$entry->message</td></tr>";
            }
            $body = str_replace("{CELLS}", $cells, $body);
            APDL::EchoHTML(array("body" => $body, "style" => $style), TRUE);
        }
    }

}