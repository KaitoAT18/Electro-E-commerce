document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("form-register");
    const inputs = form.querySelectorAll(".input");
    const submitButton = form.querySelector('button[type="submit"]');

    // Disable submit button initially
    updateSubmitButtonState();

    // Validate on form submit
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (validateForm()) {
            this.submit();
        }
    });

    // Validate each input when user finishes typing
    inputs.forEach((input) => {
        input.addEventListener("blur", function () {
            validateField(this.name || this.id);
            updateSubmitButtonState();
        });

        // Real-time validation as user types
        input.addEventListener("input", function () {
            validateField(this.name || this.id);
            updateSubmitButtonState();
        });
    });

    function validateField(fieldName) {
        const field =
            document.querySelector(`input[name="${fieldName}"]`) ||
            document.querySelector(`#${fieldName}`);

        if (!field) return;

        const value = field.value.trim();
        clearError(field);

        switch (fieldName) {
            case "first-name":
                if (value.length < 2) {
                    showError(
                        fieldName,
                        "First name must be at least 2 characters"
                    );
                    return false;
                }
                break;

            case "last-name":
                if (value.length < 2) {
                    showError(
                        fieldName,
                        "Last name must be at least 2 characters"
                    );
                    return false;
                }
                break;

            case "username":
                const usernamePattern = /^[a-z0-9]+$/;
                if (!usernamePattern.test(value)) {
                    showError(
                        fieldName,
                        "Username can only contain lowercase letters and numbers"
                    );
                    return false;
                }
                if (value.length > 15 || value.length < 5) {
                    showError(
                        fieldName,
                        "Username cannot be more than 15 characters and less than 5 characters"
                    );
                    return false;
                }
                break;

            case "email":
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    showError(fieldName, "Please enter a valid email address");
                    return false;
                }
                break;

            case "phone":
                if (value !== "") {
                    const phonePattern = /^\d{10}$/;
                    if (!phonePattern.test(value)) {
                        showError(
                            fieldName,
                            "Please enter a valid 10-digit phone number"
                        );
                        return false;
                    }
                }
                break;

            case "address":
                if (value.length < 5) {
                    showError(
                        fieldName,
                        "Please enter a valid address (at least 5 characters)"
                    );
                    return false;
                }
                break;

            case "password":
                if (value.length < 6) {
                    showError(
                        fieldName,
                        "Password must be at least 6 characters long"
                    );
                    return false;
                }
                // Check confirm password match when password changes
                const confirmPassword = document.querySelector(
                    'input[name="confirm-password"]'
                );
                if (confirmPassword.value) {
                    validateField("confirm-password");
                }
                break;

            case "confirm-password":
                const password = document.querySelector(
                    'input[name="password"]'
                ).value;
                if (value !== password) {
                    showError(fieldName, "Passwords do not match");
                    return false;
                }
                break;

            case "terms":
                if (!field.checked) {
                    showError(
                        fieldName,
                        "You must accept the terms and conditions"
                    );
                    return false;
                }
                break;
        }
        return true;
    }

    function validateForm() {
        let isValid = true;

        // Clear all previous errors
        clearErrors();

        // Validate all fields
        inputs.forEach((input) => {
            const fieldIsValid = validateField(input.name || input.id);
            isValid = isValid && fieldIsValid;
        });

        return isValid;
    }

    function updateSubmitButtonState() {
        // Check all required inputs
        let allFieldsValid = true;
        const requiredInputs = form.querySelectorAll("[required]");

        // First check if all required fields have values
        let allFieldsFilled = true;
        requiredInputs.forEach((input) => {
            if (input.type === "checkbox" && !input.checked) {
                allFieldsFilled = false;
            } else if (input.type !== "checkbox" && !input.value.trim()) {
                allFieldsFilled = false;
            }
        });

        // Then check if there are any validation errors
        const errors = document.querySelectorAll(".error-message");
        if (errors.length > 0 || !allFieldsFilled) {
            allFieldsValid = false;
        }

        // Update button state
        if (allFieldsValid) {
            submitButton.disabled = false;
            submitButton.classList.remove("disabled-btn");
        } else {
            submitButton.disabled = true;
            submitButton.classList.add("disabled-btn");
        }
    }

    function showError(fieldName, message) {
        const input =
            document.querySelector(`input[name="${fieldName}"]`) ||
            document.querySelector(`#${fieldName}`);
        clearError(input);
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.style.color = "red";
        errorDiv.style.fontSize = "12px";
        errorDiv.style.marginTop = "5px";
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
        input.style.borderColor = "red";
    }

    function clearError(input) {
        const parent = input.parentNode;
        const error = parent.querySelector(".error-message");
        if (error) {
            error.remove();
        }
        input.style.borderColor = "";
    }

    function clearErrors() {
        const errorMessages = document.querySelectorAll(".error-message");
        errorMessages.forEach((error) => error.remove());
        inputs.forEach((input) => (input.style.borderColor = ""));
    }

    // Add styles for disabled button
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
    `;
    document.head.appendChild(styleEl);
});
