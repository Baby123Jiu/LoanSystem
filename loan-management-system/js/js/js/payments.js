/* =========================================
   PAYMENTS JAVASCRIPT
========================================= */


const PAYMENT_API_URL =
    "http://localhost/loan-system/api";


/*
 * Load current loan balance.
 */

async function loadPaymentBalance() {

    try {

        const token =
            localStorage.getItem(
                "loanToken"
            );


        const headers = {};


        if (token) {

            headers.Authorization =
                `Bearer ${token}`;
        }


        const response =
            await fetch(
                `${PAYMENT_API_URL}/loans/current`,
                {
                    method: "GET",
                    headers: headers
                }
            );


        if (!response.ok) {

            throw new Error(
                "Unable to load balance."
            );
        }


        const loan =
            await response.json();


        const balance =
            Number(
                loan.remaining_balance ??
                loan.balance ??
                0
            );


        const balanceElement =
            document.getElementById(
                "paymentBalance"
            );


        if (balanceElement) {

            balanceElement.textContent =
                formatCurrency(balance);
        }


    } catch (error) {

        console.info(
            "Payment balance API is not available yet.",
            error
        );
    }
}


/*
 * Submit payment.
 */

const paymentForm =
    document.getElementById(
        "paymentForm"
    );


if (paymentForm) {

    paymentForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            clearMessage(
                "paymentMessage"
            );


            const amount =
                Number(
                    document.getElementById(
                        "paymentAmount"
                    ).value
                );


            const reference =
                document.getElementById(
                    "paymentReference"
                ).value.trim();


            if (amount <= 0) {

                showMessage(
                    "paymentMessage",
                    "Payment amount must be greater than zero.",
                    "error"
                );

                return;
            }


            try {

                const token =
                    localStorage.getItem(
                        "loanToken"
                    );


                const headers = {
                    "Content-Type":
                        "application/json"
                };


                if (token) {

                    headers.Authorization =
                        `Bearer ${token}`;
                }


                const response =
                    await fetch(
                        `${PAYMENT_API_URL}/payments`,
                        {
                            method: "POST",

                            headers: headers,

                            body: JSON.stringify({

                                amount: amount,

                                reference:
                                    reference
                            })
                        }
                    );


                const data =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message ||
                        "Payment failed."
                    );
                }


                showMessage(
                    "paymentMessage",
                    "Payment recorded successfully.",
                    "success"
                );


                paymentForm.reset();


                /*
                 * Update balance and history.
                 */

                await loadPaymentBalance();

                await loadPaymentHistory();


            } catch (error) {

                console.error(
                    "Payment error:",
                    error
                );


                showMessage(
                    "paymentMessage",
                    "Payment could not be recorded. Please make sure the backend API is available.",
                    "error"
                );
            }

        }
    );
}


/*
 * Load payment history.
 */

async function loadPaymentHistory() {

    const tableBody =
        document.getElementById(
            "paymentTableBody"
        );


    if (!tableBody) {
        return;
    }


    try {

        const token =
            localStorage.getItem(
                "loanToken"
            );


        const headers = {};


        if (token) {

            headers.Authorization =
                `Bearer ${token}`;
        }


        const response =
            await fetch(
                `${PAYMENT_API_URL}/loans/current/payments`,
                {
                    method: "GET",
                    headers: headers
                }
            );


        if (!response.ok) {

            throw new Error(
                "Unable to load payments."
            );
        }


        const data =
            await response.json();


        const payments =
            Array.isArray(data)
                ? data
                : data.payments || [];


        if (payments.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="table-empty">
                        No payment history available.
                    </td>
                </tr>
            `;

            return;
        }


        tableBody.innerHTML = "";


        payments.forEach(
            function (payment) {

                const row =
                    document.createElement("tr");


                row.innerHTML = `

                    <td>
                        ${payment.payment_date ?? "-"}
                    </td>

                    <td>
                        ${payment.reference ?? "-"}
                    </td>

                    <td>
                        ${formatCurrency(
                            payment.amount ?? 0
                        )}
                    </td>

                    <td>
                        ${formatCurrency(
                            payment.balance_after_payment ??
                            payment.remaining_balance ??
                            0
                        )}
                    </td>

                `;


                tableBody.appendChild(row);
            }
        );


    } catch (error) {

        console.info(
            "Payment history API is not available yet.",
            error
        );
    }
}


/*
 * Load payment page data.
 */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        if (
            document.getElementById(
                "paymentBalance"
            )
        ) {

            loadPaymentBalance();
        }


        if (
            document.getElementById(
                "paymentTableBody"
            )
        ) {

            loadPaymentHistory();
        }

    }
);