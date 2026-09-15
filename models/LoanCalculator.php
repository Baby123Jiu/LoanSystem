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
}