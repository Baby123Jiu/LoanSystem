/* =========================================
   MAIN JAVASCRIPT
========================================= */


/*
 * Format a number as Ghanaian Cedis.
 */
function formatCurrency(amount) {

    const number = Number(amount) || 0;

    return new Intl.NumberFormat("en-GH", {
        style: "currency",
        currency: "GHS",
        minimumFractionDigits: 2
    }).format(number);
}


/*
 * Display a message inside a message element.
 */
function showMessage(elementId, message, type = "error") {

    const element = document.getElementById(elementId);

    if (!element) {
        return;
    }

    element.textContent = message;

    element.className = `form-message ${type}`;
}


/*
 * Remove a message.
 */
function clearMessage(elementId) {

    const element = document.getElementById(elementId);

    if (!element) {
        return;
    }

    element.textContent = "";

    element.className = "form-message";
}


/*
 * Get saved customer information.
 */
function getCurrentUser() {

    const user = localStorage.getItem("loanUser");

    if (!user) {
        return null;
    }

    try {
        return JSON.parse(user);
    } catch (error) {

        console.error(
            "Could not read saved customer information.",
            error
        );

        return null;
    }
}


/*
 * Save customer information.
 */
function saveCurrentUser(user) {

    localStorage.setItem(
        "loanUser",
        JSON.stringify(user)
    );
}


/*
 * Remove customer information.
 */
function clearCurrentUser() {

    localStorage.removeItem("loanUser");

    localStorage.removeItem("loanToken");
}


/*
 * Protect pages that require login.
 */
function requireLogin() {

    const user = getCurrentUser();

    if (!user) {
        window.location.href = "login.html";
    }

    return user;
}


/*
 * Logout customer.
 */
function logout() {

    clearCurrentUser();

    window.location.href = "login.html";
}


/*
 * Connect logout buttons.
 */
document.addEventListener("DOMContentLoaded", function () {

    const logoutButton =
        document.getElementById("logoutButton");

    if (logoutButton) {

        logoutButton.addEventListener(
            "click",
            logout
        );
    }


    const user = getCurrentUser();

    const customerName =
        document.getElementById("customerName");

    if (customerName && user) {

        customerName.textContent =
            user.fullName || "Customer";
    }

});