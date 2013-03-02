$(function () {
    $('.backtrace').parent().click(function () {
        var w = 640;
        var h = 480;
        var x = screen.width / 2 - w / 2;
        var y = screen.height / 2 - h / 2;
        var btwindow = window.open("", "APDL Debug Backtrace", "height=" + h + ",width=" + w + ",\n\
        location=no,menubar=no,status=no,toolbar=no,top=" + y + ",left=" + x);
        btwindow.document.write("<pre>" + $(this).find('.backtrace').html() + "</pre>");
    });
    $('.backtrace').parent().hover(function () {
        $(this).css({ cursor:"hand", cursor:"pointer" });
        $(this).css({background:"#036"});
    }, function () {
        $(this).css({ cursor:"normal" });
        $(this).css({background:"#001020"});
    });
});