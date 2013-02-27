<?php
namespace APDL;
/**
 * APDL DATA CONTAINERS
 */
class DTO {
    protected $__dcdtodata = array();

    function __construct($rdata = "") {
        if (is_array($rdata)) {
            if (!empty($rdata)) {
                $this->__dcdtodata = $rdata;
                Log::log("DTO Init from array", Log::L_DEBUG);
            }
        } else {
            if ($rdata != "") {
                $sdata = base64_decode($rdata);
                $data = @unserialize($sdata);
                if ($data !== false || $sdata === 'b:0;') {
                    $this->__dcdtodata = $data;
                    Log::log("DTO Init from packed", Log::L_DEBUG);
                } else {
                    $jdata = json_decode($rdata);
                    if ($jdata != NULL) {
                        $this->__dcdtodata = (array) $jdata;
                        LOG::log("DTO Init from JSON", LOG::L_DEBUG);
                    } else {
                        Log::log("Bad DTO Initialization", Log::L_WARNING);
                        Log::log("Bad DTO Init DEBUG: RDATA=" . $rdata . " SDATA=" . $sdata, Log::L_DEBUG);
                    }
                }
            }
        }
    }

    function &__GET($var) {
        if ($var == '__dcdtodata') {
            return $this->__dcdtodata;
        } else {
            if (isset($this->__dcdtodata[$var])) {
                return $this->__dcdtodata[$var];
            } else {
                Log::log("Requesting unset embedded variable $var inside DTO", Log::L_WARNING);
                $ret = FALSE; //Suppress notice :)
                return $ret;
            }
        }
    }

    function __SET($var, $val) {
        $this->__dcdtodata[$var] = $val;
    }

    /**
     * Pack the object data
     * @return string The base64 encoded serialization of container array
     */
    function __pack() {
        return base64_encode(serialize($this->__dcdtodata));
    }

    /**
     * Pack the object data in JSON
     * @return string The JSON encoded serialization of container array
     */
    function __json() {
        return json_encode($this->__dcdtodata);
    }

}

?>
