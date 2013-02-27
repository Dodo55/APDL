<?php
namespace APDL;

class APDL_HTTP {
    public static function find_webroot() {
        $initdir = dirname(APDL_INDEX_FILE);
        return self::webpath($initdir);
    }

    public static function webpath($localpath, $protocol = "http") {
        return $protocol . "://" . APDL_SERVER_HOST . str_replace(APDL_HTTP_DOCROOT, "", $localpath);
    }
}

function webpath($localpath) {
    return APDL_HTTP::webpath($localpath);
}