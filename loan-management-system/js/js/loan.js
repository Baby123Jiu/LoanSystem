/* =========================================
   LOAN JAVASCRIPT
========================================= */


/*
 * API address.
 */
const LOAN_API_URL =
    "http://localhost/loan-system/api";


/*
 * Loan application form.
 */

const loanForm =
    document.getElementById("loanForm");


/*
 * Store the latest calculation.
 */

let latestLoanCalculation = null;


/*
 * Convert duration into months.
 */

function convertToMonths(duration, durationType) {

    const value = Number(duration);

    if (durationType === "years") {
        return value * 12;
    }

    return value;
}


/*
 * Calculate a flat/fixed-rate loan.
 *
 * Formula:
 *
 * Interest =
 * Principal × Annual Rate × Years
 *
 * Total =
 * Principal + Interest
 *
 * Monthly Payment =
 * Total / Number of Months
 */

function calculateFlatLoan(
    principal,
    annualRate,
    months
) {

    const years = months / 12;

    const interest =
        principal *
        (annualRate / 100) *
        years;

    const totalRepayment =
        principal + interest;

    const monthlyPayment =
        totalRepayment / months;


    return {
        principal: principal,
        interest: interest,
        totalRepayment: totalRepayment,
        monthlyPayment: monthlyPayment
    };
}


/*
 * Calculate a reducing-balance loan.
 *
 * This uses the standard amortizing-loan formula:
 *
 * M = P × [r(1+r)^n] / [(1+r)^n - 1]
 *
 * where:
 *
 * P = principal
 * r = monthly interest rate
 * n = number of monthly payments
 */

function calculateReducingLoan(
    principal,
    annualRate,
    months
) {

    const monthlyRate =
        annualRate / 100 / 12;


    let monthlyPayment;


    if (monthlyRate === 0) {

        monthlyPayment =
            principal / months;

    } else {

        const power =
            Math.pow(
                1 + monthlyRate,
                months
            );

        monthlyPayment =
            principal *
            (
                monthlyRate * power
            ) /
            (
                power - 1
            );
    }


    const totalRepayment =
        monthlyPayment * months;

    const interest =
        totalRepayment - principal;


    return {
        principal: principal,
        interest: interest,
        totalRepayment: totalRepayment,
        monthlyPayment: monthlyPayment
    };
}


/*
 * Calculate the loan based on the selected method.
 */

function calculateLoan(
    principal,
    annualRate,
    months,
    method
) {

    if (method === "reducing") {

        return calculateReducingLoan(
            principal,
            annualRate,
            months
        );
    }


    return calculateFlatLoan(
        principal,
        annualRate,
        months
    );
}


/*
 * Display calculation on the page.
 */

function displayLoanCalculation(result) {

    const principalElement =
        document.getElementById("resultPrincipal");

    const interestElement =
        document.getElementById("resultInterest");

    const totalElement =
        document.getElementById("resultTotal");

    const monthlyElement =
        document.getElementById("resultMonthly");


    if (principalElement) {

        principalElement.textContent =
            formatCurrency(result.principal);
    }


    if (interestElement) {

        interestElement.textContent =
            formatCurrency(result.interest);
    }


    if (totalElement) {

        totalElement.textContent =
            formatCurrency(result.totalRepayment);
    }


    if (monthlyElement) {

        monthlyElement.textContent =
            formatCurrency(result.monthlyPayment);
    }
}


/*
 * Display a basic schedule preview.
 */

function displaySchedulePreview(
    monthlyPayment,
    months
) {

    const schedulePreview =
        document.getElementById("schedulePreview");


    if (!schedulePreview) {
        return;
    }


    schedulePreview.innerHTML = `
        <strong>Repayment Schedule</strong>

        <p>
            Estimated monthly payment:
            <strong>${formatCurrency(monthlyPayment)}</strong>
        </p>

        <p>
            Number of payments:
            <strong>${months}</strong>
        </p>
    `;
}


/*
 * Loan form submission.
 */

