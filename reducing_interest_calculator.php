<?php
require_once __DIR__ . '/interest_calculator_interface.php';

/**
 * ReducingInterestCalculator
 *
 * Calculates interest using the reducing balance method: interest is
 * charged only on the amount still owed, so it shrinks as the loan
 * is paid down.
 */
class ReducingInterestCalculator implements InterestCalculatorInterface
{
    public function calculateInterest(float $principal, float $ratePercent, float $durationYears): float
    {
        $months = $durationYears * 12;

        if ($ratePercent == 0) {
            return 0.0;
        }

        $monthlyRate = ($ratePercent / 100) / 12;

        $monthlyPayment = $principal * $monthlyRate * (1 + $monthlyRate) ** $months
            / ((1 + $monthlyRate) ** $months - 1);

        $totalRepayment = $monthlyPayment * $months;
        $totalInterest = $totalRepayment - $principal;

        return round($totalInterest, 2);
    }

    public function calculateMonthlyPayment(float $principal, float $ratePercent, float $durationYears): float
    {
        $months = $durationYears * 12;

        if ($ratePercent == 0) {
            return round($principal / $months, 2);
        }

        $monthlyRate = ($ratePercent / 100) / 12;

        $monthlyPayment = $principal * $monthlyRate * (1 + $monthlyRate) ** $months
            / ((1 + $monthlyRate) ** $months - 1);

        return round($monthlyPayment, 2);
    }
        public function calculateRemainingBalance(
        float $principal,
        float $ratePercent,
        float $durationYears,
        int $monthsPaid
    ): float {
        $months = $durationYears * 12;

        if ($ratePercent == 0) {
            $remaining = $principal - ($principal / $months) * $monthsPaid;
            return round(max($remaining, 0), 2);
        }

        $monthlyRate = ($ratePercent / 100) / 12;

        $remaining = $principal
            * ((1 + $monthlyRate) ** $months - (1 + $monthlyRate) ** $monthsPaid)
            / ((1 + $monthlyRate) ** $months - 1);

        return round(max($remaining, 0), 2);
    }
}
