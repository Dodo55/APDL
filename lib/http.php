<?php
namespace APDL;

if (!session_id()) {
    session_start();
}

class HTTP extends BASEOBJECT {
    protected static $request_key;

    public static function find_webroot() {
        $initdir = dirname(APDL_INDEX_FILE);
        return static::webpath($initdir);
    }

    public static function webpath($localpath, $protocol = APDL_PROTOCOL) {
        $rp = rewritepaths($localpath);
        if ($rp !== false) {
            $localpath = $rp;
        }
        return $protocol . "://" . APDL_SERVER_HOST . str_replace(APDL_HTTP_DOCROOT, "", $localpath);
    }

    public static function get_request_key() {
        if (empty(self::$request_key)) {
            self::$request_key =
                md5("APDL-RK-" . time() . rand(0, 100)) . md5(APDL_HTTP_REQUEST);
        }
        return self::$request_key;
    }
}

log("HTTP Library loaded", Log::L_INFO);