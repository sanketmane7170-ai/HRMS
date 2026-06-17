/**
 * Notification Script to fetch new and mark read
 */
(function ($) {
    "use strict";
    var baseUrl = $('meta[name="base-url"]').attr("content");
    function fetchNotification() {
        $.ajax({
            url: baseUrl + "/notifications",
            success: function (response) {
                if (response.success) {
                    $("#notification-count").show();
                    $("#notification-count").text(response.count);
                    $(".notification-message").html(response.html);
                    if (response.count == 0) {
                        $("#notification-count").hide();
                    }
                }
            },
        });
    }

    $("body").on("click", ".clear-noti", function () {
        $.ajax({
            url: baseUrl + "/notifications-mark-read",
            success: function (response) {
                if (response.success) {
                    if (response.count != 0) {
                        $("#notification-count").hide();
                        $(".notification-message").html("");
                    }
                }
            },
        });
    });
    setInterval(fetchNotification, 10000);
})(jQuery);
