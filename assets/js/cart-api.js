/**
 * File API cart (cart-api.js)
 * Chứa các hàm gọi API liên quan đến giỏ hàng
 */

(function ($) {
    "use strict";

    // Xử lý lỗi khi chưa đăng nhập
    function handleUnauthorized(xhr) {
        if (xhr.status === 401) {
            const response = xhr.responseJSON || {};

            // Hiển thị thông báo trực tiếp trên trang thay vì alert
            if (typeof Notifications !== "undefined") {
                Notifications.show(
                    "You must be logged in to access cart features.",
                    "error",
                    10000
                );

                // Thêm nút đăng nhập vào thông báo
                $("#notification-message").html(
                    'You must be logged in to access cart features. <a href="' +
                        (response.redirect || BASE_URL + "/public/login.php") +
                        '" class="">Login Now</a>'
                );
            }

            return true; // Đã xử lý lỗi
        }
        return false; // Chưa xử lý lỗi
    }

    // Tạo namespace cho CartAPI
    window.CartAPI = {
        // Cập nhật tổng giá trị giỏ hàng
        updateCartSummary: function () {
            return $.ajax({
                url: BASE_URL + "/public/api/cart.php?action=view",
                type: "GET",
                success: function (response) {
                    let total = 0;

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (item) {
                            total += parseFloat(item.subtotal);
                        });
                    }

                    $("#subtotal").text("$" + total.toFixed(2));
                    $("#total").text("$" + total.toFixed(2));
                },
                error: function (xhr) {
                    handleUnauthorized(xhr);
                },
            });
        },

        // Cập nhật số lượng sản phẩm trên icon giỏ hàng
        updateCartCount: function () {
            return $.ajax({
                url: BASE_URL + "/public/api/cart.php?action=view",
                type: "GET",
                success: function (response) {
                    let count = 0;

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (item) {
                            count += parseInt(item.quantity);
                        });
                    }

                    $(".cart-count").text(count);
                },
                error: function (xhr) {
                    handleUnauthorized(xhr);
                },
            });
        },

        // API thêm sản phẩm vào giỏ hàng
        addToCart: function (productId, quantity = 1) {
            return $.ajax({
                url: BASE_URL + "/public/api/cart.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    action: "add",
                    product_id: productId,
                    quantity: quantity,
                }),
                error: function (xhr) {
                    handleUnauthorized(xhr);
                    return $.Deferred().reject(xhr);
                },
            });
        },

        // API cập nhật số lượng sản phẩm
        updateCartItem: function (productId, quantity) {
            return $.ajax({
                url: BASE_URL + "/public/api/cart.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    action: "update",
                    product_id: productId,
                    quantity: quantity,
                }),
                error: function (xhr) {
                    handleUnauthorized(xhr);
                    return $.Deferred().reject(xhr);
                },
            });
        },

        // API xóa sản phẩm khỏi giỏ hàng
        removeFromCart: function (productId) {
            return $.ajax({
                url: BASE_URL + "/public/api/cart.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    action: "remove",
                    product_id: productId,
                }),
                error: function (xhr) {
                    handleUnauthorized(xhr);
                    return $.Deferred().reject(xhr);
                },
            });
        },

        // API lấy dữ liệu giỏ hàng
        getCart: function () {
            return $.ajax({
                url: BASE_URL + "/public/api/cart.php?action=view",
                type: "GET",
                error: function (xhr) {
                    handleUnauthorized(xhr);
                    return $.Deferred().reject(xhr);
                },
            });
        },
    };
})(jQuery);
