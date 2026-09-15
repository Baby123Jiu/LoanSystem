/* =========================================
   AUTHENTICATION JAVASCRIPT
========================================= */


/*
 * Backend API address.
 *
 * Your backend teammate can later give you
 * the exact API URL.
 */
const API_BASE_URL = "http://localhost/loan-system/api";


/*
 * REGISTER
 */

const registerForm =
    document.getElementById("registerForm");


if (registerForm) {

    registerForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            const fullName =
                document.getElementById("fullName").value.trim();

            const phone =
                document.getElementById("phone").value.trim();

            const email =
                document.getElementById("email").value.trim();

            const password =
                document.getElementById("password").value;

            const confirmPassword =
                document.getElementById("confirmPassword").value;


            clearMessage("registerMessage");


            /*
             * Check that passwords match.
             */

            if (password !== confirmPassword) {

                showMessage(
                    "registerMessage",
                    "Passwords do not match.",
                    "error"
                );

                return;
            }


            /*
             * Basic password validation.
             */

            if (password.length < 6) {

                showMessage(
                    "registerMessage",
                    "Password must contain at least 6 characters.",
                    "error"
                );

                return;
            }


            /*
             * Customer data.
             */

            const customerData = {
                fullName: fullName,
                phone: phone,
                email: email,
                password: password
            };


            /*
             * Send registration data to backend.
             */

            try {

                const response = await fetch(
                    `${API_BASE_URL}/customers`,
                    {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify(customerData)
                    }
                );


                const data = await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message ||
                        "Registration failed."
                    );
                }


                showMessage(
                    "registerMessage",
                    "Registration successful. Redirecting to login...",
                    "success"
                );


                setTimeout(function () {

                    window.location.href =
                        "login.html";

                }, 1500);


            } catch (error) {

                /*
                 * This message is useful while the backend
                 * API has not yet been connected.
                 */

                console.error(
                    "Registration error:",
                    error
                );

                showMessage(
                    "registerMessage",
                    "Unable to register. Please make sure the backend API is running.",
                    "error"
                );
            }

        }
    );

}


/*
 * LOGIN
 */

const loginForm =
    document.getElementById("loginForm");


if (loginForm) {

    loginForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            const email =
                document.getElementById("email").value.trim();

            const password =
                document.getElementById("password").value;


            clearMessage("loginMessage");


            try {

                const response = await fetch(
                    `${API_BASE_URL}/login`,
                    {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify({
                            email: email,
                            password: password
                        })
                    }
                );


                const data = await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message ||
                        "Login failed."
                    );
                }


                /*
                 * Save the logged-in customer.
                 */

                if (data.user) {

                    saveCurrentUser(data.user);

                } else {

                    saveCurrentUser({
                        email: email
                    });
                }


                /*
                 * Save token if the backend provides one.
                 */

                if (data.token) {

                    localStorage.setItem(
                        "loanToken",
                        data.token
                    );
                }


                showMessage(
                    "loginMessage",
                    "Login successful. Redirecting...",
                    "success"
                );


                setTimeout(function () {

                    window.location.href =
                        "dashboard.html";

                }, 1000);


            } catch (error) {

                console.error(
                    "Login error:",
                    error
                );

                showMessage(
                    "loginMessage",
                    "Unable to login. Please check your email and password.",
                    "error"
                );
            }

        }
    );

}