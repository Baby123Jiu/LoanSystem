<?php

require_once "../models/LoanCalculator.php";

$calculator = new LoanCalculator();

$result = $calculator->calculateFlat(
    10000,
    10,
    2,
    "monthly"
);

echo "<pre>";
print_r($result);
echo "</pre>";

?>