<?php

class CoffeeUser
{
	protected $userId, $username, $firstName, $lastName, $lastLogin, $balance, $balanceUpdateTime, $isAdministrator;

	public function __construct($userId, $username, $firstName, $lastName, $lastLogin, $startingBalance, $isAdministrator)
	{
		$this->userId = $userId;
		$this->username = $username;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->lastLogin = $lastLogin;
		$this->startingBalance = $startingBalance;
		$this->isAdministrator = $isAdministrator;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getFirstName()
	{
		return $this->firstName;
	}

	public function getLastName()
	{
		return $this->lastName;
	}

	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	public function getStartingBalance()
	{
		return $this->startingBalance;
	}

	public function isAdministrator()
	{
		return $this->isAdministrator;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	public function setStartingBalance($startingBalance)
	{
		$this->startingBalance = $startingBalance;
	}
}

?>
