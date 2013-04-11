function apdl_with_jq(callback) {
    if (typeof jQuery !== 'undefined') {
        return callback();
    } else {
        var script = document.getElementsByTagName('script')[0],
            newjs = document.createElement('script');
        newjs.onreadystatechange = function () {
            if (newjs.readyState === 'loaded' || newjs.readyState === 'complete') {
                newjs.onreadystatechange = null;
                return callback();
            }
        };
        newjs.onload = function () {
            return callback();
        };
        newjs.src = "http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js";
        script.parentNode.insertBefore(newjs, script);
    }
}

apdl_with_jq(function () {
    $(function () {
        var baseurl = $('meta[name=apdl_sr]').attr("content");
        var requestkey = $('meta[name=apdl_rk]').attr("content");
        var sid = $('meta[name=apdl_sid]').attr("content");
        $("#apdl_debugger_log_wrapper").hide();
        $.get(baseurl + "/sys/dbridge.php?rk=" + requestkey + "&sid=" + sid, function (data) {
            var ddata = JSON.parse(data);
            $("#apdl_log").html(ddata.log);
            $("#apdl_debugger_runtime").html(ddata.runtime);
        });
        $("#apdl_debugger_toggle").click(function () {
            $("#apdl_debugger_log_wrapper").slideToggle();
        });
    });
});