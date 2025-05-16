/**
 * Admin User Management JavaScript
 * Xử lý tương tác với API user.php và quản lý người dùng
 */

(function ($) {
    // Biến toàn cục
    let currentPage = 1;
    let searchKeyword = "";
    let users = [];

    // Khởi tạo khi document ready
    $(document).ready(function () {
        // Load danh sách người dùng
        loadUsers();

        // Thiết lập tìm kiếm
        setupSearch();

        // Thiết lập form thêm người dùng
        setupAddUserForm();

        // Thiết lập form chỉnh sửa người dùng
        setupEditUserForm();

        // Xử lý các tab
        $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
            const target = $(e.target).attr("href");
            if (target === "#users") {
                loadUsers(currentPage);
            } else if (target === "#orders") {
                // Tải dữ liệu đơn hàng nếu cần
            }
        });
    });

    /**
     * Tải danh sách người dùng từ API
     */
    function loadUsers(page = 1) {
        currentPage = page;

        // Hiển thị loading
        $(".user-data-container").html(
            '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading users...</p></div>'
        );

        // Tạo query string cho API
        let queryParams = `action=list&page=${page}&limit=10`;
        if (searchKeyword) {
            queryParams += `&search=${encodeURIComponent(searchKeyword)}`;
        }

        // Gọi API để lấy danh sách người dùng
        $.ajax({
            url: `${BASE_URL}/public/api/user.php?${queryParams}`,
            type: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
            success: function (response) {
                if (response.success) {
                    users = response.data;
                    renderUserTable(users);
                    renderPagination(response.pagination);
                } else {
                    showAlert("danger", "Failed to load users");
                }
            },
            error: function (jqXHR) {
                handleAjaxError(null, jqXHR);
                $(".user-data-container").html(
                    '<div class="alert alert-danger">Failed to load users. Please try again.</div>'
                );
            },
        });
    }

    /**
     * Hiển thị danh sách người dùng trong bảng
     */
    function renderUserTable(users) {
        if (!users || users.length === 0) {
            $(".user-data-container").html(
                '<div class="alert alert-info">No users found.</div>'
            );
            return;
        }

        let tableHTML = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        users.forEach(function (user) {
            tableHTML += `
                <tr data-id="${user.user_id}">
                    <td>${user.user_id}</td>
                    <td>${user.username}</td>
                    <td>${user.full_name || "-"}</td>
                    <td>${user.email}</td>
                    <td><span class="label ${
                        user.role === "admin"
                            ? "label-primary"
                            : "label-default"
                    }">${user.role}</span></td>
                    <td><span class="label ${
                        user.is_active ? "label-success" : "label-danger"
                    }">${user.is_active ? "Active" : "Inactive"}</span></td>
                    <td>
                        <button class="btn btn-primary btn-sm edit-user" data-id="${
                            user.user_id
                        }" title="Edit User">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn ${
                            user.is_active ? "btn-warning" : "btn-success"
                        } btn-sm toggle-status" data-id="${
                user.user_id
            }" title="${user.is_active ? "Deactivate" : "Activate"} User">
                            <i class="fa ${
                                user.is_active ? "fa-ban" : "fa-check"
                            }"></i>
                        </button>
                        <button class="btn btn-danger btn-sm delete-user" data-id="${
                            user.user_id
                        }" title="Delete User">
                            <i class="fa fa-trash"></i>
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

        $(".user-data-container").html(tableHTML);

        // Gán sự kiện cho các nút trong bảng
        $(".edit-user").click(function () {
            const userId = $(this).data("id");
            openEditUserModal(userId);
        });

        $(".toggle-status").click(function () {
            const userId = $(this).data("id");
            const userIndex = users.findIndex((u) => u.user_id == userId);
            if (userIndex !== -1) {
                toggleUserStatus(userId, !users[userIndex].is_active);
            }
        });

        $(".delete-user").click(function () {
            const userId = $(this).data("id");
            confirmDeleteUser(userId);
        });
    }

    /**
     * Hiển thị phân trang
     */
    function renderPagination(pagination) {
        if (!pagination || pagination.last_page <= 1) {
            $("#user-pagination").empty();
            return;
        }

        const totalPages = pagination.last_page;
        const currentPage = pagination.current_page;

        let paginationHTML = `
            <nav aria-label="User pagination">
                <ul class="pagination">
                    <li class="${currentPage === 1 ? "disabled" : ""}">
                        <a href="#" aria-label="Previous" ${
                            currentPage > 1
                                ? 'data-page="' + (currentPage - 1) + '"'
                                : ""
                        }>
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
        `;

        // Hiển thị tối đa 5 trang
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="${i === currentPage ? "active" : ""}">
                    <a href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        paginationHTML += `
                    <li class="${currentPage === totalPages ? "disabled" : ""}">
                        <a href="#" aria-label="Next" ${
                            currentPage < totalPages
                                ? 'data-page="' + (currentPage + 1) + '"'
                                : ""
                        }>
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        `;

        $("#user-pagination").html(paginationHTML);

        // Gắn sự kiện cho nút phân trang
        $("#user-pagination a[data-page]").click(function (e) {
            e.preventDefault();
            loadUsers($(this).data("page"));
        });
    }

    /**
     * Thiết lập chức năng tìm kiếm
     */
    function setupSearch() {
        // Thêm ô tìm kiếm vào panel heading
        $(".panel-heading .panel-actions").prepend(`
            <div class="search-box">
                <div class="input-group">
                    <input type="text" id="search-users" class="form-control" placeholder="Search users...">
                    <span class="input-group-btn">
                        <button id="search-btn" class="btn btn-default" type="button">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </div>
        `);

        // Xử lý sự kiện tìm kiếm
        $("#search-btn").click(function () {
            searchKeyword = $("#search-users").val().trim();
            loadUsers(1);
        });

        // Tìm kiếm khi nhấn Enter
        $("#search-users").keypress(function (e) {
            if (e.which === 13) {
                searchKeyword = $(this).val().trim();
                loadUsers(1);
            }
        });
    }
    /**
     * Xác thực form theo các quy tắc
     * @param {string} formId - ID của form cần validate ("add-user-form" hoặc "edit-user-form")
     * @return {object} Kết quả validation và danh sách lỗi
     */
    function validateForm(formId) {
        let isValid = true;
        const errors = {};
        const isEditForm = formId === "edit-user-form";

        // Reset error messages
        $(`#${formId} .error-msg`).text("");
        $(`#${formId} .input, #${formId} .input-select`).removeClass("error");
        $(`#${formId}-errors`).hide().empty();

        // ===== VALIDATE ADD USER FORM =====
        if (!isEditForm) {
            // Username validation (chỉ cho form add)
            const username = $("#username").val()?.trim() || "";
            if (!username) {
                errors.username = "Username is required";
                $("#username").addClass("error");
                $("#username-error").text("Username is required");
                isValid = false;
            } else if (username.length < 5 || username.length > 20) {
                errors.username = "Username must be 5-20 characters long";
                $("#username").addClass("error");
                $("#username-error").text(
                    "Username must be 5-20 characters long"
                );
                isValid = false;
            } else if (!/^[a-zA-Z0-9]+$/.test(username)) {
                errors.username =
                    "Username can only contain letters and numbers";
                $("#username").addClass("error");
                $("#username-error").text(
                    "Username can only contain letters and numbers"
                );
                isValid = false;
            }

            // First Name
            const firstName = $("#first_name").val()?.trim() || "";
            if (!firstName) {
                errors.first_name = "First name is required";
                $("#first_name").addClass("error");
                $("#first_name-error").text("First name is required");
                isValid = false;
            } else if (firstName.length < 2) {
                errors.first_name = "First name must be at least 2 characters";
                $("#first_name").addClass("error");
                $("#first_name-error").text(
                    "First name must be at least 2 characters"
                );
                isValid = false;
            }

            // Last Name
            const lastName = $("#last_name").val()?.trim() || "";
            if (!lastName) {
                errors.last_name = "Last name is required";
                $("#last_name").addClass("error");
                $("#last_name-error").text("Last name is required");
                isValid = false;
            } else if (lastName.length < 2) {
                errors.last_name = "Last name must be at least 2 characters";
                $("#last_name").addClass("error");
                $("#last_name-error").text(
                    "Last name must be at least 2 characters"
                );
                isValid = false;
            }

            // Email
            const email = $("#email").val()?.trim() || "";
            if (!email) {
                errors.email = "Email is required";
                $("#email").addClass("error");
                $("#email-error").text("Email is required");
                isValid = false;
            } else if (
                !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email)
            ) {
                errors.email = "Invalid email format";
                $("#email").addClass("error");
                $("#email-error").text("Invalid email format");
                isValid = false;
            }

            // Password (required for add form)
            const password = $("#password").val()?.trim() || "";
            if (!password) {
                errors.password = "Password is required";
                $("#password").addClass("error");
                $("#password-error").text("Password is required");
                isValid = false;
            } else if (password.length < 6) {
                errors.password = "Password must be at least 6 characters long";
                $("#password").addClass("error");
                $("#password-error").text(
                    "Password must be at least 6 characters long"
                );
                isValid = false;
            }

            // Role
            const role = $("#role").val();
            if (!role || !["admin", "user"].includes(role)) {
                errors.role = "Please select a valid role";
                $("#role").addClass("error");
                $("#role-error").text("Please select a valid role");
                isValid = false;
            }
        }
        // ===== VALIDATE EDIT USER FORM =====
        else {
            // First Name (edit form)
            const editFirstName = $("#edit_first_name").val()?.trim() || "";
            if (!editFirstName) {
                errors.edit_first_name = "First name is required";
                $("#edit_first_name").addClass("error");
                $("#edit_first_name-error").text("First name is required");
                isValid = false;
            } else if (editFirstName.length < 2) {
                errors.edit_first_name =
                    "First name must be at least 2 characters";
                $("#edit_first_name").addClass("error");
                $("#edit_first_name-error").text(
                    "First name must be at least 2 characters"
                );
                isValid = false;
            }

            // Last Name (edit form)
            const editLastName = $("#edit_last_name").val()?.trim() || "";
            if (!editLastName) {
                errors.edit_last_name = "Last name is required";
                $("#edit_last_name").addClass("error");
                $("#edit_last_name-error").text("Last name is required");
                isValid = false;
            } else if (editLastName.length < 2) {
                errors.edit_last_name =
                    "Last name must be at least 2 characters";
                $("#edit_last_name").addClass("error");
                $("#edit_last_name-error").text(
                    "Last name must be at least 2 characters"
                );
                isValid = false;
            }

            // Email (edit form)
            const editEmail = $("#edit_email").val()?.trim() || "";
            if (!editEmail) {
                errors.edit_email = "Email is required";
                $("#edit_email").addClass("error");
                $("#edit_email-error").text("Email is required");
                isValid = false;
            } else if (
                !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(
                    editEmail
                )
            ) {
                errors.edit_email = "Invalid email format";
                $("#edit_email").addClass("error");
                $("#edit_email-error").text("Invalid email format");
                isValid = false;
            }

            // Password (optional for edit form)
            const editPassword = $("#edit_password").val()?.trim() || "";
            if (editPassword && editPassword.length < 6) {
                errors.edit_password =
                    "Password must be at least 6 characters long";
                $("#edit_password").addClass("error");
                $("#edit_password-error").text(
                    "Password must be at least 6 characters long"
                );
                isValid = false;
            }

            // Phone (optional, validation nếu có nhập)
            const editPhone = $("#edit_phone").val()?.trim() || "";
            if (editPhone && !/^[0-9+\-\s]{10}$/.test(editPhone)) {
                errors.edit_phone = "Please enter a valid phone number";
                $("#edit_phone").addClass("error");
                $("#edit_phone-error").text(
                    "Please enter a valid phone number"
                );
                isValid = false;
            }

            // Role (edit form)
            const editRole = $("#edit_role").val();
            if (!editRole || !["admin", "user"].includes(editRole)) {
                errors.edit_role = "Please select a valid role";
                $("#edit_role").addClass("error");
                $("#edit_role-error").text("Please select a valid role");
                isValid = false;
            }
        }

        // Validate full_name nếu được sử dụng
        if (!isEditForm && $("#full_name").length > 0) {
            const fullName = $("#full_name").val()?.trim() || "";
            if (!fullName) {
                errors.full_name = "Full name is required";
                $("#full_name").addClass("error");
                $("#full_name-error").text("Full name is required");
                isValid = false;
            }
        } else if (isEditForm && $("#edit_full_name").length > 0) {
            const editFullName = $("#edit_full_name").val()?.trim() || "";
            if (!editFullName) {
                errors.edit_full_name = "Full name is required";
                $("#edit_full_name").addClass("error");
                $("#edit_full_name-error").text("Full name is required");
                isValid = false;
            }
        }

        return { isValid, errors };
    }

    /**
     * Xử lý lỗi API - phiên bản nâng cao để chỉ hiển thị mỗi lỗi một lần duy nhất
     * @param {string} formPrefix - Tiền tố của form ('add-user' hoặc 'edit-user')
     * @param {object} response - Phản hồi từ server chứa thông tin lỗi
     */
    function handleApiErrors(formPrefix, response) {
        // Reset error display
        $(`#${formPrefix}-errors`).hide().empty();
        const formId = `${formPrefix}-form`;
        $(`#${formId} .error-msg`).text("");
        $(`#${formId} .input, #${formId} .input-select`).removeClass("error");

        // Nếu không có lỗi nhưng request không thành công
        if (!response.errors || response.errors.length === 0) {
            $(`#${formPrefix}-errors`)
                .show()
                .text(response.message || "An error occurred");
            return;
        }

        // Mapping từ tên field trả về từ API đến id của field trong form
        const fieldMapping = {
            // Trường hợp chung
            username: formPrefix === "add-user" ? "username" : null,
            email: formPrefix === "add-user" ? "email" : "edit_email",
            password: formPrefix === "add-user" ? "password" : "edit_password",
            role: formPrefix === "add-user" ? "role" : "edit_role",
            is_active:
                formPrefix === "add-user" ? "is_active" : "edit_is_active",

            // Form add/edit user
            full_name:
                formPrefix === "add-user" ? "full_name" : "edit_full_name",
            first_name:
                formPrefix === "add-user" ? "first_name" : "edit_first_name",
            last_name:
                formPrefix === "add-user" ? "last_name" : "edit_last_name",

            // Form edit user
            phone: "edit_phone",
            address: "edit_address",
        };

        // Theo dõi lỗi đã được xử lý để tránh hiển thị lặp lại
        const processedErrors = new Set();

        // Danh sách lỗi không gắn được cho trường cụ thể
        const generalErrors = [];

        // Bước 1: Phân loại các lỗi - ưu tiên gán vào trường đầu vào cụ thể trước
        response.errors.forEach((error) => {
            // Tìm field liên quan đến lỗi
            let fieldName = null;

            const patterns = [
                { regex: /username/i, field: "username" },
                { regex: /email/i, field: "email" },
                { regex: /password/i, field: "password" },
                { regex: /role/i, field: "role" },
                { regex: /full[\s_]name/i, field: "full_name" },
                { regex: /first[\s_]name/i, field: "first_name" },
                { regex: /last[\s_]name/i, field: "last_name" },
                { regex: /phone/i, field: "phone" },
                { regex: /address/i, field: "address" },
                { regex: /active/i, field: "is_active" },
            ];

            // Tìm kiếm field từ nội dung lỗi
            for (const pattern of patterns) {
                if (pattern.regex.test(error)) {
                    fieldName = pattern.field;
                    break;
                }
            }

            if (fieldName && fieldMapping[fieldName]) {
                const fieldId = fieldMapping[fieldName];

                // Kiểm tra xem field có tồn tại trong form hiện tại hay không
                if (fieldId && $(`#${fieldId}`).length > 0) {
                    // Chỉ xử lý lỗi nếu chưa hiển thị trước đó
                    if (!processedErrors.has(error)) {
                        $(`#${fieldId}`).addClass("error");
                        $(`#${fieldId}-error`).text(error);
                        processedErrors.add(error);
                    }
                } else {
                    // Field không tồn tại, thêm vào lỗi chung (nếu chưa xử lý)
                    if (!processedErrors.has(error)) {
                        generalErrors.push(error);
                        processedErrors.add(error);
                    }
                }
            } else {
                // Lỗi không liên quan đến field cụ thể
                if (!processedErrors.has(error)) {
                    generalErrors.push(error);
                    processedErrors.add(error);
                }
            }
        });

        // Bước 2: Hiển thị các lỗi chung trong container lỗi
        if (generalErrors.length > 0) {
            $(`#${formPrefix}-errors`)
                .show()
                .html('<ul class="list-unstyled"></ul>');
            generalErrors.forEach((error) => {
                $(`#${formPrefix}-errors ul`).append(
                    `<li><i class="fa fa-exclamation-circle"></i> ${error}</li>`
                );
            });
        }

        // Bước 3: Focus vào trường lỗi đầu tiên để cải thiện UX
        const firstErrorField = $(`#${formId} .error`).first();
        if (firstErrorField.length > 0) {
            firstErrorField.focus();
        }
    }

    /**
     * Thiết lập form thêm người dùng mới
     */
    function setupAddUserForm() {
        $("#add-user-form").submit(function (e) {
            e.preventDefault();

            // Validate form
            const { isValid, errors } = validateForm("add-user-form");
            if (!isValid) {
                return false;
            }

            // Lấy dữ liệu form
            const userData = {
                action: "create",
                csrf_token: CSRF_TOKEN,
                username: $("#username").val().trim(),
                email: $("#email").val().trim(),
                password: $("#password").val().trim(),
                role: $("#role").val(),
                is_active: $("#is_active").is(":checked"),
            };

            // Kiểm tra xem form có first_name/last_name hay full_name
            if ($("#first_name").length > 0 && $("#last_name").length > 0) {
                userData.first_name = $("#first_name").val().trim();
                userData.last_name = $("#last_name").val().trim();
            } else if ($("#full_name").length > 0) {
                userData.full_name = $("#full_name").val().trim();
            }

            // Gọi API để tạo người dùng mới
            $.ajax({
                url: `${BASE_URL}/public/api/user.php`,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(userData),
                beforeSend: function () {
                    $("#add-user-btn")
                        .prop("disabled", true)
                        .html(
                            '<i class="fa fa-spinner fa-spin"></i> Creating...'
                        );
                },
                success: function (response) {
                    if (response.success) {
                        // Đóng modal và hiển thị thông báo thành công
                        $("#addUserModal").modal("hide");
                        showAlert("success", "User created successfully");

                        // Reset form
                        $("#add-user-form")[0].reset();

                        // Cập nhật CSRF token nếu có
                        if (response.csrf_token) {
                            CSRF_TOKEN = response.csrf_token;
                            $('input[name="csrf_token"]').val(
                                response.csrf_token
                            );
                        }

                        // Reload danh sách người dùng
                        loadUsers(currentPage);
                    } else {
                        handleApiErrors("add-user", response);
                    }
                },
                error: function (jqXHR) {
                    handleAjaxError("add-user", jqXHR);
                },
                complete: function () {
                    $("#add-user-btn").prop("disabled", false).html("Add User");
                },
            });
        });
    }

    /**
     * Mở modal chỉnh sửa người dùng
     */
    function openEditUserModal(userId) {
        $("#edit_user_id").val(userId);
        $("#edit-user-form-fields").html(
            '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading user data...</div>'
        );
        $("#editUserModal").modal("show");

        // Lấy thông tin người dùng từ API
        $.ajax({
            url: `${BASE_URL}/public/api/user.php?action=detail&id=${userId}`,
            type: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
            success: function (response) {
                if (response.success) {
                    renderEditUserForm(response.data);
                } else {
                    $("#edit-user-form-fields").html(
                        `<div class="alert alert-danger">${
                            response.message || "Failed to load user data"
                        }</div>`
                    );
                }
            },
            error: function (jqXHR) {
                handleAjaxError(null, jqXHR);
                $("#edit-user-form-fields").html(
                    `<div class="alert alert-danger">An error occurred while loading user data</div>`
                );
            },
        });
    }

    /**
     * Hiển thị form chỉnh sửa người dùng
     */
    function renderEditUserForm(user) {
        // Tách full_name thành first_name và last_name (nếu có)
        let firstName = "",
            lastName = "";

        if (user.full_name) {
            const nameParts = user.full_name.trim().split(" ");
            if (nameParts.length > 1) {
                lastName = nameParts.pop();
                firstName = nameParts.join(" ");
            } else {
                firstName = user.full_name;
            }
        }

        const formHTML = `
            <div class="form-group">
                <label>Username</label>
                <input class="input" type="text" value="${
                    user.username
                }" disabled>
                <small class="text-muted">Username cannot be changed</small>
            </div>
            <div class="form-group">
                <label for="edit_first_name">First Name <span class="text-danger">*</span></label>
                <input class="input" type="text" id="edit_first_name" name="first_name" value="${firstName}" placeholder="First Name" required>
                <small class="error-msg" id="edit_first_name-error"></small>
            </div>
            <div class="form-group">
                <label for="edit_last_name">Last Name <span class="text-danger">*</span></label>
                <input class="input" type="text" id="edit_last_name" name="last_name" value="${lastName}" placeholder="Last Name" required>
                <small class="error-msg" id="edit_last_name-error"></small>
            </div>
            <div class="form-group">
                <label for="edit_email">Email <span class="text-danger">*</span></label>
                <input class="input" type="email" id="edit_email" name="email" value="${
                    user.email
                }" placeholder="Email" required>
                <small class="error-msg" id="edit_email-error"></small>
            </div>
            <div class="form-group">
                <label for="edit_phone">Phone</label>
                <input class="input" type="text" id="edit_phone" name="phone" value="${
                    user.phone || ""
                }" placeholder="Phone Number">
                <small class="error-msg" id="edit_phone-error"></small>
            </div>
            <div class="form-group">
                <label for="edit_address">Address</label>
                <input class="input" type="text" id="edit_address" name="address" value="${
                    user.address || ""
                }" placeholder="Address">
                <small class="error-msg" id="edit_address-error"></small>
            </div>
            <div class="form-group">
                <label for="edit_role">Role <span class="text-danger">*</span></label>
                <select class="input-select" id="edit_role" name="role" required>
                    <option value="user" ${
                        user.role === "user" ? "selected" : ""
                    }>Regular User</option>
                    <option value="admin" ${
                        user.role === "admin" ? "selected" : ""
                    }>Administrator</option>
                </select>
                <small class="error-msg" id="edit_role-error"></small>
            </div>
            <div class="form-group">
                <label for="edit_is_active">Status</label>
                <select class="input-select" id="edit_is_active" name="is_active">
                    <option value="1" ${
                        user.is_active ? "selected" : ""
                    }>Active</option>
                    <option value="0" ${
                        !user.is_active ? "selected" : ""
                    }>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_password">Password</label>
                <input class="input" type="password" id="edit_password" name="password" placeholder="Leave blank to keep current password">
                <small class="error-msg" id="edit_password-error"></small>
                <small class="help-text">Enter new password only if you want to change it</small>
            </div>
        `;

        $("#edit-user-form-fields").html(formHTML);
    }

    /**
     * Thiết lập form chỉnh sửa người dùng
     */
    function setupEditUserForm() {
        // Xử lý submit form
        $(document).on("submit", "#edit-user-form", function (e) {
            e.preventDefault();

            // Debug log - kiểm tra biểu mẫu đã được gửi
            console.log("Form submitted");

            // Validate form
            const { isValid, errors } = validateForm("edit-user-form");
            if (!isValid) {
                console.log("Form validation failed:", errors);
                return false;
            }

            // Lấy dữ liệu form
            const userId = $("#edit_user_id").val();
            if (!userId) {
                console.error("ERROR: User ID is missing!");
                alert("Error: User ID is missing. Please try again.");
                return false;
            }

            console.log("Editing user ID:", userId);

            const userData = {
                action: "update",
                user_id: userId,
                csrf_token: CSRF_TOKEN,
                email: $("#edit_email").val().trim(),
                phone: $("#edit_phone").val().trim(),
                address: $("#edit_address").val().trim(),
                role: $("#edit_role").val(),
                is_active: $("#edit_is_active").val() === "1",
            };

            // Kiểm tra xem form dùng first_name/last_name hay full_name
            if (
                $("#edit_first_name").length > 0 &&
                $("#edit_last_name").length > 0
            ) {
                const firstName = $("#edit_first_name").val().trim();
                const lastName = $("#edit_last_name").val().trim();

                // Tạo full_name từ first_name và last_name
                userData.full_name =
                    firstName + (lastName ? " " + lastName : "");
                console.log("Created full_name:", userData.full_name);
            } else if ($("#edit_full_name").length > 0) {
                userData.full_name = $("#edit_full_name").val().trim();
            }

            // Thêm mật khẩu nếu đã nhập
            const password = $("#edit_password").val().trim();
            if (password) {
                userData.password = password;
            }

            // Debug log - kiểm tra dữ liệu gửi đi
            console.log("Sending user data:", JSON.stringify(userData));

            // Gọi API để cập nhật thông tin người dùng
            $.ajax({
                url: `${BASE_URL}/public/api/user.php`,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(userData),
                beforeSend: function (xhr) {
                    console.log(
                        "Request URL:",
                        `${BASE_URL}/public/api/user.php`
                    );
                    console.log("CSRF Token:", CSRF_TOKEN);

                    $("#edit-user-btn")
                        .prop("disabled", true)
                        .html(
                            '<i class="fa fa-spinner fa-spin"></i> Updating...'
                        );

                    // Thêm header để nhận dạng request
                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                },
                success: function (response) {
                    console.log("API Response:", response);

                    if (response.success) {
                        // Đóng modal và hiển thị thông báo thành công
                        $("#editUserModal").modal("hide");
                        showAlert("success", "User updated successfully");

                        // Cập nhật CSRF token nếu có
                        if (response.csrf_token) {
                            CSRF_TOKEN = response.csrf_token;
                            $('input[name="csrf_token"]').val(
                                response.csrf_token
                            );
                        }

                        // Reload danh sách người dùng
                        loadUsers(currentPage);
                    } else {
                        console.error("Update failed:", response);
                        handleApiErrors("edit-user", response);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown);
                    console.log("Status code:", jqXHR.status);
                    console.log("Response text:", jqXHR.responseText);

                    try {
                        const respObj = JSON.parse(jqXHR.responseText);
                        console.log("Parsed error response:", respObj);
                    } catch (e) {
                        console.log("Could not parse response as JSON");
                    }

                    handleAjaxError("edit-user", jqXHR);
                },
                complete: function () {
                    $("#edit-user-btn")
                        .prop("disabled", false)
                        .html("Update User");
                },
            });
        });
    }

    /**
     * Bật/tắt trạng thái người dùng
     */
    function toggleUserStatus(userId, newStatus) {
        const userData = {
            action: "update",
            user_id: userId,
            csrf_token: CSRF_TOKEN,
            is_active: newStatus,
        };

        // Gọi API cập nhật trạng thái
        $.ajax({
            url: `${BASE_URL}/public/api/user.php`,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(userData),
            success: function (response) {
                if (response.success) {
                    showAlert(
                        "success",
                        `User ${
                            newStatus ? "activated" : "deactivated"
                        } successfully`
                    );

                    // Cập nhật CSRF token nếu có
                    if (response.csrf_token) {
                        CSRF_TOKEN = response.csrf_token;
                        $('input[name="csrf_token"]').val(response.csrf_token);
                    }

                    // Reload danh sách người dùng
                    loadUsers(currentPage);
                } else {
                    handleApiErrors("status-toggle", response);
                }
            },
            error: function (jqXHR) {
                handleAjaxError("status-toggle", jqXHR);
            },
        });
    }

    /**
     * Xác nhận xóa người dùng
     */
    function confirmDeleteUser(userId) {
        if (
            confirm(
                "Are you sure you want to delete this user? This action cannot be undone."
            )
        ) {
            deleteUser(userId);
        }
    }

    /**
     * Xóa người dùng
     */
    function deleteUser(userId) {
        const userData = {
            action: "delete",
            user_id: userId,
            csrf_token: CSRF_TOKEN,
            mode: "hard", // Xóa hoàn toàn khỏi cơ sở dữ liệu
        };

        // Gọi API xóa người dùng
        $.ajax({
            url: `${BASE_URL}/public/api/user.php`,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(userData),
            success: function (response) {
                if (response.success) {
                    showAlert("success", "User deleted successfully");

                    // Cập nhật CSRF token nếu có
                    if (response.csrf_token) {
                        CSRF_TOKEN = response.csrf_token;
                        $('input[name="csrf_token"]').val(response.csrf_token);
                    }

                    // Reload danh sách người dùng
                    loadUsers(currentPage);
                } else {
                    handleApiErrors("user-delete", response);
                }
            },
            error: function (jqXHR) {
                handleAjaxError("user-delete", jqXHR);
            },
        });
    }

    /**
     * Xử lý lỗi AJAX
     */
    function handleAjaxError(formPrefix, jqXHR) {
        // Nếu response có cấu trúc lỗi từ API
        if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
            // Sử dụng hàm xử lý lỗi đã cải tiến
            handleApiErrors(formPrefix, jqXHR.responseJSON);
            return;
        }

        // Xử lý các lỗi không phải từ API
        let errorMessage = "An error occurred while processing your request";

        if (jqXHR.status === 403) {
            errorMessage =
                "Access denied or invalid security token. Please refresh the page.";
            // Nếu token hết hạn, có thể chuyển hướng người dùng đến trang đăng nhập
            if (
                jqXHR.responseJSON &&
                jqXHR.responseJSON.message &&
                jqXHR.responseJSON.message.includes("session expired")
            ) {
                showAlert(
                    "danger",
                    "Your session has expired. Redirecting to login page..."
                );
                setTimeout(function () {
                    window.location.href = `${BASE_URL}/public/login.php`;
                }, 2000);
                return;
            }
        } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
            errorMessage = jqXHR.responseJSON.message;
        }

        // Hiển thị thông báo lỗi chung
        if (formPrefix) {
            $(`#${formPrefix}-errors`).show().text(errorMessage);
        } else {
            showAlert("danger", errorMessage);
        }
    }

    /**
     * Hiển thị thông báo
     */
    function showAlert(type, message) {
        // Xóa thông báo cũ
        $("#user-alerts").empty();

        // Tạo thông báo mới
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade in">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                ${message}
            </div>
        `;

        // Thêm thông báo vào container
        $("#user-alerts").html(alertHTML);

        // Tự động ẩn sau 5 giây
        setTimeout(function () {
            $("#user-alerts .alert").alert("close");
        }, 5000);
    }
})(jQuery);
