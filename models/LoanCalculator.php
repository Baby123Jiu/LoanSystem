<?php

class LoanCalculator
{
    public function calculateFlat($amount, $rate, $years, $frequency)
    {
        $interest = $amount * ($rate / 100) * $years;

        $totalPayable = $amount + $interest;

        if ($frequency === "monthly") {
            $numberOfInstallments = $years * 12;
        } else {
            $numberOfInstallments = $years;
        }

        $installmentAmount = $totalPayable / $numberOfInstallments;

        return [
            "method" => "Flat Interest",
            "principal" => $amount,
            "interest" => round($interest, 2),
            "total_payable" => round($totalPayable, 2),
            "number_of_installments" => $numberOfInstallments,
            "installment_amount" => round($installmentAmount, 2)
        ];
    }

    public function calculateReducing($amount, $rate, $years, $frequency)
    {
        if ($frequency === "monthly") {
            $numberOfInstallments = $years * 12;
            $periodicRate = ($rate / 100) / 12;
        } else {
            $numberOfInstallments = $years;
            $periodicRate = $rate / 100;
        }

        if ($rate == 0) {
            $installmentAmount = $amount / $numberOfInstallments;
            $totalPayable = $amount;
            $interest = 0;
        } else {
            $installmentAmount = $amount * $periodicRate * (1 + $periodicRate) ** $numberOfInstallments
                / ((1 + $periodicRate) ** $numberOfInstallments - 1);

            $totalPayable = $installmentAmount * $numberOfInstallments;
            $interest = $totalPayable - $amount;
        }

        return [
            "method" => "Reducing Balance",
            "principal" => $amount,
            "interest" => round($interest, 2),
            "total_payable" => round($totalPayable, 2),
            "number_of_installments" => $numberOfInstallments,
            "installment_amount" => round($installmentAmount, 2)
        ];
    }

    public function calculateRemainingBalance($amount, $rate, $years, $frequency, $method, $installmentsPaid)
    {
        if ($frequency === "monthly") {
            $numberOfInstallments = $years * 12;
            $periodicRate = ($rate / 100) / 12;
        } else {
            $numberOfInstallments = $years;
            $periodicRate = $rate / 100;
        }

        if ($method === "flat") {
            $result = $this->calculateFlat($amount, $rate, $years, $frequency);
            $remaining = $result["total_payable"] - ($result["installment_amount"] * $installmentsPaid);

            return round(max($remaining, 0), 2);
        }

        // Reducing balance
        if ($rate == 0) {
            $remaining = $amount - ($amount / $numberOfInstallments) * $installmentsPaid;

            return round(max($remaining, 0), 2);
        }

        $remaining = $amount
            * ((1 + $periodicRate) ** $numberOfInstallments - (1 + $periodicRate) ** $installmentsPaid)
            / ((1 + $periodicRate) ** $numberOfInstallments - 1);

        return round(max($remaining, 0), 2);
    }
}