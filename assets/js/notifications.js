/**
 * File quản lý thông báo (notifications.js)
 */

(function ($) {
    "use strict";

    // Tạo namespace cho Notifications
    window.Notifications = {
        /**
         * Hiển thị thông báo
         * @param {string} message - Nội dung thông báo
         * @param {string} type - Loại thông báo ('success', 'error')
         * @param {number} duration - Thời gian hiển thị (ms)
         * @param {boolean} isHTML - Có cho phép HTML trong message không
         */
        show: function (
            message,
            type = "success",
            duration = 5000,
            isHTML = true
        ) {
            // Thêm div thông báo nếu chưa tồn tại
            if ($("#cart-notification").length === 0) {
                $("body").prepend(`
                    <div id="cart-notification" class="cart-notification" style="display:none;">
                        <div class="container">
                            <div class="alert">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="fa fa-check-circle"></i> 
                                <span id="notification-message"></span>
                            </div>
                        </div>
                    </div>
                `);
            }

            // Thay đổi lớp CSS dựa trên loại thông báo
            const alertClass =
                type === "error" ? "alert-danger" : "alert-success";
            // Thay đổi icon dựa trên loại thông báo
            const icon =
                type === "error" ? "fa-exclamation-circle" : "fa-check-circle";

            // Cập nhật nội dung và class của alert
            $("#cart-notification .alert")
                .removeClass("alert-success alert-danger")
                .addClass(alertClass);

            // Cập nhật icon và nội dung
            $("#cart-notification .fa")
                .removeClass("fa-check-circle fa-exclamation-circle")
                .addClass(icon);

            if (isHTML) {
                $("#notification-message").html(message);
            } else {
                $("#notification-message").text(message);
            }

            // Hiển thị thông báo
            $("#cart-notification").slideDown();

            // Tự động ẩn sau thời gian định sẵn
            if (duration > 0) {
                setTimeout(function () {
                    $("#cart-notification").slideUp();
                }, duration);
            }
        },

        /**
         * Ẩn thông báo
         */
        hide: function () {
            $("#cart-notification").slideUp();
        },

        /**
         * Khởi tạo sự kiện cho nút đóng thông báo
         */
        init: function () {
            $(document).on("click", ".cart-notification .close", function () {
                Notifications.hide();
            });
        },
    };

    // Khởi tạo module khi document ready
    $(document).ready(function () {
        Notifications.init();
    });
})(jQuery);
