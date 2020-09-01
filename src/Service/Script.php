<?php
namespace FeeCalculation\CommissionTask\Service;
require "Fee.php";

$Fee = new Fee();
$Fee->readFile($argv[1]);
?>