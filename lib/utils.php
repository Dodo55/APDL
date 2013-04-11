<?php
namespace APDL;
function get_binary_version($strversion) {
    preg_match("#\\d([0-9]|\.|-)+\\d#", $strversion, $vn);
    if (empty($vn[0])) {
        log("Invalid version string given to binary version parser!", L_WARNING);
        return false;
    } else {
        $values = preg_split("#(\.|-)#", $vn[0]);
        $bv = 0;
        for ($i = 3, $i2 = 0; $i >= 0; $i--, $i2++) {
            if (isset($values[$i])) {
                $bv += $values[$i] * pow(100, $i2);
            }
        }
        return $bv;
    }
}

function get_string_version($bver) {
    $sv = "";
    for ($i = 3; $i >= 0; $i--) {
        $vtag = (int)($bver / pow(100, $i));
        $sv .= $vtag;
        $bver = $bver % pow(100, $i);
        if ($i) {
            $sv .= ".";
        }
    }
    return $sv;
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function clink($controller, $args = array()) {
    return internal_url(ROUTING::get_route($controller, $args));
}

function dump($obj) {
    Log::log("<pre>" . print_r($obj, 1) . "</pre>", L_DUMP);
}

function safestr($str) {
    setlocale(LC_ALL, "en_US.utf8");
    $badchars = array(' ', '\n', '\\', "-", "?", "!", "/", "\"", "'","=","+","*","%","@","§","~","$","#","{","}","[","]",";",".",",","<",">");
    return strtolower(str_replace($badchars, "_", iconv('UTF-8', 'ASCII//TRANSLIT', $str)));
}

log("Utility functions loaded", L_INFO);