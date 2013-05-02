<?php
namespace APDL;

class ControllerBase {
    protected $host;

    public function __construct($host) {
        $this->host = $host;
    }

    protected function arg($arg) {
        return $this->host->arg($arg);
    }

    protected function sublink($args) {
        return $this->host->sublink($args);
    }
}

class Controller {
    protected $__file, $__base, $__args = array(), $__valid = false, $__methods, $__logident, $__module = false, $__instance = false;

    public function __construct($routeinfo, $args = array()) {
        if (is_array($routeinfo)) {
            $args = $routeinfo['args'];
            $file = $routeinfo['target'];
        } else {
            $file = $routeinfo;
        }
        $this->__args = $args;
        $this->__base = $file;
        if (substr($file, 0, 4) == "[FS]") {
            $this->__file = substr($file, 4);
            $this->__logident = "FS: " . basename($file);
        } elseif ($file != "" && $file != "?") {
            list($mod, $fn) = explode("/", $file, 2);
            $this->__module = MODULESTORE::load_module($mod);
            $moddir = $this->__module->getdir();
            $this->__file = $moddir . "/controller/" . $fn . ".php";
            $this->__logident = $file;
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

    public function get_module() {
        return $this->__module;
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
    function run($args = false) {
        if ($this->__valid) {
            //Log controller running
            log("---Running controller '" . $this->__base . "'---", L_INFO);
            //Overwrite args if they're specified in call
            if (is_array($args)) {
                $this->__args = $args;
            }
            $caller_region = APDL::$CTRACK;
            set_codetracker($this->__logident);
            $interrupted = false;
            ob_start();
            //Check and run modulewide interrupt fn if exists
            if ($this->__module && is_callable($this->__module->interrupt)) {
                $interrupted = call_user_func($this->__module->interrupt);
            }
            //Run controller if it was not interrupted by the host module
            if (!$interrupted) {
                $_this =& $this;
                include ($this->__file);
                $ob = ob_get_clean();
                if (!empty($this->__args)) {
                    $action = array_shift(@array_values($this->__args));
                }
                if ($this->__instance) {
                    //If we have a class based controller
                    if (!empty($action) && method_exists($this->__instance, $action)) {
                        ob_start();
                        $this->__instance->$action(array_slice($this->__args, 1));
                        $ob .= ob_get_clean();
                    } elseif (method_exists($this->__instance, "index")) {
                        ob_start();
                        $this->__instance->index($this->__args);
                        $ob .= ob_get_clean();
                    }
                } else {
                    //If we have an anonymous controller
                    if (!empty($action) && isset($this->__methods[$action]) && is_callable($this->__methods[$action]) && strpos($action, "_") === false) {
                        ob_start();
                        $this->__methods[$action](array_slice($this->__args, 1));
                        $ob .= ob_get_clean();
                    } elseif (isset($this->__methods["index"]) && is_callable($this->__methods["index"])) {
                        ob_start();
                        $this->__methods["index"]($this->__args);
                        $ob .= ob_get_clean();
                    }
                }
            } else {
                log("Module '" . $this->__module->name . "' has interrupted the starting of the controller!", L_WARNING);
                $ob=ob_get_clean();
            }
            set_codetracker($caller_region);
            //Log controller running
            log("---Controller '" . $this->__base . "' has finished running---", L_INFO);
            return $ob;
        } else {
            log("Trying to run invalid controller '$this->__file'!", L_ERROR, APDL_E_CONTROLLER_NOT_EXISTS);
            return false;
        }
    }
}

log("Controller class loaded", Log::L_INFO);