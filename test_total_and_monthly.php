<?php

require_once __DIR__ . '/../src/interest_calculator_interface.php';
require_once __DIR__ . '/../src/flat_interest_calculator.php';
require_once __DIR__ . '/../src/reducing_interest_calculator.php';
require_once __DIR__ . '/../src/loan_calculator.php';

echo "--- FLAT METHOD ---" . PHP_EOL;
$flatLoan = new LoanCalculator(10000, 10, 2, new FlatInterestCalculator());
echo "Interest: GH₵" . number_format($flatLoan->calculateInterest(), 2) . PHP_EOL;
echo "Total Repayment: GH₵" . number_format($flatLoan->calculateTotalRepayment(), 2) . PHP_EOL;
echo "Monthly Payment: GH₵" . number_format($flatLoan->calculateMonthlyPayment(), 2) . PHP_EOL;

assert($flatLoan->calculateInterest() === 2000.00);
assert($flatLoan->calculateTotalRepayment() === 12000.00);
assert($flatLoan->calculateMonthlyPayment() === 500.00);

echo PHP_EOL . "--- REDUCING METHOD ---" . PHP_EOL;
$reducingLoan = new LoanCalculator(10000, 10, 2, new ReducingInterestCalculator());
echo "Interest: GH₵" . number_format($reducingLoan->calculateInterest(), 2) . PHP_EOL;
echo "Total Repayment: GH₵" . number_format($reducingLoan->calculateTotalRepayment(), 2) . PHP_EOL;
echo "Monthly Payment: GH₵" . number_format($reducingLoan->calculateMonthlyPayment(), 2) . PHP_EOL;

assert($reducingLoan->calculateMonthlyPayment() > 0);
assert($reducingLoan->calculateTotalRepayment() < $flatLoan->calculateTotalRepayment());

echo PHP_EOL . "✅ All tests passed!" . PHP_EOL;