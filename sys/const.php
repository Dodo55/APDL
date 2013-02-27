<?php
define("APDL_SERVER_HOST", $_SERVER['HTTP_HOST']);
//TODO: SERVER IP
define("APDL_HTTP_REQUEST", $_SERVER['REQUEST_URI']);
define("APDL_HTTP_DOCROOT", $_SERVER['DOCUMENT_ROOT']);
define("APDL_INDEX_FILE", $_SERVER['SCRIPT_FILENAME']);
define("APDL_OUTPUT_SCREEN", 0);
define("APDL_OUTPUT_FILE", 1);
define("L_FATAL", 1);
define("L_ERROR", 2);
define("L_WARNING", 3);
define("L_INFO", 4);
define("L_DUMP", 5);
define("L_DEBUG", 6);