if (loanForm) {

    loanForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            clearMessage("loanMessage");


            const amount =
                Number(
                    document.getElementById(
                        "loanAmount"
                    ).value
                );


            const duration =
                Number(
                    document.getElementById(
                        "duration"
                    ).value
                );


            const durationType =
                document.getElementById(
                    "durationType"
                ).value;


            const interestRate =
                Number(
                    document.getElementById(
                        "interestRate"
                    ).value
                );


            const method =
                document.getElementById(
                    "repaymentMethod"
                ).value;


            /*
             * Validate values.
             */

            if (amount <= 0) {

                showMessage(
                    "loanMessage",
                    "Loan amount must be greater than zero.",
                    "error"
                );

                return;
            }


            if (duration <= 0) {

                showMessage(
                    "loanMessage",
                    "Loan duration must be greater than zero.",
                    "error"
                );

                return;
            }


            if (interestRate < 0) {

                showMessage(
                    "loanMessage",
                    "Interest rate cannot be negative.",
                    "error"
                );

                return;
            }


            /*
             * Convert years to months if necessary.
             */

            const months =
                convertToMonths(
                    duration,
                    durationType
                );


            /*
             * Calculate the loan.
             *
             * This is currently used to make the frontend
             * immediately show a calculation.
             *
             * The backend should be the final authority
             * once the API is connected.
             */

            latestLoanCalculation =
                calculateLoan(
                    amount,
                    interestRate,
                    months,
                    method
                );


            /*
             * Display the result.
             */

            displayLoanCalculation(
                latestLoanCalculation
            );


            displaySchedulePreview(
                latestLoanCalculation.monthlyPayment,
                months
            );


            /*
             * Enable Submit button.
             */

            const submitButton =
                document.getElementById(
                    "submitLoanButton"
                );


            if (submitButton) {

                submitButton.disabled = false;
            }


            showMessage(
                "loanMessage",
                "Loan calculation completed successfully.",
                "success"
            );


            /*
             * Try to ask the backend for its official
             * calculation.
             */

            try {

                const response = await fetch(
                    `${LOAN_API_URL}/loans/calculate`,
                    {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify({
                            amount: amount,
                            duration: duration,
                            durationType: durationType,
                            interestRate: interestRate,
                            repaymentMethod: method
                        })
                    }
                );


                if (response.ok) {

                    const data =
                        await response.json();


                    /*
                     * If backend sends calculation,
                     * use backend calculation.
                     */

                    if (
                        data &&
                        (
                            data.interest !== undefined ||
                            data.total_repayment !== undefined
                        )
                    ) {

                        const backendResult = {

                            principal:
                                Number(
                                    data.loan_amount ??
                                    data.principal ??
                                    amount
                                ),

                            interest:
                                Number(
                                    data.interest ??
                                    0
                                ),

                            totalRepayment:
                                Number(
                                    data.total_repayment ??
                                    data.totalRepayment ??
                                    0
                                ),

                            monthlyPayment:
                                Number(
                                    data.monthly_payment ??
                                    data.monthlyPayment ??
                                    0
                                )
                        };


                        latestLoanCalculation =
                            backendResult;


                        displayLoanCalculation(
                            backendResult
                        );


                        displaySchedulePreview(
                            backendResult.monthlyPayment,
                            months
                        );
                    }
                }

            } catch (error) {

                /*
                 * The frontend calculation remains visible
                 * if the backend is not running yet.
                 */

                console.info(
                    "Backend calculation unavailable. Showing frontend estimate."
                );
            }

        }
    );

}


/*
 * Submit the actual loan application.
 */

const submitLoanButton =
    document.getElementById(
        "submitLoanButton"
    );


if (submitLoanButton) {

    submitLoanButton.addEventListener(
        "click",
        async function () {

            if (!latestLoanCalculation) {

                showMessage(
                    "loanMessage",
                    "Please calculate the loan first.",
                    "error"
                );

                return;
            }


            const amount =
                Number(
                    document.getElementById(
                        "loanAmount"
                    ).value
                );


            const duration =
                Number(
                    document.getElementById(
                        "duration"
                    ).value
                );


            const durationType =
                document.getElementById(
                    "durationType"
                ).value;


            const interestRate =
                Number(
                    document.getElementById(
                        "interestRate"
                    ).value
                );


            const repaymentMethod =
                document.getElementById(
                    "repaymentMethod"
                ).value;


            const user =
                getCurrentUser();


            if (!user) {

                window.location.href =
                    "login.html";

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
                        `${LOAN_API_URL}/loans`,
                        {
                            method: "POST",

                            headers: headers,

                            body: JSON.stringify({

                                amount: amount,

                                duration: duration,

                                durationType:
                                    durationType,

                                interestRate:
                                    interestRate,

                                repaymentMethod:
                                    repaymentMethod
                            })
                        }
                    );


                const data =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message ||
                        "Loan application failed."
                    );
                }


                alert(
                    "Loan application submitted successfully."
                );


                window.location.href =
                    "dashboard.html";


            } catch (error) {

                console.error(
                    "Loan submission error:",
                    error
                );


                showMessage(
                    "loanMessage",
                    "The loan could not be submitted. Please make sure the backend API is available.",
                    "error"
                );
            }

        }
    );

}


