<?php

namespace APDL;

class ControllerBase extends BASEOBJECT{

    protected $__host;

    public function __construct($host) {
        $this->__host = $host;
        $this->onConstruct();
    }

    protected function arg($arg) {
        return $this->__host->arg($arg);
    }

    protected function sublink($args) {
        return $this->__host->sublink($args);
    }
    
    public function onConstruct(){
        
    }
}

class Controller {

    protected $__file, $__base, $__args = array(), $__valid = false, $__methods, $__logident, $__module = false, $__instance = false, $__is_class_based = false;

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
            $this->__logident = "FS: " . basename(substr($file, 4));
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
                $_this = &$this;
                include ($this->__file);
                $ob = ob_get_clean();
                if (!empty($this->__args)) {
                    $action = array_shift(@array_values($this->__args));
                }
                if ($this->__instance && is_object($this->__instance)) {
                    $this->__is_class_based = true;
                } elseif (!isset($this->__methods["index"]) && (!isset($action) || !isset($this->__methods[$action]))) {
                    $cf = fopen($this->__file, "r");
                    $parse = fread($cf, CONTROLLER_CLASS_SEEK_LIMIT);
                    $tokens = token_get_all($parse);
                    $catch = false;
                    foreach ($tokens as $index => $token) {
                        if ($catch && $token[0] == T_STRING && in_array("APDL\\ControllerBase", class_parents($token[1]))) {
                            $classname = $token[1];
                            $this->__instance = new $classname($this);
                            $this->__is_class_based = true;
                            log("Controller class parser: found ControllerBase class " . $classname . " at line " . $token[2]);
                            break;
                        }
                        if ($token[0] == T_CLASS) {
                            $catch = $index;
                        }
                    }
                }

                $call_method = false;

                //If we have a class based controller
                if ($this->__is_class_based) {
                    if (!in_array("APDL\\ControllerBase", class_parents(get_class($this->__instance)))) {
                        log("Controller $this->__base is class based, but doesn't extend ControllerBase!", L_WARNING);
                    }
                    if (!empty($action) && method_exists($this->__instance, $action)) {
                        $call_method = "action";
                    } elseif (method_exists($this->__instance, "index")) {
                        $call_method = "index";
                    }
                } else {
                    //If we have an anonymous controller
                    if (!empty($action) && isset($this->__methods[$action]) && is_callable($this->__methods[$action]) && strpos($action, "_") === false) {
                        $call_method = "action";
                    } elseif (isset($this->__methods["index"]) && is_callable($this->__methods["index"])) {
                        $call_method = "index";
                    }
                }

                if ($call_method == "action") {
                    $ob.=$this->call_method($action, array_slice($this->__args, 1));
                } elseif ($call_method == "index") {
                    $ob.=$this->call_method("index", $this->__args);
                }
            } else {
                log("Module '" . $this->__module->name . "' has interrupted the starting of the controller!", L_WARNING);
                $ob = ob_get_clean();
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

    protected function call_method($method, $args) {
        ob_start();
        $arraymode = false;
        if ($this->__is_class_based) {
            $reflector = new \ReflectionMethod($this->__instance, $method);
        } else {
            $reflector = new \ReflectionFunction($this->__methods[$method]);
        }
        $params = $reflector->getParameters();
        if (isset($params[0])) {
            if ($params[0]->getName() == "args" && count($params) == 1) {
                $arraymode = true;
            }
        }

        if ($arraymode) {
            $call = function($method) use ($args) {
                call_user_func($method, $args);
            };
        } else {
            $avalues = array();
            $remparams = array();
            foreach ($params as $param) {
                $name = $param->getName();
                if (isset($args[$name])) {
                    $avalues[$name] = $args[$name];
                    unset($args[$name]);
                } else {
                    $remparams[] = $name;
                }
            }
            $args = array_values($args);

            foreach ($remparams as $key => $paramname) {
                if (isset($args[$key])) {
                    $avalues[$paramname] = $args[$key];
                }
            }

            $values = array();
            foreach ($params as $param) {
                if (!array_key_exists($param->getName(), $avalues)) {
                    if ($param->isDefaultValueAvailable()) {
                        $avalues[$param->getName()] = $param->getDefaultValue();
                    } else {
                        apdl_log("Method $method of controller $this->__base requires parameter {$param->getName()} to be given!", L_FATAL, APDL_E_MISSING_CONTROLLER_PARAM);
                    }
                }
                $values[$param->getPosition()] = $avalues[$param->getName()];
            }

            $call = function($method) use ($values) {
                call_user_func_array($method, $values);
            };
        }
        if ($this->__is_class_based) {
            $call(array($this->__instance, $method));
        } else {
            $call($this->__methods[$method]);
        }
        return ob_get_clean();
    }

}

log("Controller class loaded", Log::L_INFO);
