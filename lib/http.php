<?php
namespace APDL;

class HTTP extends BASEOBJECT{
    public static function find_webroot() {
        $initdir = dirname(APDL_INDEX_FILE);
        return static::webpath($initdir);
    }

    public static function webpath($localpath, $protocol = "http") {
        $localpath=static::__fire("rewritepaths",$localpath);
        return $protocol . "://" . APDL_SERVER_HOST . str_replace(APDL_HTTP_DOCROOT, "", $localpath);
    }
}

function webpath($localpath) {
    return HTTP::webpath($localpath);
}