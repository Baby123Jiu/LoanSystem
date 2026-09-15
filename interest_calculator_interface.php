<?php

/**
 * InterestCalculatorInterface
 *
 * A rule book that says: any class calculating loan interest must have
 * a calculateInterest() method that takes principal, rate, and duration,
 * and returns the interest amount.
 */
interface InterestCalculatorInterface
{
    public function calculateInterest(float $principal, float $ratePercent, float $durationYears): float;

      public function calculateMonthlyPayment(float $principal, float $ratePercent, float $durationYears): float;

       public function calculateRemainingBalance(
        float $principal,
        float $ratePercent,
        float $durationYears,
        int $monthsPaid
    ): float;
}
