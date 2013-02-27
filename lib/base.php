<?php
namespace APDL;
Class BASEOBJECT {

    protected static $__events = array();
    protected $__oevents = array();

    /**
     * Bind a function to an event
     * @param string $event The name of the event
     * @param function $fn The function to execute
     */
    public function __bind($event, $fn) {
        if (is_callable($fn)) {
            $fnInfo = new \ReflectionFunction($fn);
            $fnHash = $fnInfo->getFileName() . "." . $fnInfo->getStartLine() . "-" . $fnInfo->getEndline();
            if (isset($this)) {
                if (!isset($this->__oevents[$event][$fnHash])) {
                    $this->__oevents[$event][$fnHash] = $fn;
                    LOG::log("New function bound to event \"$event\" on a(n) " . get_class($this) . " object");
                } else {
                    LOG::log("Event \"$event\" on a(n) " . get_class($this) . " object already has the same function bound!", APDL_Log::L_WARNING);
                }
            } else {
                if (!isset(self::$__events[$event][$fnHash])) {
                    self::$__events[$event][$fnHash] = $fn;
                    LOG::log("New function bound to static event \"$event\" on " . __CLASS__);
                } else {
                    LOG::log("Static event \"$event\" on " . __CLASS__ . " already has the same function bound!", Log::L_WARNING);
                }
            }
        } else {
            LOG::log("Trying to bind non callable function to event \"$event\"!", Log::L_WARNING);
        }
    }

    /**
     * Fire an event
     * @param string $event The event to fire
     */
    public function __fire($event) {
        if (is_array(self::$__events[$event])) {
            foreach (self::$__events[$event] as $fn) {
                if (isset($this)) {
                    LOG::log("Event \"$event\" of class: " . __CLASS__ . " fired by object: " . spl_object_hash($this));
                    call_user_func_array($fn, array_merge(array($this), array_slice(func_get_args(), 1)));
                } else {
                    LOG::log("Event \"$event\" of class: " . __CLASS__ . " fired statically");
                    call_user_func_array($fn, array_merge(array(__CLASS__), array_slice(func_get_args(), 1)));
                }
            }
        }
        if (isset($this)) {
            if (is_array($this->__oevents[$event])) {
                foreach ($this->__oevents[$event] as $fn) {
                    LOG::log("Event \"$event\" of object " . spl_object_hash($this) . " fired");
                    call_user_func_array($fn, array_merge(array($this), array_slice(func_get_args(), 1)));
                }
            }
        }
    }

}

class DUMMY extends BASEOBJECT {
    protected $err, $elev;

    public function __construct($err = "", $elev = L_WARNING) {
        $this->err = $err;
        $this->elev = $elev;
    }

    public function __call($fn, $args) {
        log("Method $fn called on dummy object");
        if ($this->err) {
            log($this->err, $this->elev);
        }
        return FALSE;
    }

    public static function __callstatic($fn, $args) {
        log("Static method $fn called on dummy class");
        return FALSE;
    }
}

?>