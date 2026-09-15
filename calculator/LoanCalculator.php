<?php

class LoanCalculator
{
    private function periodsPerYear($frequency)
    {
        $frequency = strtolower($frequency);

        $map = [
            "monthly" => 12,
            "quarterly" => 4,
            "semi-annually" => 2,
            "annually" => 1
        ];

        if (!isset($map[$frequency])) {
            throw new InvalidArgumentException("Unknown frequency: $frequency");
        }

        return $map[$frequency];
    }

    private function validateInputs($amount, $rate, $years)
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Amount must be greater than zero.");
        }

        if ($rate < 0) {
            throw new InvalidArgumentException("Rate cannot be negative.");
        }

        if ($years <= 0) {
            throw new InvalidArgumentException("Years must be greater than zero.");
        }
    }

    public function calculateFlat($amount, $rate, $years, $frequency)
    {
        $this->validateInputs($amount, $rate, $years);

        $rateDecimal = $rate / 100;
        $interest = $amount * $rateDecimal * $years;
        $totalRepayment = $amount + $interest;

        $periodsPerYear = $this->periodsPerYear($frequency);
        $numberOfPayments = round($years * $periodsPerYear);
        $paymentAmount = $totalRepayment / $numberOfPayments;

        return [
            "principal" => round($amount, 2),
            "rate" => $rate,
            "years" => $years,
            "frequency" => $frequency,
            "interest" => round($interest, 2),
            "totalRepayment" => round($totalRepayment, 2),
            "numberOfPayments" => $numberOfPayments,
            "paymentAmount" => round($paymentAmount, 2)
        ];
    }

    public function calculateReducing($amount, $rate, $years, $frequency)
    {
        $this->validateInputs($amount, $rate, $years);

        $periodsPerYear = $this->periodsPerYear($frequency);
        $numberOfPayments = round($years * $periodsPerYear);

        if ($rate == 0) {
            $paymentAmount = $amount / $numberOfPayments;
            $totalRepayment = $amount;
            $interest = 0;
        } else {
            $periodicRate = ($rate / 100) / $periodsPerYear;

            $paymentAmount = $amount * $periodicRate * (1 + $periodicRate) ** $numberOfPayments
                / ((1 + $periodicRate) ** $numberOfPayments - 1);

            $totalRepayment = $paymentAmount * $numberOfPayments;
            $interest = $totalRepayment - $amount;
        }

        return [
            "principal" => round($amount, 2),
            "rate" => $rate,
            "years" => $years,
            "frequency" => $frequency,
            "interest" => round($interest, 2),
            "totalRepayment" => round($totalRepayment, 2),
            "numberOfPayments" => $numberOfPayments,
            "paymentAmount" => round($paymentAmount, 2)
        ];
    }

    public function calculateRemainingBalance($amount, $rate, $years, $frequency, $method, $periodsPaid)
    {
        $periodsPerYear = $this->periodsPerYear($frequency);
        $numberOfPayments = round($years * $periodsPerYear);

        if (strtolower($method) === "flat") {
            $result = $this->calculateFlat($amount, $rate, $years, $frequency);
            $remaining = $result["totalRepayment"] - ($result["paymentAmount"] * $periodsPaid);

            return round(max($remaining, 0), 2);
        }

        if ($rate == 0) {
            $remaining = $amount - ($amount / $numberOfPayments) * $periodsPaid;

            return round(max($remaining, 0), 2);
        }

        $periodicRate = ($rate / 100) / $periodsPerYear;

        $remaining = $amount
            * ((1 + $periodicRate) ** $numberOfPayments - (1 + $periodicRate) ** $periodsPaid)
            / ((1 + $periodicRate) ** $numberOfPayments - 1);

        return round(max($remaining, 0), 2);
    }
}