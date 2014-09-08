<?php

include('/path/to/config.php');

$database = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);

$usersQuery = $database->prepare("
	SELECT userid, username, weeklypayment
	FROM " . TABLE_PREFIX . "users
	WHERE weeklypayment > 0
");

$usersQuery->bind_result($userId, $username, $weeklyPayment);
$usersQuery->execute();
$usersQuery->store_result();

echo date('r') . "\n";
echo "Starting transactions...\n";

while($usersQuery->fetch())
{
	echo "Adding GBP " . sprintf("%.2f", $weeklyPayment) . " payment for " . $username . "\n";

	$transactionQuery = $database->prepare("
		INSERT INTO " . TABLE_PREFIX . "transactions (comment, userid, amount, time)
		VALUES (?, ?, ?, ?)
	") or die($database->error);

	$comment = "Weekly Payment";
	$payment = -floatval($weeklyPayment);
	$time = time();

	$transactionQuery->bind_param("sidi", $comment, $userId, $payment, $time);
	$transactionQuery->execute();
	$transactionQuery->close();
}

$usersQuery->close();

echo "Finished transactions.\n";

?>
