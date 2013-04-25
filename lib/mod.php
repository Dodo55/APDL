<?php
namespace APDL;

class MODULESTORE {
    //Init default
    protected static $modules, $moddirs = array(), $pending = array();

    public static function add_dir($dir) {
        self::$moddirs[] = $dir;
    }

    public static function load_module($mod, $minbver = 0, $args = array()) {
        if (empty(self::$modules[$mod])) {
            log("Trying to load module '$mod'...");
            $notfound = true;
            foreach (self::$moddirs as $dir) {
                $file = $dir . "/" . $mod . "/_mod.php";
                if (file_exists($file)) {
                    $notfound = false;
                    break;
                }
            }
            if ($notfound) {
                log("Can't find module: $mod!", L_FATAL, APDL_E_MODULE_NOT_FOUND);
            }
            self::$modules[$mod] = new MODULE($mod, $file, $minbver, $args);
            self::$modules[$mod]->initialize();
        }
        return self::$modules[$mod];
    }

}

MODULESTORE::add_dir(APDL_DEFAULT_MODDIR);

class MODULE {
    protected $dir, $name, $dname, $version, $binary_version, $init, $state, $loadargs;
    public $interrupt;

    public function getdir() {
        return $this->dir;
    }

    public function __construct($dname, $file, $minbver = 0, $args = array()) {
        $this->dname = $dname;
        $this->dir = dirname($file);
        $this->loadargs = array($dname, $file, $minbver, $args);
    }

    public function initialize() {
        $dname = $this->loadargs[0];
        $file = $this->loadargs[1];
        $minbver = $this->loadargs[2];
        $args = $this->loadargs[3];
        require($file);
        if (empty($this->binary_version)) {
            $this->binary_version = get_binary_version($this->version);
        }
        if (empty($this->version)) {
            $this->version = get_string_version($this->binary_version);
        }
        if (!empty($this->init)) {
            if (is_callable($this->init)) {
                if ($minbver <= $this->binary_version) {
                    $pct = APDL::$CTRACK;
                    set_codetracker($dname);
                    call_user_func($this->init, $args);
                    set_codetracker($pct);
                    log("Loaded module: '$this->name' ($this->dname)", L_INFO);
                } else {
                    log("Module '" . $this->name . "' (" . $this->dname . ") version $minbver is required!", L_ERROR, APDL_E_MODULE_OUTDATED);
                }
            } else {
                log("Module '" . $this->name . "' (" . $this->dname . ") has an invalid initiator function (not callable)!", L_ERROR, APDL_E_TYPEERROR);
            }
        } else {
            log("Loaded container module: '$this->name' ($this->dname)", L_INFO);
        }
    }
}