<?php

declare(strict_types=1);

namespace FeeCalculation\CommissionTask\Service;
require "User.php";

class Fee
{
    const USD_CONVERTION = 1.1497;
    const JPY_CONVERTION = 129.53;
    const MAX_FEE = 5;
    const MIN_FEE = 0.5;
    const IN_COMMISION_FEE = 0.0003;
    const OUT_COMMISION_FEE = 0.003;
    const MAX_PER_WEEK_OUT = 1000;
    public $fileName;
    private $currency;
    private $mapToUser = [];

    public function readFile(string $fileName)
    {
        $file = fopen($fileName, "r") or die ("Unable to open file!");
        while(!feof($file)) {
            $singleLine = fgets($file);
            $word_arr = explode(",", $singleLine);
            fwrite(STDOUT, $this->calculateFee($word_arr[0], $word_arr[1], $word_arr[2], $word_arr[3], $word_arr[4], rtrim($word_arr[5])) . "\n");
        }
        fclose($file);
    }

    public function calculateFee(string $date, string $userID, string $userType,
         string $operationType, string $operationAmount, string $currency)
    {
        
        $currencyConverted = $operationAmount;
        $maxFeeConverted = self::MAX_FEE;
        $minFeeConverted = self::MIN_FEE;
        $maxPerWeekConverted = self::MAX_PER_WEEK_OUT;

        $this->currency = $currency;
        if($currency != 'EUR') {
            $currencyConverted = $this->convertToEur(floatval($currencyConverted));
            $maxFeeConverted = $this->convertFromEur(floatval($maxFeeConverted));
            $minFeeConverted = $this->convertFromEur(floatval($minFeeConverted));
        }

        if($operationType == 'cash_in') {
            if($currencyConverted * self::IN_COMMISION_FEE > self::MAX_FEE) {
                return (strval($this->round_up($maxFeeConverted, 2)));
            } else {
                return (strval($this->round_up($operationAmount * self::IN_COMMISION_FEE, 2)));
            }
        } else {
                if($userType == 'legal') {
                    if($currencyConverted * self::OUT_COMMISION_FEE < self::MIN_FEE) {
                        return (strval($this->round_up($minFeeConverted, 2)));
                    } else {
                        return (strval($this->round_up($operationAmount * self::OUT_COMMISION_FEE, 2)));
                    }
                } else {
                    $diff = 0;
                    // creates mapping for users with id as the key
                    if(!array_key_exists(intval($userID), $this->mapToUser)) {
                        $user = new User();
                        $user->setId(intval($userID));
                        $this->mapToUser = array(intval($userID) => $user);
                    } else {
                        $user = $this->mapToUser[$userID];
                        $diff = strtotime($date) - strtotime($user->getMondayDate());
                    }
                    // creates array inside user object if one week has passed from last monday or null
                    if($user->getLastTransactions() == null || $diff / (60*60*24) > 6) {
                        if($currencyConverted > self::MAX_PER_WEEK_OUT) {
                            $user->setLastTransactions($maxPerWeekConverted, $date, $maxPerWeekConverted);
                            $overMaxFee = $currencyConverted - $maxPerWeekConverted;
                            return (strval($this->round_up($this->convertFromEur($overMaxFee) * self::OUT_COMMISION_FEE, 2)));
                        } else {
                            $user->setLastTransactions(floatval($currencyConverted), $date, $maxPerWeekConverted);    
                            return (strval($this->round_up(0, 2)));
                        }
                    } else {
                        // checks if the number of transactions is less than 3 per week
                        if($user->getLastTransactions()[$user->getMondayDate()][1] == 0) {
                            return (strval($this->round_up($operationAmount * self::OUT_COMMISION_FEE, 2)));
                        } // checks if the amount is not exceeded 1000 
                         else if($user->getLastTransactions()[$user->getMondayDate()][0] - $currencyConverted < 0) {
                            $overTheReminder = $currencyConverted - $user->getLastTransactions()[$user->getMondayDate()][0]; 
                            $user->updateLastTransactions(floatval($user->getLastTransactions()[$user->getMondayDate()][0]));
                            return (strval($this->round_up($this->convertFromEur($overTheReminder) * self::OUT_COMMISION_FEE, 2)));
                            
                        } else {
                            $user->updateLastTransactions(floatval($currencyConverted));
                            return (strval($this->round_up(0, 2)));
                            
                        }
                    }
                }   
        }           
    }
    private function convertToEur(float $amount)
    {
        if($this->currency == "USD") {
            return $amount / self::USD_CONVERTION;
        } else if($this->currency == "JPY") {
            return $amount / self::JPY_CONVERTION; 
        } else {
            return $amount;
        }
    }

    private function convertFromEur(float $amount)
    {
        if($this->currency == "USD") {
            return $amount * self::USD_CONVERTION;
        } else if($this->currency == "JPY") {
            return $amount * self::JPY_CONVERTION; 
        } else {
            return $amount;
        }
    }

    private function round_up($value, $precision)
    {
        if($this->currency == "JPY") {
            $pow = pow(10, 0);
            $temp = (ceil($pow * $value) + ceil($pow * $value - ceil($pow * $value))) / $pow;
             return (number_format($temp, 0, '.', ''));
        }
        else {
            $pow = pow(10, $precision);
            $temp = (ceil($pow * $value) + ceil($pow * $value - ceil($pow * $value))) / $pow;
            return (number_format($temp, 2, '.', ''));
        }
    }
}

