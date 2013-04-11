<?php
namespace APDL;

class MODULESTORE {
    //Init default
    protected static $modules, $moddirs = array();

    public static function add_dir($dir) {
        self::$moddirs[] = $dir;
    }

    public static function load_module($mod, $minbver = 0) {
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
            self::$modules[$mod] = $mod; //Prevent recursive loading if it would happen during initialization
            self::$modules[$mod] = new MODULE($mod, $file, $minbver);
        }
        return self::$modules[$mod]->getdir();
    }
}

MODULESTORE::add_dir(APDL_SYSROOT . "/mod");

class MODULE {
    protected $dir, $name, $dname, $version, $binary_version, $init, $state;

    public function getdir() {
        return $this->dir;
    }

    public function __construct($dname, $file, $minbver = 0) {
        $this->dname = $dname;
        $this->dir = dirname($file);
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
                    call_user_func($this->init);
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