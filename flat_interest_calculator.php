<?php

require_once __DIR__ . '/interest_calculator_interface.php';

/**
 * FlatInterestCalculator
 *
 * Calculates interest using the flat method: interest is based on the
 * original principal for the whole loan term and never changes.
 */
class FlatInterestCalculator implements InterestCalculatorInterface
{
    public function calculateInterest(float $principal, float $ratePercent, float $durationYears): float
    {
        $rateDecimal = $ratePercent / 100;

        $interest = $principal * $rateDecimal * $durationYears;

        return round($interest, 2);
    }

    public function calculateMonthlyPayment(float $principal, float $ratePercent, float $durationYears): float
    {
        $interest = $this->calculateInterest($principal, $ratePercent, $durationYears);
        $totalRepayment = $principal + $interest;
        $months = $durationYears * 12;

        return round($totalRepayment / $months, 2);
    }
        public function calculateRemainingBalance(
        float $principal,
        float $ratePercent,
        float $durationYears,
        int $monthsPaid
    ): float {
        $totalRepayment = $principal + $this->calculateInterest($principal, $ratePercent, $durationYears);
        $monthlyPayment = $this->calculateMonthlyPayment($principal, $ratePercent, $durationYears);

        $remaining = $totalRepayment - ($monthlyPayment * $monthsPaid);

        return round(max($remaining, 0), 2);
    }
}
