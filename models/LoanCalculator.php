<?php

declare(strict_types=1);

class LoanCalculator
{
    private const ALLOWED_FREQUENCIES = ['monthly', 'yearly'];
    private const ALLOWED_METHODS = ['flat', 'reducing'];

    public function calculateFlat(float $amount, float $rate, int $years, string $frequency): array
    {
        $this->validateLoanInputs($amount, $rate, $years, $frequency);

        $interest = $amount * ($rate / 100) * $years;
        $totalPayable = $amount + $interest;
        $numberOfInstallments = $this->getInstallmentCount($years, $frequency);
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

    public function calculateReducing(float $amount, float $rate, int $years, string $frequency): array
    {
        $this->validateLoanInputs($amount, $rate, $years, $frequency);

        $numberOfInstallments = $this->getInstallmentCount($years, $frequency);
        $periodicRate = $this->getPeriodicRate($rate, $frequency);

        if ($rate === 0.0) {
            $installmentAmount = $amount / $numberOfInstallments;
            $totalPayable = $amount;
            $interest = 0.0;
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

    public function calculateRemainingBalance(
        float $amount,
        float $rate,
        int $years,
        string $frequency,
        string $method,
        int $installmentsPaid
    ): float {
        $this->validateLoanInputs($amount, $rate, $years, $frequency);

        if (!in_array($method, self::ALLOWED_METHODS, true)) {
            throw new InvalidArgumentException(
                "Method must be one of: " . implode(', ', self::ALLOWED_METHODS)
            );
        }

        $numberOfInstallments = $this->getInstallmentCount($years, $frequency);

        if ($installmentsPaid < 0 || $installmentsPaid > $numberOfInstallments) {
            throw new InvalidArgumentException(
                "installmentsPaid must be between 0 and {$numberOfInstallments}."
            );
        }

        if ($method === "flat") {
            $result = $this->calculateFlat($amount, $rate, $years, $frequency);
            $remaining = $result["total_payable"] - ($result["installment_amount"] * $installmentsPaid);

            return round(max($remaining, 0), 2);
        }

        // Reducing balance
        $periodicRate = $this->getPeriodicRate($rate, $frequency);

        if ($rate === 0.0) {
            $remaining = $amount - ($amount / $numberOfInstallments) * $installmentsPaid;

            return round(max($remaining, 0), 2);
        }

        $remaining = $amount
            * ((1 + $periodicRate) ** $numberOfInstallments - (1 + $periodicRate) ** $installmentsPaid)
            / ((1 + $periodicRate) ** $numberOfInstallments - 1);

        return round(max($remaining, 0), 2);
    }

    private function validateLoanInputs(float $amount, float $rate, int $years, string $frequency): void
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

        if (!in_array($frequency, self::ALLOWED_FREQUENCIES, true)) {
            throw new InvalidArgumentException(
                "Frequency must be one of: " . implode(', ', self::ALLOWED_FREQUENCIES)
            );
        }
    }

    private function getInstallmentCount(int $years, string $frequency): int
    {
        return $frequency === "monthly" ? $years * 12 : $years;
    }

    private function getPeriodicRate(float $rate, string $frequency): float
    {
        return $frequency === "monthly" ? ($rate / 100) / 12 : $rate / 100;
    }
}