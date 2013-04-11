<?php
namespace APDL;

class Controller {
    protected $__file, $__base, $__args = array(), $__valid = false, $__methods, $logident;

    public function __construct($file, $args = array()) {
        $this->__args = $args;
        $this->__base = $file;
        if (substr($file, 0, 4) == "[FS]") {
            $this->__file = substr($file, 4);
            $this->logident = "FS: " . basename($file);
        } elseif ($file != "" && $file != "?") {
            list($mod, $fn) = explode("/", $file, 2);
            $moddir = MODULESTORE::load_module($mod);
            $this->__file = $moddir . "/controller/" . $fn . ".php";
            $this->logident = $file;
        } else {
            log("No controller path given!", L_ERROR);
        }
        if (file_exists($this->__file)) {
            $this->__valid = true;
        } else {
            $this->__valid = false;
            log("Controller '$this->__file' doesn't exist!", L_ERROR, APDL_E_CONTROLLER_NOT_EXISTS);
        }
    }

    public function base() {
        return $this->__base;
    }

    public function sublink($args) {
        return clink($this->__base, $args);
    }

    public function arg($arg) {
        if (!empty($this->__args[$arg])) {
            return $this->__args[$arg];
        } else {
            log("Controller argument $arg is empty", L_WARNING);
            return false;
        }
    }

    public function is_set($arg) {
        return (!empty($this->__args[$arg]));
    }

    public function __set($method, $fn) {
        $this->__methods[$method] = $fn;
    }

    public function __get($method) {
        return $this->__methods[$method];
    }

    public
    function run() {
        if ($this->__valid) {
            $caller_region = APDL::$CTRACK;
            set_codetracker($this->logident);
            ob_start();
            $_this =& $this;
            include ($this->__file);
            $ob = ob_get_clean();
            if (!empty($this->__args)) {
                $action = array_shift(@array_values($this->__args));
            }
            if (!empty($action) && isset($this->__methods[$action]) && is_callable($this->__methods[$action]) && strpos($action, "_") === false) {
                ob_start();
                $this->__methods[$action](array_slice($this->__args, 1));
                $ob .= ob_get_clean();
            } elseif (isset($this->__methods["index"]) && is_callable($this->__methods["index"])) {
                ob_start();
                $this->__methods["index"]($this->__args);
                $ob .= ob_get_clean();
            }
            set_codetracker($caller_region);
            return $ob;
        } else {
            return false;
        }
    }
}

log("Controller class loaded", Log::L_INFO);