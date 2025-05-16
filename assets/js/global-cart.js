/**
 * File chức năng giỏ hàng global (global-cart.js)
 * Chức năng giỏ hàng áp dụng cho toàn bộ website
 */

(function ($) {
    "use strict";

    // Khởi tạo các chức năng giỏ hàng toàn cục
    $(document).ready(function () {
        // Xử lý xóa sản phẩm từ mini cart
        $(document).on("click", ".mini-cart-remove", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productId = $(this).data("product-id");
            const $widget = $(this).closest(".product-widget");

            $widget.css("opacity", "0.5");

            CartAPI.removeFromCart(productId)
                .done(function (response) {
                    $widget.fadeOut(300, function () {
                        $(this).remove();
                        // Cập nhật lại giỏ hàng sau khi xóa
                        if (typeof window.updateHeaderCart === "function") {
                            window.updateHeaderCart();
                        }
                    });
                })
                .fail(function (xhr) {
                    if (xhr.status !== 401) {
                        // Nếu không phải lỗi đăng nhập đã được xử lý
                        $widget.css("opacity", "1");
                        Notifications.show(
                            "Failed to remove item: " +
                                (xhr.responseJSON?.message || "Unknown error"),
                            "error"
                        );
                    }
                });
        });

        // Xử lý thêm sản phẩm vào giỏ hàng từ danh sách sản phẩm
        $(document).on(
            "click",
            ".add-to-cart-btn:not([disabled])",
            function () {
                const $button = $(this);
                const productId = $button.data("product-id");
                const productName = $button.data("product-name");

                // Vô hiệu hóa nút và thêm icon loading
                $button
                    .prop("disabled", true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Adding...');

                CartAPI.addToCart(productId, 1)
                    .done(function (response) {
                        // Cập nhật header cart
                        if (typeof window.updateHeaderCart === "function") {
                            window.updateHeaderCart();
                        }

                        // Hiển thị thông báo
                        Notifications.show(
                            productName + " added to cart!",
                            "success"
                        );
                    })
                    .fail(function (xhr) {
                        if (xhr.status !== 401) {
                            // Nếu không phải lỗi đăng nhập đã được xử lý
                            Notifications.show(
                                "Failed to add product: " +
                                    (xhr.responseJSON?.message ||
                                        "Unknown error"),
                                "error"
                            );
                        }
                    })
                    .always(function () {
                        // Khôi phục nút
                        $button
                            .prop("disabled", false)
                            .html(
                                '<i class="fa fa-shopping-cart"></i> add to cart'
                            );
                    });
            }
        );
    });
})(jQuery);
