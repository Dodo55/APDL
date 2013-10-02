<?php

namespace APDL;

HTTP::__ping();

abstract class OUTPUT {

    protected $skeleton, $elements;

    const eseparator = "\n";

    public function &__get($var) {
        if (isset($this->elements[$var])) {
            return $this->elements[$var];
        } else {
            log("No element $var in output generator " . get_class($this) . "!", L_WARNING);
        }
    }

    public function __set($var, $val) {
        if (isset($this->elements[$var])) {
            $this->elements[$var] = $val;
        } else {
            log("No element $var in output generator " . get_class($this) . "!", L_WARNING);
        }
    }

    public function render($mode = OUTPUT_RETURN) {
        $out = $this->skeleton;
        foreach ($this->elements as $key => $contents) {
            if (is_array($contents) && !is_string($contents)) {
                $out = str_ireplace("{#$key}", implode(static::eseparator, $contents), $out);
            } elseif (is_string($contents)) {
                $out = str_ireplace("{#$key}", $contents, $out);
            } else {
                $out = str_ireplace("{#$key}", "", $out);
            }
        }
        //Delete remaining empty elements
        $out = preg_replace("%{#.*?}%", "", $out);

        if ($mode == OUTPUT_DIE_CLEAN) {
            if (ob_get_level() > 0) {
                ob_clean();
            }
        }
        if ($mode >= OUTPUT_DIE) {
            die($out);
        }
        return $out;
    }

}

class HTML5 extends OUTPUT {

    protected $skeleton = <<<EOT
<!DOCTYPE html>
<html>
    <head>
        {#head}
        <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    </head>
    <body>
        {#body}
    </body>
</html>
EOT;

    public function __construct() {
        $this->elements['head'] = array();
        $this->elements['body'] = array();
        $this->charset("utf-8");
    }

    public function scriptlink($src) {
        $this->head[] = "<script src=\"$src\"></script>";
    }

    public function script($body) {
        $this->head[] = "<script>$body</script>";
    }

    public function css($href, $media = "all") {
        $this->head[] = "<link rel=\"stylesheet\" type=\"text/css\" media=\"$media\" href=\"$href\"/>";
    }

    public function style($style) {
        $this->head[] = "<style>$style</style>";
    }

    public function meta($name, $content) {
        $this->head[] = "<meta name=\"$name\" content=\"$content\"/>";
    }

    public function charset($charset) {
        $this->head[] = "<meta charset=\"$charset\" />";
    }

    public function title($title) {
        $this->head[] = "<title>$title</title>";
    }

    public function attach_debugger() {
        APDL::Setvar("__session_logging_active", true, APDL_INTERNALCALL);
        $this->meta("apdl_sr", HTTP::webpath(APDL_SYSROOT));
        $this->meta("apdl_rk", HTTP::get_request_key());
        $this->meta("apdl_sid", \session_id());
        $this->scriptlink(webpath(APDL_SYSROOT . "/assets/js/debug.js"));
        $this->css(webpath(APDL_SYSROOT . "/assets/css/debugger.css"));
        $this->body[] = str_replace("__SYSROOT__", webpath(APDL_SYSROOT), file_get_contents(APDL_SYSROOT . "/assets/html/debugger.html"));
        if (APDL_SYSMODE >= SYSMODE_DEBUG_BACKTRACE) {
            $this->scriptlink(webpath(APDL_SYSROOT . "/assets/js/backtrace.js"));
        }
    }

    public static function inject_debugger($html) {
        //Check if valid HTML and get head + body
        if (preg_match("#<html>.*?<head.*?>(.+?)</head>.*?<body.*?>(.+?)</body>.*?</html>#ims", $html, $matches)) {
            $head = $matches[1];
            $body = $matches[2];
            $debugger = new static;
            $debugger->body = array($body);
            $debugger->head[] = $head;
            $debugger->attach_debugger();
            return $debugger->render();
        }
        log("Inject debugger: invalid HTML input given", L_WARNING);
        return false;
    }

}

class JSON {

    public static function Flush($data) {
        $json = json_encode($data);
        $warn = false;
        while (ob_get_level()) {
            if (ob_get_length()) {
                log("Existing output data destroyed on flushing JSON output!
                    For the best performance, you should avoid any other output in JSON responses.", L_WARNING);
                $warn = true;
            }
            ob_end_clean();
        }
        if ($warn && APDL_SYSMODE >= SYSMODE_DEBUG) {
            Log::dumplog(APDL_OUTPUT_FILE);
        }
        die($json);
    }

}

log("Output generator & HTML5 output class loaded", Log::L_INFO);