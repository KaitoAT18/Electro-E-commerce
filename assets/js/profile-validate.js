/**
 * Profile form validation
 *
 * File này chỉ chịu trách nhiệm xác thực dữ liệu form mà không tương tác với API
 */
(function () {
    "use strict";

    // Khởi tạo biến toàn cục để lưu trạng thái hợp lệ của các form
    window.profileFormValidator = {
        isProfileFormValid: false,
        isPasswordFormValid: false,

        // Phương thức để kiểm tra form
        validateProfileForm: function (form) {
            return validateFormByType(form, "profile");
        },
        validatePasswordForm: function (form) {
            return validateFormByType(form, "password");
        },
    };

    document.addEventListener("DOMContentLoaded", function () {
        // Lấy tất cả các form cần validation
        const profileForm = document.querySelector("#profile form");
        const passwordForm = document.querySelector("#settings form");

        // Thiết lập validation cho từng form
        if (profileForm) setupProfileFormValidation(profileForm);
        if (passwordForm) setupPasswordFormValidation(passwordForm);

        // Thêm CSS cho các thông báo lỗi và nút disable
        addCustomStyles();
    });

    /**
     * Thiết lập validation cho form thông tin cá nhân
     */
    function setupProfileFormValidation(form) {
        const inputs = form.querySelectorAll(".input");
        const submitButton = form.querySelector('button[type="submit"]');

        // Trạng thái khởi tạo
        updateSubmitButtonState(form, "profile");

        // Validate khi người dùng nhập xong
        inputs.forEach((input) => {
            // Khi focus ra khỏi trường input
            input.addEventListener("blur", function () {
                validateField(this.name || this.id, "profile");
                updateSubmitButtonState(form, "profile");
            });

            // Validate theo thời gian thực khi đang nhập
            input.addEventListener("input", function () {
                validateField(this.name || this.id, "profile");
                updateSubmitButtonState(form, "profile");
            });
        });
    }

    /**
     * Thiết lập validation cho form đổi mật khẩu
     */
    function setupPasswordFormValidation(form) {
        const inputs = form.querySelectorAll(".input");
        const submitButton = form.querySelector('button[type="submit"]');

        // Trạng thái khởi tạo
        updateSubmitButtonState(form, "password");

        // Validate khi người dùng nhập xong
        inputs.forEach((input) => {
            // Khi focus ra khỏi trường input
            input.addEventListener("blur", function () {
                validateField(this.name || this.id, "password");
                updateSubmitButtonState(form, "password");
            });

            // Validate theo thời gian thực khi đang nhập
            input.addEventListener("input", function () {
                validateField(this.name || this.id, "password");
                updateSubmitButtonState(form, "password");
            });
        });
    }

    /**
     * Validate form dựa trên loại form
     */
    function validateFormByType(form, formType) {
        let isValid = true;

        // Xóa hết thông báo lỗi trước đó
        clearErrors(form);

        // Validate từng trường
        const inputs = form.querySelectorAll(".input");
        inputs.forEach((input) => {
            const fieldIsValid = validateField(
                input.name || input.id,
                formType
            );
            isValid = isValid && fieldIsValid;
        });

        // Cập nhật biến toàn cục tương ứng
        if (formType === "profile") {
            window.profileFormValidator.isProfileFormValid = isValid;
        } else if (formType === "password") {
            window.profileFormValidator.isPasswordFormValid = isValid;
        }

        // Cập nhật trạng thái nút submit
        updateSubmitButtonState(form, formType);

        return isValid;
    }

    /**
     * Validate từng trường dữ liệu dựa trên tên trường
     */
    function validateField(fieldName, formType) {
        // Tìm phần tử field trong DOM
        const field =
            document.querySelector(`input[name="${fieldName}"]`) ||
            document.querySelector(`#${fieldName}`);

        if (!field) return true;

        const value = field.value.trim();
        clearErrorForField(field);

        // Logic validation chung cho cả hai loại form
        switch (fieldName) {
            case "first-name":
                if (value.length < 2) {
                    showErrorForField(
                        field,
                        "First name must be at least 2 characters"
                    );
                    return false;
                }
                break;

            case "last-name":
                if (value.length < 2) {
                    showErrorForField(
                        field,
                        "Last name must be at least 2 characters"
                    );
                    return false;
                }
                break;

            case "email":
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    showErrorForField(
                        field,
                        "Please enter a valid email address"
                    );
                    return false;
                }
                break;

            case "phone":
                if (value !== "") {
                    const phonePattern = /^\d{10}$/;
                    if (!phonePattern.test(value)) {
                        showErrorForField(
                            field,
                            "Please enter a valid 10-digit phone number"
                        );
                        return false;
                    }
                }
                break;

            case "address":
                if (value.length < 5) {
                    showErrorForField(
                        field,
                        "Please enter a valid address (at least 5 characters)"
                    );
                    return false;
                }
                break;
        }

        // Validation riêng cho form mật khẩu
        if (formType === "password") {
            switch (fieldName) {
                case "current-password":
                    if (value.length < 6) {
                        showErrorForField(
                            field,
                            "Please enter your current password"
                        );
                        return false;
                    }
                    break;

                case "new-password":
                    if (value.length < 6) {
                        showErrorForField(
                            field,
                            "New password must be at least 6 characters long"
                        );
                        return false;
                    }
                    // Kiểm tra confirm password khi thay đổi password
                    const confirmPassword = document.querySelector(
                        'input[name="confirm-password"]'
                    );
                    if (confirmPassword && confirmPassword.value) {
                        validateField("confirm-password", formType);
                    }
                    break;

                case "confirm-password":
                    const password = document.querySelector(
                        'input[name="new-password"]'
                    ).value;
                    if (value !== password) {
                        showErrorForField(field, "Passwords do not match");
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Cập nhật trạng thái nút submit dựa trên độ hợp lệ của form
     */
    function updateSubmitButtonState(form, formType) {
        // Kiểm tra tất cả các input
        let allFieldsValid = true;
        const submitButton = form.querySelector('button[type="submit"]');
        const inputs = form.querySelectorAll(".input");

        // Kiểm tra xem tất cả các trường required có được điền đầy đủ không
        let allFieldsFilled = true;
        inputs.forEach((input) => {
            if (input.hasAttribute("required") && !input.value.trim()) {
                allFieldsFilled = false;
            }
        });

        // Kiểm tra xem có lỗi validation nào không
        const errors = form.querySelectorAll(".error-message");
        if (errors.length > 0 || !allFieldsFilled) {
            allFieldsValid = false;
        }

        // Cập nhật trạng thái nút submit
        if (allFieldsValid) {
            submitButton.disabled = false;
            submitButton.classList.remove("disabled-btn");
        } else {
            submitButton.disabled = true;
            submitButton.classList.add("disabled-btn");
        }

        // Cập nhật biến toàn cục
        if (formType === "profile") {
            window.profileFormValidator.isProfileFormValid = allFieldsValid;
        } else if (formType === "password") {
            window.profileFormValidator.isPasswordFormValid = allFieldsValid;
        }
    }

    /**
     * Hiển thị thông báo lỗi cho một trường
     */
    function showErrorForField(input, message) {
        clearErrorForField(input);
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.style.color = "red";
        errorDiv.style.fontSize = "12px";
        errorDiv.style.marginTop = "5px";
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
        input.style.borderColor = "red";
    }

    /**
     * Xóa thông báo lỗi cho một trường
     */
    function clearErrorForField(input) {
        const parent = input.parentNode;
        const error = parent.querySelector(".error-message");
        if (error) {
            error.remove();
        }
        input.style.borderColor = "";
    }

    /**
     * Xóa tất cả thông báo lỗi trong một form
     */
    function clearErrors(form) {
        const errorMessages = form.querySelectorAll(".error-message");
        errorMessages.forEach((error) => error.remove());
        const inputs = form.querySelectorAll(".input");
        inputs.forEach((input) => (input.style.borderColor = ""));
    }

    /**
     * Thêm CSS cho các style custom
     */
    function addCustomStyles() {
        const styleEl = document.createElement("style");
        styleEl.textContent = `
            .disabled-btn {
                opacity: 0.6 !important;
                cursor: not-allowed !important;
                background-color: #999999 !important;
                border-color: #888888 !important;
                color: #f8f8f8 !important;
                transition: all 0.3s ease;
            }
            
            button:not(.disabled-btn) {
                transition: all 0.3s ease;
            }
            
            .error-message {
                color: red;
                font-size: 12px;
                margin-top: 5px;
            }
        `;
        document.head.appendChild(styleEl);
    }
})();
