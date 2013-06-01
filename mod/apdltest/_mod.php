<?php
$this->name = "ADPL Internal Tests";

//Internal test cases up to date for APDL version:
$this->version = "0.0.4-0";

assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_CALLBACK, "apdl_test_assert_callback");
assert_options(ASSERT_WARNING, false);

apdl_test_reset_error_counter();

function apdl_test_assert_callback($file, $line, $exp, $desc = "") {
    \APDL\setvar("apdl_test_error", 1);
    apdl_log("[TEST] ASSERT \"$exp\" failed in '$file' at line $line", L_ERROR);
}

function apdl_test_reset_error_counter() {
    \APDL\setvar("apdl_test_error", 0);
}

function apdl_test_check_success() {
    if (!\APDL\sysvar("apdl_test_error")) {
        \APDL\log("OK", L_DUMP);
    }
    apdl_test_reset_error_counter();
}