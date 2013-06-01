<?php
\APDL\log("----------APDL INTERNAL TESTS----------",L_DUMP);
\APDL\log("Testing system core...",L_INFO);

assert("true"); //Test php assert()

assert('defined("APDL_MUST_RUN")'); //Test APDL init.php reinclude protection
assert('defined("APDL_INITIALIZED")'); //Test APDL init.php load state const
assert('defined("APDL_MUST_RUN") && !APDL_MUST_RUN'); //Test APDL include protector

\APDL\Setvar("apdl_test_testvar","testvalue");
assert('\\APDL\\Sysvar("apdl_test_testvar")=="testvalue"'); //Test basic sysvar functionality

\APDL\APDL::Setvar("__test","goodvalue",APDL_INTERNALCALL);
\APDL\log("(Warning is normal below:)",L_INFO);
\APDL\Setvar("__test","badvalue");

assert('\\APDL\\Sysvar("__test")=="goodvalue"'); //Test sysvar protection

$ctbackup=\APDL\APDL::$CTRACK;
\APDL\set_codetracker("test...");
assert('\\APDL\\APDL::$CTRACK=="test..."'); //Test codetracker
\APDL\set_codetracker($ctbackup);

apdl_test_check_success();