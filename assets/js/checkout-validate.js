document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("checkout-form");
    const inputs = form.querySelectorAll(".input");
    const submitButton = form.querySelector(".order-submit");

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

        if (!field) return true;

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

            case "email":
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    showError(fieldName, "Please enter a valid email address");
                    return false;
                }
                break;

            case "tel":
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

            case "city":
                if (value.length < 2) {
                    showError(
                        fieldName,
                        "City name must be at least 2 characters"
                    );
                    return false;
                }
                break;

            case "country":
                if (value.length < 2) {
                    showError(
                        fieldName,
                        "Country name must be at least 2 characters"
                    );
                    return false;
                }
                break;

            case "zip-code":
                if (value !== "") {
                    const zipPattern = /^\d{5}(-\d{4})?$/;
                    if (!zipPattern.test(value)) {
                        showError(
                            fieldName,
                            "Please enter a valid ZIP code (e.g., 12345 or 12345-6789)"
                        );
                        return false;
                    }
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
        if (!input) return;

        clearError(input);
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.style.color = "#D10024";
        errorDiv.style.fontSize = "12px";
        errorDiv.style.marginTop = "5px";
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
        input.style.borderColor = "#D10024";
    }

    function clearError(input) {
        if (!input) return;
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
        
        .input-error {
            border-color: #D10024 !important;
        }
    `;
    document.head.appendChild(styleEl);
});
