<?php
namespace APDL;
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
        $out=preg_replace("%{#.*?}%","",$out);

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
        <meta charset="{#charset}" />
        <title>{#title}</title>
        <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        {#head}
    </head>
    <body>
        {#body}
    </body>
</html>
EOT;

    public function __construct() {
        $this->elements['charset'] = 'utf-8';
        $this->elements['head'] = array();
        $this->elements['body'] = array();
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
        $this->charset = $charset;
    }
}