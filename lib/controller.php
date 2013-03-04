<?php
namespace APDL;

class Controller {
    protected $__file, $__args = array(), $__valid = false, $__methods;

    public function __construct($file, $args = array()) {
        $this->__args = $args;
        if (substr($file, 0, 4) == "[FS]") {
            $this->__file = substr($file, 4);
        } else {
            $fne = explode("/", $file, 2);
            $this->__file = APDL_SYSROOT . "/mod/" . $fne[0] . "/controller/" . $fne[1] . ".php";
        }
        if (file_exists($this->__file)) {
            $this->__valid = true;
        } else {
            $this->__valid = false;
            log("Controller '$this->__file' doesn't exist!", L_ERROR);
        }
    }

    protected function arg($arg) {
        if (!empty($this->__args[$arg])) {
            return $this->__args[$arg];
        } else {
            log("Controller argument $arg is empty", L_WARNING);
            return false;
        }
    }

    protected function is_set($arg) {
        return (!empty($this->__args[$arg]));
    }

    protected function get_base() {

    }

    public function __set($method, $fn) {
        $this->__methods[$method] = $fn;
    }

    public function run() {
        if ($this->__valid) {
            ob_start();
            include ($this->__file);
            $ob = ob_get_clean();
            if (!empty($this->__args)) {
                $action = array_shift(@array_values($this->__args));
            }
            if (!empty($action) && isset($this->__methods[$action]) && is_callable($this->__methods[$action])) {
                ob_start();
                $this->__methods[$action](array_slice($this->__args, 1));
                $ob .= ob_get_clean();
            } elseif (isset($this->__methods["index"]) && is_callable($this->__methods["index"])) {
                ob_start();
                $this->__methods["index"](array_slice($this->__args, 1));
                $ob .= ob_get_clean();
            }
            return $ob;
        } else {
            return false;
        }
    }
}

function clink($controller, $args = array()) {
    if (sysvar("url_rewrite")) {
        return APDL_HTTP_WEBROOT . "/" . ROUTING::get_route($controller, $args);
    } else {
        return webpath(APDL_INDEX_FILE) . "?" . sysvar("url_route_key") . "=".ROUTING::get_route($controller, $args);
    }
}