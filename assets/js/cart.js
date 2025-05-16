/**
 * File quản lý chức năng giỏ hàng (cart.js)
 */

(function ($) {
    "use strict";

    $(document).ready(function () {
        // Hủy bỏ các event handler cũ từ main.js trước
        $(".qty-up, .qty-down").off("click");

        // Xử lý nút tăng số lượng
        $(".qty-up").click(function (e) {
            // Kiểm tra xem sự kiện đã được xử lý chưa
            if (e.handled !== true) {
                e.handled = true;

                var $input = $(this).parent().find("input");
                var currentValue = parseInt($input.val());
                var maxValue = parseInt($input.attr("max") || 99999);

                // Tăng số lượng lên 1, nhưng không vượt quá giá trị max
                if (currentValue < maxValue) {
                    $input.val(currentValue + 1);
                    $input.trigger("change"); // Kích hoạt sự kiện change
                }
            }
        });

        // Xử lý nút giảm số lượng
        $(".qty-down").click(function (e) {
            // Kiểm tra xem sự kiện đã được xử lý chưa
            if (e.handled !== true) {
                e.handled = true;

                var $input = $(this).parent().find("input");
                var currentValue = parseInt($input.val());
                var minValue = parseInt($input.attr("min") || 1);

                // Giảm số lượng xuống 1, nhưng không dưới giá trị min
                if (currentValue > minValue) {
                    $input.val(currentValue - 1);
                    $input.trigger("change"); // Kích hoạt sự kiện change
                }
            }
        });

        // Đảm bảo giá trị nhập vào luôn hợp lệ
        $(".item-quantity").on("input change", function () {
            var $input = $(this);
            var value = parseInt($input.val());
            var minValue = parseInt($input.attr("min") || 1);
            var maxValue = parseInt($input.attr("max") || 99999);

            // Nếu giá trị không phải số hoặc nhỏ hơn min
            if (isNaN(value) || value < minValue) {
                $input.val(minValue);
            }
            // Nếu lớn hơn max
            else if (value > maxValue) {
                $input.val(maxValue);
            }
        });

        // Xử lý nút xóa sản phẩm
        $(".remove-from-cart").click(function () {
            const cartItem = $(this).closest(".cart-item");
            const productId = cartItem.data("product-id");
            removeFromCart(productId, cartItem);
        });

        // Xử lý nút cập nhật giỏ hàng
        $("#update-cart").click(function () {
            updateAllCartItems();
        });

        // Hàm xóa sản phẩm khỏi giỏ hàng
        function removeFromCart(productId, element) {
            const confirmRemove = confirm(
                "Are you sure you want to remove this item from your cart?"
            );

            if (confirmRemove) {
                element.addClass("loading");

                $.ajax({
                    url: BASE_URL + "/public/api/cart.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        action: "remove",
                        product_id: productId,
                    }),
                    success: function (response) {
                        element.fadeOut(300, function () {
                            element.remove();
                            updateCartSummary();

                            // Check if cart is now empty
                            if ($(".cart-item").length === 0) {
                                $("#cart-container").html(`
                                <div class="empty-cart">
                                    <h3>Your cart is empty</h3>
                                    <p>Looks like you haven't added any products to your cart yet.</p>
                                    <a href="products.php" class="primary-btn">Continue Shopping</a>
                                </div>
                            `);
                                $(".order-submit").addClass("disabled");
                            }

                            // Update cart icon in header
                            updateCartCount();
                            showNotification("Product removed from cart");
                        });
                    },
                    error: function (xhr) {
                        element.removeClass("loading");
                        showNotification(
                            "Failed to remove item: " +
                                (xhr.responseJSON?.message || "Unknown error"),
                            "error"
                        );
                    },
                });
            }
        }

        // Thay thế hàm updateAllCartItems() hiện tại bằng hàm này
        function updateAllCartItems() {
            $("#cart-container").addClass("loading");
            $("#update-cart").prop("disabled", true).text("Updating...");

            const promises = [];

            $(".cart-item").each(function () {
                const $item = $(this);
                const productId = $item.data("product-id");
                const quantity = parseInt($item.find(".item-quantity").val());

                if (quantity > 0) {
                    const promise = $.ajax({
                        url: BASE_URL + "/public/api/cart.php",
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            action: "update",
                            product_id: productId,
                            quantity: quantity,
                        }),
                    });

                    promises.push(promise);
                }
            });

            $.when
                .apply($, promises)
                .then(function () {
                    // Hiển thị thông báo thành công
                    showNotification("Cart updated successfully");

                    // Đợi 0.5 giây và sau đó tải lại trang
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                })
                .fail(function (xhr) {
                    $("#cart-container").removeClass("loading");
                    $("#update-cart")
                        .prop("disabled", false)
                        .text("Update Cart");
                    showNotification(
                        "Failed to update cart: " +
                            (xhr.responseJSON?.message || "Unknown error"),
                        "error"
                    );
                });
        }

        // Hàm hiển thị thông báo
        function showNotification(message, type = "success") {
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
            $("#notification-message").text(message);

            // Hiển thị thông báo
            $("#cart-notification").slideDown();

            // Tự động ẩn sau 5 giây
            setTimeout(function () {
                $("#cart-notification").slideUp();
            }, 5000);
        }

        // Đóng thông báo khi nhấn nút close
        $(document).on("click", ".cart-notification .close", function () {
            $("#cart-notification").slideUp();
        });
    });
})(jQuery);
