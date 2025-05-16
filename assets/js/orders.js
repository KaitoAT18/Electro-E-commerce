/**
 * Tệp JavaScript dành cho trang Orders
 */

(function ($) {
    "use strict";

    $(document).ready(function () {
        const BASE_URL = window.BASE_URL || "";
        let currentPage = 1;
        let totalPages = 1;
        let orders = [];

        // Load orders khi trang được tải
        loadOrders(currentPage);

        // Hàm load danh sách đơn hàng
        function loadOrders(page) {
            $(".orders-list").html(
                '<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Loading orders...</p></div>'
            );

            $.ajax({
                url: `${BASE_URL}/public/api/order.php?action=list&page=${page}&limit=5`,
                type: "GET",
                success: function (response) {
                    if (response.success) {
                        orders = response.data;
                        totalPages = response.pagination.total_pages;
                        currentPage = response.pagination.current_page;

                        // Hiển thị đơn hàng
                        renderOrders(orders);

                        // Cập nhật phân trang
                        renderPagination(response.pagination);
                    } else {
                        $(".orders-list").html(
                            '<div class="alert alert-warning">No orders found.</div>'
                        );
                    }
                },
                error: function (xhr) {
                    // Xử lý lỗi, ví dụ: phiên đăng nhập hết hạn
                    if (xhr.status === 401) {
                        window.location.href = `${BASE_URL}/public/login.php`;
                    } else {
                        $(".orders-list").html(
                            '<div class="alert alert-danger">Failed to load orders. Please try again.</div>'
                        );
                    }
                },
            });
        }

        // Hàm hiển thị danh sách đơn hàng
        function renderOrders(orders) {
            if (orders.length === 0) {
                $(".orders-list").html(
                    '<div class="alert alert-info">You have no orders yet.</div>'
                );
                return;
            }

            let html = "";

            orders.forEach(function (order) {
                const orderDate = new Date(order.created_at).toLocaleDateString(
                    "en-US",
                    {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                    }
                );

                // Xác định class CSS cho trạng thái đơn hàng
                let statusClass = "";
                switch (order.status) {
                    case "pending":
                        statusClass = "pending";
                        break;
                    case "processing":
                        statusClass = "processing";
                        break;
                    case "shipped":
                        statusClass = "shipped";
                        break;
                    case "completed":
                        statusClass = "delivered";
                        break;
                    case "cancelled":
                        statusClass = "cancelled";
                        break;
                    default:
                        statusClass = "";
                        break;
                }

                // Tạo HTML cho từng đơn hàng
                html += `
                <div class="order-item" data-order-id="${order.order_id}">
                    <div class="order-header">
                        <div class="row">
                            <div class="col-md-3 col-sm-3">
                                <h4 class="order-id">Order #${
                                    order.order_id
                                }</h4>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <span class="order-date">${orderDate}</span>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <span class="order-total">Total: $${parseFloat(
                                    order.total_amount
                                ).toFixed(2)}</span>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <span class="order-status ${statusClass}">${
                    order.status.charAt(0).toUpperCase() + order.status.slice(1)
                }</span>
                            </div>
                        </div>
                    </div>
                    <div class="order-body">
                        <div class="row">
                            <div class="col-md-8 col-sm-7">
                                <div class="order-product-info">
                                    <h5>
                                        ${
                                            order.total_items
                                        } item(s) in this order
                                    </h5>
                                    <!-- Thêm kết quả tải thông tin sản phẩm -->
                                    <div class="product-preview" id="products-${
                                        order.order_id
                                    }">
                                        <p><i class="fa fa-spinner fa-spin"></i> Loading product details...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-5">
                                <div class="order-actions">
                                    <button class="order-action-btn view-details" data-order-id="${
                                        order.order_id
                                    }">
                                        View Details
                                    </button>
                                    ${
                                        order.status !== "completed" &&
                                        order.status !== "cancelled"
                                            ? `<button class="order-action-btn cancel-order" data-toggle="modal"
                                            data-target="#cancelOrderModal" data-orderid="${order.order_id}">
                                            Cancel Order
                                        </button>`
                                            : ""
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;

                // Sau khi thêm đơn hàng vào DOM, tải chi tiết sản phẩm của đơn hàng
                setTimeout(function () {
                    loadOrderProductPreview(order.order_id);
                }, 100);
            });

            $(".orders-list").html(html);
        }

        // Thêm hàm mới để tải thông tin sản phẩm cho mỗi đơn hàng
        function loadOrderProductPreview(orderId) {
            $.ajax({
                url: `${BASE_URL}/public/api/order.php?action=detail&order_id=${orderId}`,
                type: "GET",
                success: function (response) {
                    if (response.success && response.data.items.length > 0) {
                        const items = response.data.items;
                        let previewHtml = '<div class="product-preview-items">';

                        // Hiển thị tối đa 3 sản phẩm trong preview
                        const maxPreviewItems = Math.min(items.length, 3);

                        for (let i = 0; i < maxPreviewItems; i++) {
                            const item = items[i];
                            previewHtml += `
                                <div class="preview-item">
                                    <div class="preview-img">
                                        <img src="${BASE_URL}${
                                item.primary_image ||
                                "/assets/images/placeholder.png"
                            }" alt="${item.product_name}">
                                    </div>
                                    <div class="preview-info">
                                        <p class="preview-name">${
                                            item.product_name
                                        }</p>
                                        <p class="preview-qty">Qty: ${
                                            item.quantity
                                        }</p>
                                    </div>
                                </div>
                            `;
                        }

                        // Nếu có nhiều hơn 3 sản phẩm, hiển thị thông báo
                        if (items.length > 3) {
                            previewHtml += `<div class="more-items">+${
                                items.length - 3
                            } more item(s)</div>`;
                        }

                        previewHtml += "</div>";
                        $(`#products-${orderId}`).html(previewHtml);
                    } else {
                        $(`#products-${orderId}`).html(
                            "<p>No product details available</p>"
                        );
                    }
                },
                error: function () {
                    $(`#products-${orderId}`).html(
                        "<p>Failed to load product details</p>"
                    );
                },
            });
        }

        // Hàm hiển thị phân trang
        function renderPagination(pagination) {
            const { current_page, total_pages, total_orders, limit } =
                pagination;

            // Hiển thị thông tin về số lượng đơn hàng
            let showingStart = (current_page - 1) * limit + 1;
            let showingEnd = Math.min(showingStart + limit - 1, total_orders);

            $(".store-qty").text(
                `Showing ${showingStart}-${showingEnd} of ${total_orders} orders`
            );

            // Tạo các nút phân trang
            let paginationHtml = "";

            for (let i = 1; i <= total_pages; i++) {
                if (i === current_page) {
                    paginationHtml += `<li class="active">${i}</li>`;
                } else {
                    paginationHtml += `<li><a href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
                }
            }

            if (current_page < total_pages) {
                paginationHtml += `<li><a href="javascript:void(0)" data-page="${
                    current_page + 1
                }"><i class="fa fa-angle-right"></i></a></li>`;
            }

            $(".store-pagination").html(paginationHtml);
        }

        // Xử lý khi click vào nút phân trang
        $(document).on("click", ".store-pagination a", function (e) {
            e.preventDefault();
            const page = $(this).data("page");
            loadOrders(page);
        });

        // Xử lý khi click vào nút View Details
        $(document).on("click", ".view-details", function () {
            const orderId = $(this).data("order-id");
            loadOrderDetails(orderId);
        });

        // Hàm load chi tiết đơn hàng
        function loadOrderDetails(orderId) {
            $.ajax({
                url: `${BASE_URL}/public/api/order.php?action=detail&order_id=${orderId}`,
                type: "GET",
                success: function (response) {
                    if (response.success) {
                        const orderData = response.data;

                        // Hiển thị chi tiết đơn hàng trong modal
                        renderOrderDetails(orderData);

                        // Hiển thị modal
                        $("#orderDetailsModal").modal("show");
                    } else {
                        alert("Failed to load order details.");
                    }
                },
                error: function () {
                    alert("An error occurred while loading order details.");
                },
            });
        }

        // Hàm hiển thị chi tiết đơn hàng
        function renderOrderDetails(orderData) {
            const order = orderData.order;
            const items = orderData.items;

            // Cập nhật tiêu đề modal
            $("#orderDetailsModal .modal-title").text(
                `Order #${order.order_id} Details`
            );

            // Cập nhật thông tin đơn hàng
            const orderDate = new Date(order.created_at).toLocaleDateString(
                "en-US",
                {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                }
            );

            let statusClass = "";
            switch (order.status) {
                case "pending":
                    statusClass = "pending";
                    break;
                case "processing":
                    statusClass = "processing";
                    break;
                case "shipped":
                    statusClass = "shipped";
                    break;
                case "completed":
                    statusClass = "delivered";
                    break;
                case "cancelled":
                    statusClass = "cancelled";
                    break;
                default:
                    statusClass = "";
                    break;
            }

            // Cập nhật thông tin đơn hàng
            let headerHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order Date:</strong> ${orderDate}</p>
                        <p><strong>Order Status:</strong> <span class="order-status ${statusClass}">${
                order.status.charAt(0).toUpperCase() + order.status.slice(1)
            }</span></p>
                        <p><strong>Payment Method:</strong> Cash on Delivery</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Shipping Address:</strong></p>
                        <p>
                            ${order.first_name} ${order.last_name}<br>
                            ${order.address},<br>
                            ${order.city}, ${order.zip_code}<br>
                            ${order.country}
                        </p>
                    </div>
                </div>
            `;

            $(".order-detail-header").html(headerHtml);

            // Cập nhật danh sách sản phẩm
            let itemsHtml = "";

            items.forEach(function (item) {
                const itemTotal =
                    parseFloat(item.price) * parseInt(item.quantity);

                itemsHtml += `
                    <tr>
                        <td>
                            <div class="product-widget">
                                <div class="product-img">
                                    <img src="${BASE_URL}${
                    item.primary_image
                }" alt="" />
                                </div>
                                <div class="product-body">
                                    <h5 class="product-name">${
                                        item.product_name
                                    }</h5>
                                </div>
                            </div>
                        </td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>$${itemTotal.toFixed(2)}</td>
                    </tr>
                `;
            });

            $(".order-detail-items table tbody").html(itemsHtml);

            // Cập nhật tổng tiền
            let totalAmount = parseFloat(order.total_amount);

            let summaryHtml = `
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Subtotal</td>
                            <td>$${totalAmount.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td>Shipping</td>
                            <td>FREE</td>
                        </tr>
                        <tr>
                            <td>Tax</td>
                            <td>$0.00</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td><strong>$${totalAmount.toFixed(2)}</strong></td>
                        </tr>
                    </tbody>
                </table>
            `;

            $(".order-detail-summary .col-md-4").html(summaryHtml);

            // Tạo timeline theo trạng thái đơn hàng
            let trackingHtml = `<div class="tracking-timeline">`;

            // Các bước trong quá trình đơn hàng
            const steps = [
                { status: "pending", name: "Order Placed" },
                { status: "processing", name: "Processing" },
                { status: "shipped", name: "Shipped" },
                { status: "completed", name: "Delivered" },
            ];

            // Logic để đánh dấu các bước đã hoàn thành
            let currentStep = -1;

            if (order.status === "cancelled") {
                // Đơn hàng đã hủy, chỉ hiển thị bước đặt hàng
                currentStep = 0;
            } else {
                // Xác định bước hiện tại dựa vào trạng thái đơn hàng
                for (let i = 0; i < steps.length; i++) {
                    if (steps[i].status === order.status) {
                        currentStep = i;
                        break;
                    }
                }
            }

            // Tạo HTML cho timeline
            steps.forEach((step, index) => {
                const isCompleted =
                    order.status !== "cancelled" && index <= currentStep;
                const stepClass = isCompleted ? "completed" : "";

                trackingHtml += `
                    <div class="tracking-step ${stepClass}">
                        <div class="step-icon">
                            ${isCompleted ? '<i class="fa fa-check"></i>' : ""}
                        </div>
                        <div class="step-info">
                            <h6>${step.name}</h6>
                            ${isCompleted ? `<p>${orderDate}</p>` : ""}
                        </div>
                    </div>
                `;
            });

            // Nếu đơn hàng đã hủy, hiển thị step hủy đơn
            if (order.status === "cancelled") {
                trackingHtml += `
                    <div class="tracking-step completed">
                        <div class="step-icon">
                            <i class="fa fa-times"></i>
                        </div>
                        <div class="step-info">
                            <h6>Cancelled</h6>
                            <p>${orderDate}</p>
                        </div>
                    </div>
                `;
            }

            trackingHtml += `</div>`;

            $(".order-detail-tracking .tracking-timeline").html(trackingHtml);
        }

        // Xử lý khi mở modal Cancel Order
        $("#cancelOrderModal").on("show.bs.modal", function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data("orderid");
            const modal = $(this);
            modal.find("#cancelOrderId").text(orderId);
        });

        // Hiển thị trường nhập lý do khác khi chọn "Other"
        $(".input-select").change(function () {
            if ($(this).val() === "other") {
                $("#otherReasonGroup").show();
            } else {
                $("#otherReasonGroup").hide();
            }
        });

        // Xử lý khi nhấn nút Confirm Cancellation
        $("#confirmCancelBtn").click(function () {
            const reason = $(".input-select").val();
            const otherReason = $("textarea").val();
            const orderId = $("#cancelOrderId").text();

            // Kiểm tra xem đã chọn lý do chưa
            if (!reason) {
                alert("Please select a reason for cancellation.");
                return;
            }

            // Kiểm tra nếu chọn Other nhưng không nhập lý do
            if (reason === "other" && !otherReason.trim()) {
                alert("Please specify your reason for cancellation.");
                return;
            }

            // Chuẩn bị dữ liệu để gửi
            const cancelReason = reason === "other" ? otherReason : reason;

            // Gửi yêu cầu hủy đơn hàng
            $.ajax({
                url: `${BASE_URL}/public/api/order.php`,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    action: "cancel",
                    order_id: orderId,
                    reason: cancelReason,
                }),
                success: function (response) {
                    if (response.success) {
                        // Đóng modal
                        $("#cancelOrderModal").modal("hide");

                        // Hiển thị thông báo thành công
                        alert(
                            "Order #" +
                                orderId +
                                " has been cancelled successfully."
                        );

                        // Cập nhật UI để hiển thị đơn hàng đã huỷ
                        $(`[data-orderid="${orderId}"]`)
                            .closest(".order-item")
                            .find(".order-status")
                            .removeClass("pending shipped processing")
                            .addClass("cancelled")
                            .text("Cancelled");

                        // Xóa nút Cancel Order
                        $(`[data-orderid="${orderId}"]`).remove();

                        // Tải lại danh sách đơn hàng
                        loadOrders(currentPage);
                    } else {
                        alert(
                            response.message ||
                                "Failed to cancel order. Please try again."
                        );
                    }
                },
                error: function (xhr) {
                    alert(
                        "An error occurred while cancelling the order. Please try again."
                    );
                },
            });
        });
    });
})(jQuery);
