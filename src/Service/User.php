<?php
declare(strict_types=1);

namespace FeeCalculation\CommissionTask\Service;

class User
{	
	const NUMOFCASHOUT = 3;

	private $id;
	private $lastTransactions;
	private $mondayDate;

	public function getId() {
		return $this->id;
	}
	public function setId(int $id) {
		$this->id = $id;
	}
    // finds the start of the week(Monday) and counts number of transactions and its amount
	public function setLastTransactions(float $amount, string $date, float $maxPerWeek) {
		$formattedDate = date("Y-m-d", strtotime($date));
		$day = date('w', strtotime($formattedDate));
		
		if ($day != 0) {
			$mondayDate = date("Y-m-d", strtotime("-" . strval($day - 1) . " days", strtotime($formattedDate)));
		} else {
			$mondayDate = date("Y-m-d", strtotime("-" . strval(abs($day - 6)) . " days", strtotime($formattedDate)));
		}
		$this->lastTransactions = array($mondayDate => array($maxPerWeek - $amount, self::NUMOFCASHOUT-1));
		$this->mondayDate = $mondayDate;
	}
	public function getLastTransactions() {
		return $this->lastTransactions;
	}
	public function updateLastTransactions(float $amount) {
		$this->lastTransactions[$this->mondayDate][0] -= $amount;
		$this->lastTransactions[$this->mondayDate][1] -= 1;
	}
	public function getMondayDate() {
		return $this->mondayDate;
	}
	public function setMondayDate(string $date) {
		$this->mondayDate = $date;
	}


}
