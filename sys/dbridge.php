<?php
/*
 * APDL Debugger Bridge
 */

//Read log from session
$k = $_GET['rk'];
$sid = $_GET['sid'];

if (!session_id()){
    session_id($sid);
    session_start();
}
echo json_encode($_SESSION["APDL-LD-" . $k]);
foreach ($_SESSION as $k => $val) {
    if (substr($k, 0, 8) == "APDL-LD-") {
        unset($_SESSION[$k]);
    }
}