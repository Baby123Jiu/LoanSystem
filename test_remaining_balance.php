<?php

require_once __DIR__ . '/../src/interest_calculator_interface.php';
require_once __DIR__ . '/../src/flat_interest_calculator.php';
require_once __DIR__ . '/../src/reducing_interest_calculator.php';
require_once __DIR__ . '/../src/loan_calculator.php';

echo "--- FLAT METHOD (after 12 of 24 months paid) ---" . PHP_EOL;
$flatLoan = new LoanCalculator(10000, 10, 2, new FlatInterestCalculator());
echo "Remaining Balance: GH₵" . number_format($flatLoan->calculateRemainingBalance(12), 2) . PHP_EOL;

echo PHP_EOL . "--- REDUCING METHOD (after 12 of 24 months paid) ---" . PHP_EOL;
$reducingLoan = new LoanCalculator(10000, 10, 2, new ReducingInterestCalculator());
echo "Remaining Balance: GH₵" . number_format($reducingLoan->calculateRemainingBalance(12), 2) . PHP_EOL;

echo PHP_EOL . "--- Fully paid (24 of 24 months) ---" . PHP_EOL;
echo "Remaining Balance: GH₵" . number_format($reducingLoan->calculateRemainingBalance(24), 2) . PHP_EOL;