/*
 * Load loan details page.
 */

async function loadLoanDetails() {

    const user =
        getCurrentUser();


    if (!user) {

        window.location.href =
            "login.html";

        return;
    }


    /*
     * The backend should eventually provide the
     * customer's active loan.
     */

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
                `${LOAN_API_URL}/loans/current`,
                {
                    method: "GET",
                    headers: headers
                }
            );


        if (!response.ok) {

            throw new Error(
                "Could not load loan."
            );
        }


        const data =
            await response.json();


        populateLoanDetails(data);


    } catch (error) {

        console.info(
            "Loan details API is not available yet.",
            error
        );
    }
}


/*
 * Put loan information into the HTML.
 */

function populateLoanDetails(loan) {

    const principal =
        Number(
            loan.loan_amount ??
            loan.principal ??
            0
        );


    const total =
        Number(
            loan.total_repayment ??
            loan.totalRepayment ??
            0
        );


    const paid =
        Number(
            loan.amount_paid ??
            loan.amountPaid ??
            0
        );


    const balance =
        Number(
            loan.remaining_balance ??
            loan.balance ??
            total - paid
        );


    const values = {

        detailPrincipal:
            formatCurrency(principal),

        detailTotal:
            formatCurrency(total),

        detailPaid:
            formatCurrency(paid),

        detailBalance:
            formatCurrency(balance),

        detailLoanAmount:
            formatCurrency(principal),

        detailInterestRate:
            `${loan.interest_rate ?? 0}%`,

        detailDuration:
            `${loan.duration ?? "-"} ${loan.duration_type ?? ""}`,

        detailMethod:
            loan.repayment_method ??
            "-",

        detailMonthly:
            formatCurrency(
                loan.monthly_payment ?? 0
            ),

        detailNextPayment:
            loan.next_payment_date ??
            "-"
    };


    Object.entries(values).forEach(
        ([id, value]) => {

            const element =
                document.getElementById(id);

            if (element) {
                element.textContent = value;
            }
        }
    );


    const status =
        document.getElementById(
            "detailStatus"
        );


    if (status && loan.status) {

        status.textContent =
            loan.status;
    }


    /*
     * Load schedule if available.
     */

    if (loan.schedule) {

        populateSchedule(
            loan.schedule
        );
    }
}


/*
 * Display repayment schedule.
 */

function populateSchedule(schedule) {

    const tableBody =
        document.getElementById(
            "scheduleTableBody"
        );


    if (!tableBody) {
        return;
    }


    tableBody.innerHTML = "";


    schedule.forEach(
        function (payment, index) {

            const row =
                document.createElement("tr");


            row.innerHTML = `

                <td>${index + 1}</td>

                <td>
                    ${payment.due_date ?? "-"}
                </td>

                <td>
                    ${formatCurrency(
                        payment.amount ?? 0
                    )}
                </td>

                <td>
                    ${formatCurrency(
                        payment.principal ?? 0
                    )}
                </td>

                <td>
                    ${formatCurrency(
                        payment.interest ?? 0
                    )}
                </td>

                <td>
                    <span class="status status-${String(
                        payment.status ?? "pending"
                    ).toLowerCase()}">
                        ${payment.status ?? "Pending"}
                    </span>
                </td>

            `;


            tableBody.appendChild(row);
        }
    );
}


/*
 * Automatically load details when the page
 * contains the schedule table.
 */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        if (
            document.getElementById(
                "scheduleTableBody"
            )
        ) {

            loadLoanDetails();
        }
    }
);