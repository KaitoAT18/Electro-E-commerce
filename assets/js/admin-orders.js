/**
 * Admin Order History JavaScript
 * Xử lý tương tác với API order-history.php và hiển thị lịch sử đơn hàng
 */

(function ($) {
    // Biến toàn cục
    let orders = [];

    // Khởi tạo khi document ready
    $(document).ready(function () {
        // Load lịch sử đơn hàng khi tab orders được kích hoạt
        $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
            if ($(e.target).attr("href") === "#orders") {
                loadOrderHistory();
            }
        });

        // Load lịch sử đơn hàng nếu tab orders đã active
        if ($("#orders").hasClass("active")) {
            loadOrderHistory();
        }
    });

    /**
     * Load lịch sử đơn hàng từ API
     */
    function loadOrderHistory() {
        $("#orders .panel-body").html(
            '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading order history...</p></div>'
        );

        // Xóa tham số tìm kiếm trong URL
        let apiUrl = `${BASE_URL}/public/api/order-history.php`;

        $.ajax({
            url: apiUrl,
            type: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
            success: function (response) {
                console.log("Order history response:", response);
                if (response.success) {
                    orders = response.data;
                    renderOrderHistoryTable(orders);
                } else {
                    showOrderAlert(
                        "danger",
                        response.message || "Failed to load order history"
                    );
                }
            },
            error: function (xhr) {
                console.error("Error loading orders:", xhr);
                $("#orders .panel-body").html(
                    '<div class="alert alert-danger">Failed to load order history. Please try again.</div>'
                );
            },
        });
    }

    /**
     * Hiển thị lịch sử đơn hàng trong bảng
     */
    function renderOrderHistoryTable(orders) {
        if (!orders || orders.length === 0) {
            $("#orders .panel-body").html(
                '<div class="alert alert-info">No orders found.</div>'
            );
            return;
        }

        let tableHTML = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        orders.forEach(function (order) {
            const customerName =
                order.customer_name || order.full_name || "Unknown Customer";

            tableHTML += `
                <tr>
                    <td>#${order.order_id}</td>
                    <td>
                        <strong>${customerName}</strong><br>
                        <small>${order.email}</small>
                    </td>
                    <td>${order.order_date_formatted}</td>
                    <td>${order.item_count} items</td>
                    <td>$${order.total_amount_formatted}</td>
                    <td><span class="label ${order.status_class}">${order.status_text}</span></td>
                    <td>
                        <button class="btn btn-info btn-sm view-order" data-id="${order.order_id}" title="View Details">
                            <i class="fa fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tableHTML += `
                    </tbody>
                </table>
            </div>
        `;

        $("#orders .panel-body").html(tableHTML);

        // Gắn sự kiện cho nút xem chi tiết
        $(".view-order").click(function () {
            const orderId = $(this).data("id");
            const order = orders.find((o) => o.order_id == orderId);
            if (order) {
                showOrderDetails(order);
            }
        });
    }

    /**
     * Hiển thị chi tiết đơn hàng trong modal
     */
    function showOrderDetails(order) {
        // Cập nhật tiêu đề modal
        $("#viewOrderModal .modal-title").text(
            `Order Details #${order.order_id}`
        );

        // Xây dựng nội dung chi tiết đơn hàng
        let detailsHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h4>Order Information</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Order ID</th>
                            <td>#${order.order_id}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>${order.order_date_formatted}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="label ${order.status_class}">${
            order.status_text
        }</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h4>Customer Details</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>${order.customer_name}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>${order.email}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>${order.phone || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>${order.address}, ${order.city}, ${
            order.country
        } ${order.zip_code}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <h4>Order Items</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        let subTotal = 0;

        // Thêm từng sản phẩm
        if (order.items && order.items.length > 0) {
            order.items.forEach(function (item) {
                subTotal += parseFloat(item.total_price);
                detailsHTML += `
                    <tr>
                        <td>
                            <div class="product-info">
                                ${
                                    item.image_path
                                        ? `<img src="${item.image_url}" alt="${item.product_name}" width="50">`
                                        : ""
                                }
                                <span>${item.product_name}</span>
                            </div>
                        </td>
                        <td>$${item.price_formatted}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.total_price_formatted}</td>
                    </tr>
                `;
            });
        } else {
            detailsHTML += `
                <tr>
                    <td colspan="4" class="text-center">No items found in this order.</td>
                </tr>
            `;
        }

        detailsHTML += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Subtotal:</th>
                            <td>$${subTotal.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right">Shipping:</th>
                            <td>$${(
                                parseFloat(order.total_amount) - subTotal
                            ).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right">Total:</th>
                            <td><strong>$${
                                order.total_amount_formatted
                            }</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        // Cập nhật nội dung modal
        $("#viewOrderModal .modal-body").html(detailsHTML);

        // Làm rộng modal để hiển thị nhiều thông tin hơn
        $("#viewOrderModal .modal-dialog").addClass("modal-lg");

        // Hiện modal
        $("#viewOrderModal").modal("show");
    }

    /**
     * Hiển thị thông báo
     */
    function showOrderAlert(type, message) {
        // Tạo container nếu chưa có
        if ($("#order-alerts").length === 0) {
            $("#orders .panel-body").prepend('<div id="order-alerts"></div>');
        }

        // Xóa thông báo cũ
        $("#order-alerts").empty();

        // Tạo thông báo mới
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                ${message}
            </div>
        `;

        // Thêm thông báo vào container
        $("#order-alerts").html(alertHTML);

        // Tự động ẩn sau 5 giây
        setTimeout(function () {
            $("#order-alerts .alert").fadeOut();
        }, 5000);
    }
})(jQuery);
