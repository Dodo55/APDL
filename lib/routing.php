<?php
namespace APDL;

HTTP::__ping();

class ROUTING {
    //TODO: Better implementation with less loops
    protected static $routes = array();

    public static function add_route($selector, $target) {
        if (array_key_exists($selector, self::$routes)) {
            log("Overwriting existing URL route '$selector'='" . self::$routes[$selector] . "' with '$target'!", L_WARNING);
        }
        if ($selector == "$1") {
            log("Catch-all routing in effect to $target!", L_WARNING);
        }
        self::$routes[$selector] = $target;
        log("URL route '$selector' set to '$target'");
    }

    public static function arr_to_getparams($array) {
        return http_build_str($array);
    }

    public static function getparams_to_arr($params) {
        $array = array();
        parse_str($params, $array);
        return $array;
    }

    public static function route_exists($selector) {
        return array_key_exists($selector, self::$routes);
    }

    public static function get_route($target, $args = array()) {
        $caller_region = APDL::$CTRACK;
        set_codetracker("Routing");
        $err = "";
        $te = explode("?", $target, 2);
        if (isset($te[1])) {
            $args = array_merge($args, self::getparams_to_arr($te[1]));
        }
        $target = $te[0];
        $te = explode("/", $target);
        foreach (self::$routes as $selector => $route) {
            $thisargs = $args;
            $re = explode("/", $route);
            $se = explode("/", $selector);
            $ok = true;
            foreach ($re as $index => $element) {
                if (strpos($element, "?") !== false) {
                    $ete = explode("?", $element, 2);
                    $params = self::getparams_to_arr($ete[1]);
                    $badparam = false;
                    foreach ($params as $pvar => $pval) {
                        if (preg_match("#(\\$[0-9])#", $pval, $abrm)) {
                            $selector = str_replace($abrm[1], $args[$pvar], $selector);
                            unset($thisargs[$pvar]);
                            continue;
                        }
                        if (!isset($args[$pvar]) || $args[$pvar] != $pval) {
                            $badparam = true;
                            //TODO: Causes false warnings, need to rewrite the whole control structure of the loop to make it reliable...
                            //$err = "Route '$selector' could match, but it sets argument '$pvar' to '$pval' which conflicts with the current arguments";
                            break;
                        } else {
                            unset($thisargs[$pvar]);
                        }
                    }
                    if ($badparam) {
                        $ok = false;
                        break;
                    }
                    $element = $ete[0];
                }
                if (isset($te[$index]) && $element == $te[$index]) {
                    continue;
                }
                if (preg_match("#(\\$[0-9])#", $element, $rxm)) {
                    $mask = str_replace($rxm[1], "", $element);
                    $dval = str_replace($mask, "", $te[$index]);
                    $selector = str_replace($rxm[1], $dval, $selector);
                    continue;
                }
                $ok = false;
                break;
            }
            if ($ok) {
                foreach ($se as $stag) {
                    if (preg_match("#\[(.+?)\]#", $stag, $pm)) {
                        if (isset($thisargs[$pm[1]])) {
                            $selector = str_replace($pm[0], $thisargs[$pm[1]], $selector);
                            unset($thisargs[$pm[1]]);
                        } else {
                            $selector = str_replace("/" . $pm[0], "", $selector);
                        }
                        continue;
                    }
                    if (preg_match("#{(.+?)}#", $stag, $pm)) {
                        if (isset($thisargs[$pm[1]])) {
                            $selector = str_replace($pm[0], $thisargs[$pm[1]], $selector);
                            unset($thisargs[$pm[1]]);
                            continue;
                        }
                        $err = "Route '$selector' could match, but it requires argument '$pm[1]' which is not given";
                        $ok = false;
                        break;
                    }
                }
            }
            if ($ok) {
                if (!empty($thisargs)) {
                    $selector .= "/" . implode("/", $thisargs);
                }
                set_codetracker($caller_region);
                return $selector;
            }
        }
        log("No matching route found for controller path '$target', returning default m/c route format! $err", L_WARNING);
        set_codetracker($caller_region);
        return $target . "/" . implode("/", $args);
    }

    public static function resolve_url($url) {
        $caller_region = APDL::$CTRACK;
        set_codetracker("Routing");
        $tags = explode("/", $url);
        if ($tags[0] == "") {
            log("Routing: webroot detected, resolving as route 'index'", L_INFO);
            set_codetracker($caller_region);
            return self::resolve_url("index");
        }
        $match = false;
        $target = "?";
        log("Resolving URL route '$url'...", L_INFO);
        foreach (self::$routes as $selector => $route) {
            if (strpos($route, "/") === FALSE && $tags[0] == $selector) {
                set_codetracker($caller_region);
                return self::resolve_url(str_replace($selector, $route, $url));
            }
            $s_exp = explode("/", $selector);
            $args = array();
            foreach ($s_exp as $index => $tag) {
                if (isset($tags[$index]) && $tag == $tags[$index]) {
                    $match = true;
                } elseif (!empty($tags[$index]) && strpos($tag, "$") !== FALSE) {
                    $tagvar = substr($tag, 1);
                    $match = true;
                    $route = str_replace("$" . $tagvar, $tags[$index], $route);
                } elseif (strpos($tag, "[") !== FALSE) {
                    $match = true;
                    if (!empty($tags[$index])) {
                        $an = trim($tag, "[]");
                        $args[$an] = $tags[$index];
                    }
                } elseif (!empty($tags[$index]) && strpos($tag, "{") !== FALSE) {
                    $match = true;
                    $an = trim($tag, "{}");
                    $args[$an] = $tags[$index];

                } else {
                    $plusargs = array_slice($tags, $index);
                    $match = false;
                    break;
                }
                $plusargs = array_slice($tags, $index + 1);
            }
            if ($match) {
                log("URL '$url' matches route '$selector' and resolves to: '$route'", L_INFO);
                $te = explode("?", $route, 2);
                if (isset($te[1])) {
                    $fixargs = self::getparams_to_arr($te[1]);
                    foreach ($fixargs as $var => $val) {
                        $args[$var] = $val;
                    }
                }
                $args = array_merge($args, $plusargs);
                $target = $te[0];
                break;
            } else {
                $target = "?";
            }
        }

        if ($target == "?") {
            if (isset($tags[1]) && $tags[1] != "") {
                $target = $tags[0] . "/" . $tags[1];
                $args = array_slice($tags, 2);
                log("Using default URL mapping, as the URL doesn't match any route selectors", L_INFO);
            } else {
                log("The URL is invalid (404)", L_INFO);
                $args = array();
            }
        }
        set_codetracker($caller_region);
        return array("target" => $target, "args" => $args);
    }

    public static function internal_url($path) {
        $url = APDL_HTTP_WEBROOT . "/";
        if (!sysvar("url_rewrite")) {
            $url .= basename(APDL_INDEX_FILE) . "?" . sysvar("url_route_key") . "=";
        }
        $url .= $path;
        return $url;
    }
}

log("Routing loaded", Log::L_INFO);