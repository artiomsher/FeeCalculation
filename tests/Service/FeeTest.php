<?php

declare(strict_types=1);

namespace FeeCalculation\CommissionTask\Tests\Service;

use PHPUnit\Framework\TestCase;
use FeeCalculation\CommissionTask\Service\Fee;

class FeeTest extends TestCase
{
    /**
     * @var fee
     */
    private $fee;


    public function setUp() :void
    {
        $this->fee = new Fee();
    }

    /**
     * @param string $date
     * @param string $userID
     * @param string $userType
     * @param string $operationType
     * @param string $userAmount
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForCalculationTesting
     */
    public function testCalculation(string $date, string $userID, string $userType,
         string $operationType, string $operationAmount, string $currency, string $expectation)
    { 
        $this->assertEquals(
            $expectation,
            $this->fee->calculateFee($date, $userID, $userType, $operationType, $operationAmount, $currency)
        );
    }

    public function dataProviderForCalculationTesting(): array
    {

        return [
            'fee from cash_in in EUR' => ['2016-01-05','1','natural','cash_in','200.00','EUR', '0.06'],
            'fee from cash_in in EUR MAX' => ['2016-01-05','1','natural','cash_in','1000000.00','EUR', '5.00'],
            'fee from cash_out for legal persons' => ['2016-01-05','2','legal','cash_out','200.00','EUR', '0.60'],
            'fee from cash_out for legal persons MIN' => ['2016-01-05','2','legal','cash_out','2.00','EUR', '0.50'],
            'fee from cash_out for natural persons > 1000 EUR' => ['2016-01-05','3','natural','cash_out','1300.00','EUR', '0.90'],
            'fee from cash_out for natural persons < 1000 EUR' => ['2016-01-05','3','natural','cash_out','300.00','EUR', '0.00'],
            'fee from cash_out for natural persons < 1000 EUR in USD' => ['2016-01-05','3','natural','cash_out','1100.00','USD', '0.00'],
            'fee from cash_out for natural persons > 1000 EUR in JPY' => ['2016-01-05','3','natural','cash_out','3000000','JPY', '8612'],
        ];
    }

    /**
     * @param string $date
     * @param string $userID
     * @param string $userType
     * @param string $operationType
     * @param string $userAmount
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForOneWeekOperationsTesting
     */
    public function testOneWeekOperations(string $date, string $userID, string $userType,
         string $operationType, string $operationAmount, string $currency, string $expectation)
    {   
        $this->fee->calculateFee("2016-01-05", "3", "natural", "cash_out", "600.00", "EUR");
        $this->assertEquals(
            $expectation,
            $this->fee->calculateFee($date, $userID, $userType, $operationType, $operationAmount, $currency)
        );
    }

    public function dataProviderForOneWeekOperationsTesting(): array
    {

        return [
            'fee from cash_out for natural persons < 1000 EUR in one week' => ['2016-01-05','3','natural','cash_out','300.00','EUR', '0.00'],
            'fee from cash_out for natural persons > 1000 EUR in one week' => ['2016-01-05','3','natural','cash_out','700.00','EUR', '0.90'],
            'fee from cash_out for natural persons < 1000 EUR next week' => ['2016-01-11','3','natural','cash_out','700.00','EUR', '0.00'],
        ];
    }
    /**
     * @param string $date
     * @param string $userID
     * @param string $userType
     * @param string $operationType
     * @param string $userAmount
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForMoreThanThreeOperationsTesting
     */
    public function testMoreThanThreeOperations(string $date, string $userID, string $userType,
         string $operationType, string $operationAmount, string $currency, string $expectation)
    {   
        $this->fee->calculateFee("2016-01-05", "3", "natural", "cash_out", "200.00", "EUR");
        $this->fee->calculateFee("2016-01-06", "3", "natural", "cash_out", "200.00", "EUR");
        $this->fee->calculateFee("2016-01-07", "3", "natural", "cash_out", "200.00", "EUR");
        $this->assertEquals(
            $expectation,
            $this->fee->calculateFee($date, $userID, $userType, $operationType, $operationAmount, $currency)
        );
    }

    public function dataProviderForMoreThanThreeOperationsTesting(): array
    {
        return [
            'fee from cash_out for natural persons > 1000 EUR in one week' => ['2016-01-08','3','natural','cash_out','400.00','EUR', '1.20'],
            'fee from cash_out for natural persons next week' => ['2016-01-11','3','natural','cash_out','400.00','EUR', '0.00'],
        ];
    }


}
