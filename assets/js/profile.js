/**
 * Profile page JavaScript functionality
 */
(function ($) {
    "use strict";

    // Constants
    const BASE_URL = window.BASE_URL || "";

    $(document).ready(function () {
        // Load profile information on page load
        loadProfileData();

        // Load order statistics
        loadOrderStats();

        // Setup form submit handlers
        setupProfileUpdateForm();
        setupPasswordChangeForm();
        setupAvatarUploadForm();

        // Handle tab switching to keep state
        $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
            // Store the active tab in local storage
            localStorage.setItem("profileActiveTab", $(e.target).attr("href"));
        });

        // Restore active tab from local storage
        const activeTab = localStorage.getItem("profileActiveTab");
        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab("show");
        }
    });

    /**
     * Load user profile data from API
     */
    function loadProfileData() {
        $.ajax({
            url: `${BASE_URL}/public/api/profile.php?action=view`,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    displayProfileData(response.data.user);
                } else {
                    showAlert("error", "Failed to load profile data");
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = `${BASE_URL}/public/login.php`;
                } else {
                    showAlert(
                        "error",
                        "An error occurred while loading profile data"
                    );
                }
            },
        });
    }

    /**
     * Display profile data in the form
     */
    function displayProfileData(user) {
        // Split full name into first and last name
        const nameParts = user.full_name ? user.full_name.split(" ") : ["", ""];
        const firstName = nameParts[0] || "";
        const lastName = nameParts.slice(1).join(" ") || "";

        // Set form values
        $('input[name="first-name"]').val(firstName);
        $('input[name="last-name"]').val(lastName);
        $('input[name="email"]').val(user.email || "");
        $('input[name="phone"]').val(user.phone || "");
        $('input[name="address"]').val(user.address || "");

        // Update profile picture
        if (user.avatar_path) {
            $(".profile-userpic img").attr(
                "src",
                `${BASE_URL}${user.avatar_path}`
            );
        } else {
            $(".profile-userpic img").attr(
                "src",
                `${BASE_URL}/assets/images/local/avatar_default.png`
            );
        }

        // Update username display if needed
        $(".profile-usertitle-name").text(
            user.full_name || user.username || "User"
        );
    }

    /**
     * Load order statistics
     */
    function loadOrderStats() {
        $.ajax({
            url: `${BASE_URL}/public/api/profile.php?action=orders`,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    displayOrderStats(response.data);
                }
            },
            error: function () {
                console.log("Failed to load order statistics");
            },
        });
    }

    /**
     * Display order statistics
     */
    function displayOrderStats(stats) {
        const statsHtml = `
            <div class="row stats-row">
                <div class="col-md-3 col-xs-6">
                    <div class="stat-box total">
                        <h4>${stats.total}</h4>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="stat-box pending">
                        <h4>${stats.pending}</h4>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="stat-box completed">
                        <h4>${stats.completed}</h4>
                        <p>Completed</p>
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="stat-box cancelled">
                        <h4>${stats.cancelled}</h4>
                        <p>Cancelled</p>
                    </div>
                </div>
            </div>
        `;

        // Add stats at the top of orders tab
        $("#orders .section-title").after(statsHtml);

        // If there are no orders, show a message
        if (stats.total === 0) {
            $("#orders .order-products").html(
                '<div class="alert alert-info">You have no orders yet.</div>'
            );
        } else {
            // We would load actual orders here
            loadOrderHistory();
        }
    }

    /**
     * Load order history for the orders tab
     */
    function loadOrderHistory() {
        $.ajax({
            url: `${BASE_URL}/public/api/order.php?action=list&page=1&limit=5`,
            type: "GET",
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    let ordersHtml = "";

                    response.data.forEach(function (order) {
                        const orderDate = new Date(
                            order.created_at
                        ).toLocaleDateString("en-US", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                        });

                        ordersHtml += `
                            <div class="order-col">
                                <div>
                                    <strong>ORDER #${
                                        order.order_id
                                    }</strong> - ${orderDate}
                                    <span class="order-status ${
                                        order.status
                                    }">${
                            order.status.charAt(0).toUpperCase() +
                            order.status.slice(1)
                        }</span>
                                </div>
                                <div>
                                    <strong>$${parseFloat(
                                        order.total_amount
                                    ).toFixed(2)}</strong>
                                    <a href="${BASE_URL}/public/orders.php" class="view-btn">View</a>
                                </div>
                            </div>
                        `;
                    });

                    $("#orders .order-products").html(ordersHtml);

                    // Add view all orders link
                    $("#orders .order-products").append(`
                        <div class="text-center" style="margin-top: 20px;">
                            <a href="${BASE_URL}/public/orders.php" class="primary-btn">View All Orders</a>
                        </div>
                    `);
                }
            },
            error: function () {
                $("#orders .order-products").html(
                    '<div class="alert alert-danger">Failed to load order history.</div>'
                );
            },
        });
    }

    /**
     * Setup profile update form
     */
    function setupProfileUpdateForm() {
        $("#profile form").on("submit", function (e) {
            e.preventDefault();

            // Sử dụng validator từ profile-validate.js
            if (
                window.profileFormValidator &&
                window.profileFormValidator.validateProfileForm(this)
            ) {
                // Form hợp lệ, tiến hành gửi API

                // Combine first and last name
                const firstName = $('input[name="first-name"]').val().trim();
                const lastName = $('input[name="last-name"]').val().trim();
                const fullName = firstName + (lastName ? " " + lastName : "");

                // Lấy CSRF token từ form
                const csrfToken = $('input[name="csrf_token"]').val();

                // Kiểm tra xem token có tồn tại không
                if (!csrfToken) {
                    showAlert(
                        "error",
                        "Security token is missing. Please refresh the page."
                    );
                    return;
                }

                // Get form data
                const formData = {
                    action: "update",
                    full_name: fullName,
                    email: $('input[name="email"]').val().trim(),
                    phone: $('input[name="phone"]').val().trim(),
                    address: $('input[name="address"]').val().trim(),
                    csrf_token: csrfToken, // Thêm CSRF token vào request
                };

                $.ajax({
                    url: `${BASE_URL}/public/api/profile.php`,
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(formData),
                    beforeSend: function () {
                        // Disable submit button and show loading state
                        $('#profile form button[type="submit"]')
                            .prop("disabled", true)
                            .html(
                                '<i class="fa fa-spinner fa-spin"></i> Saving...'
                            );
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert(
                                "success",
                                "Profile updated successfully"
                            );

                            // Nếu server trả về token mới, cập nhật trong form
                            if (response.new_csrf_token) {
                                $('input[name="csrf_token"]').val(
                                    response.new_csrf_token
                                );
                            }
                        } else {
                            showAlert(
                                "error",
                                response.message || "Failed to update profile"
                            );
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 403) {
                            // Lỗi CSRF token
                            showAlert(
                                "error",
                                "Security token expired. Please refresh the page and try again."
                            );
                        } else if (
                            xhr.responseJSON &&
                            xhr.responseJSON.errors
                        ) {
                            showAlert(
                                "error",
                                xhr.responseJSON.errors.join("<br>")
                            );
                        } else {
                            showAlert(
                                "error",
                                "An error occurred while updating profile"
                            );
                        }
                    },
                    complete: function () {
                        // Re-enable submit button
                        $('#profile form button[type="submit"]')
                            .prop("disabled", false)
                            .html("Save Changes");
                    },
                });
            }
        });
    }

    /**
     * Setup password change form
     */
    function setupPasswordChangeForm() {
        $("#settings form").on("submit", function (e) {
            e.preventDefault();

            // Sử dụng validator từ profile-validate.js
            if (
                window.profileFormValidator &&
                window.profileFormValidator.validatePasswordForm(this)
            ) {
                // Form hợp lệ, tiến hành gửi API

                // Lấy thông tin từ form
                const currentPassword = $(
                    'input[name="current-password"]'
                ).val();
                const newPassword = $('input[name="new-password"]').val();
                const confirmPassword = $(
                    'input[name="confirm-password"]'
                ).val();
                const csrfToken = $('input[name="csrf_token"]').val();

                const formData = {
                    action: "change-password",
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword,
                    csrf_token: csrfToken,
                };

                $.ajax({
                    url: `${BASE_URL}/public/api/profile.php`,
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(formData),
                    beforeSend: function () {
                        $('#settings form button[type="submit"]')
                            .prop("disabled", true)
                            .html(
                                '<i class="fa fa-spinner fa-spin"></i> Updating...'
                            );
                    },
                    success: function (response) {
                        if (response.success) {
                            showAlert(
                                "success",
                                "Password updated successfully"
                            );
                            $("#settings form")[0].reset();
                        } else {
                            showAlert(
                                "error",
                                response.message || "Failed to update password"
                            );
                        }
                    },
                    error: function (xhr) {
                        showAlert(
                            "error",
                            xhr.responseJSON
                                ? xhr.responseJSON.message
                                : "An error occurred while updating password"
                        );
                    },
                    complete: function () {
                        $('#settings form button[type="submit"]')
                            .prop("disabled", false)
                            .html("Update Password");
                    },
                });
            }
        });
    }

    /**
     * Setup avatar upload form
     */
    function setupAvatarUploadForm() {
        // Create file input for avatar upload
        const fileInput = $(
            '<input type="file" name="avatar" accept="image/*" style="display: none;">'
        );
        $("body").append(fileInput);

        // Handle button click to trigger file input
        $(".change-photo-btn").on("click", function () {
            fileInput.click();
        });

        // Handle file selection
        fileInput.on("change", function (e) {
            if (!this.files || !this.files[0]) return;

            const file = this.files[0];

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showAlert("error", "Image size must be less than 2MB");
                return;
            }

            // Create FormData to send file
            const formData = new FormData();
            formData.append("avatar", file);
            formData.append("action", "upload-avatar");

            $.ajax({
                url: `${BASE_URL}/public/api/profile.php`,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(".change-photo-btn")
                        .prop("disabled", true)
                        .html('<i class="fa fa-spinner fa-spin"></i>');
                },
                success: function (response) {
                    if (response.success) {
                        // Update avatar image
                        $(".profile-userpic img").attr(
                            "src",
                            `${BASE_URL}${
                                response.data.avatar_path
                            }?v=${Date.now()}`
                        );
                        showAlert("success", "Avatar updated successfully");
                    } else {
                        showAlert(
                            "error",
                            response.message || "Failed to update avatar"
                        );
                    }
                },
                error: function (xhr) {
                    showAlert(
                        "error",
                        xhr.responseJSON
                            ? xhr.responseJSON.message
                            : "An error occurred while uploading avatar"
                    );
                    console.error("Upload error:", xhr.responseText);
                },
                complete: function () {
                    $(".change-photo-btn")
                        .prop("disabled", false)
                        .html("Change Photo");
                    fileInput.val(""); // Reset file input
                },
            });
        });
    }

    /**
     * Show alert message
     */
    function showAlert(type, message) {
        // Remove any existing alerts
        $(".alert-message").remove();

        // Create alert element
        const alertClass =
            type === "success" ? "alert-success" : "alert-danger";
        const alertHtml = `
            <div class="alert ${alertClass} alert-message">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                ${message}
            </div>
        `;

        // Insert alert at the top of the active tab pane
        $(".tab-pane.active .section-title").after(alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(function () {
            $(".alert-message").fadeOut(500, function () {
                $(this).remove();
            });
        }, 5000);
    }
})(jQuery);
