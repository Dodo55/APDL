function apdl_backtrace_reader() {
    $(function () {
        $('#apdl_log').on("click", "td:has(.backtrace)", function () {
            var w = 640;
            var h = 480;
            var x = screen.width / 2 - w / 2;
            var y = screen.height / 2 - h / 2;
            var btwindow = window.open("", "APDL Debug Backtrace", "height=" + h + ",width=" + w + ",\n\
        location=no,menubar=no,status=no,toolbar=no,top=" + y + ",left=" + x);
            btwindow.document.write("<pre>" + $(this).find('.backtrace').html() + "</pre>");
        });
        $('#apdl_log').on({mouseenter:function () {
            $(this).css({ cursor:"hand", cursor:"pointer" });
            $(this).css({background:"#036"});
        },
            mouseleave:function () {
                $(this).css({ cursor:"normal" });
                $(this).css({background:"#001020"});
            }}, "td:has(.backtrace)");
    });
}

if (typeof(apdl_with_jq)!=='undefined'){
    apdl_with_jq(apdl_backtrace_reader);
} else{
    apdl_backtrace_reader();
}