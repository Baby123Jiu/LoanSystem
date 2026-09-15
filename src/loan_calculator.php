<?php

require_once __DIR__ . '/interest_calculator_interface.php';

/**
 * LoanCalculator
 *
 * Holds a loan's details and links to whichever interest calculator
 * (flat or reducing) it was given, then forwards calculations to it.
 */
class LoanCalculator
{
    protected float $principal;
    protected float $ratePercent;
    protected float $durationYears;
    protected InterestCalculatorInterface $calculator;

    public function __construct(
        float $principal,
        float $ratePercent,
        float $durationYears,
        InterestCalculatorInterface $calculator
    ) {
        if ($principal <= 0) {
            throw new InvalidArgumentException('Principal must be greater than zero.');
        }
        if ($ratePercent < 0) {
            throw new InvalidArgumentException('Interest rate cannot be negative.');
        }
        if ($durationYears <= 0) {
            throw new InvalidArgumentException('Duration must be greater than zero.');
        }

        $this->principal     = $principal;
        $this->ratePercent   = $ratePercent;
        $this->durationYears = $durationYears;
        $this->calculator    = $calculator;
    }

    public function calculateInterest(): float
    {
        return $this->calculator->calculateInterest(
            $this->principal,
            $this->ratePercent,
            $this->durationYears
        );
    }

    public function calculateTotalRepayment(): float
    {
        $interest = $this->calculateInterest();

        $totalRepayment = $this->principal + $interest;

        return round($totalRepayment, 2);
    }

    public function calculateMonthlyPayment(): float
    {
        return $this->calculator->calculateMonthlyPayment(
            $this->principal,
            $this->ratePercent,
            $this->durationYears
        );
    }

    public function calculateRemainingBalance(int $monthsPaid): float
    {
        return $this->calculator->calculateRemainingBalance(
            $this->principal,
            $this->ratePercent,
            $this->durationYears,
            $monthsPaid
        );
    }
}