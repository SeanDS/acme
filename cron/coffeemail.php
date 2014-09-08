<?php

include('/path/to/config.php');

$database = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);

$coffeeRota = $database->prepare("
	SELECT day, month, year, username, firstname, lastname
	FROM " . TABLE_PREFIX . "schedule AS schedule
	INNER JOIN " . TABLE_PREFIX . "users AS users
		ON schedule.userid = users.userid
	WHERE day = ? AND month = ? AND year = ? AND type = 0 AND mailsent = 0
");

$tomorrow = mktime(12, 0, 0, date('n'), date('j') + 1, date('Y'));

$thisDay = date('j', $tomorrow);
$thisMonth = date('n', $tomorrow);
$thisYear = date('Y', $tomorrow);

$coffeeRota->bind_param("iii", $thisDay, $thisMonth, $thisYear);
$coffeeRota->bind_result($day, $month, $year, $username, $firstName, $lastName);
$coffeeRota->execute();
$coffeeRota->fetch();
$coffeeRota->close();

echo date('r') . "\n";

if($firstName)
{
	echo "Found a user on coffee duty tomorrow: " . $firstName . " " . $lastName . "\n";

	$subject = 'Coffee Duty Reminder';
	$date = mktime(12, 0, 0, $month, $day, $year); // midday so that we don't have problems with daylight savings time
	$weekBeginning = gmdate('l jS F Y', $date);

	$message = 'Hello ' . $firstName . ",\r\n\r\nThis email is to remind you that you will be on coffee duty this week (week beginning " . $weekBeginning . ").\r\n\r\nMay the bean be with you,\r\n\r\nThe ACME Coffee Facilitation Robot";

	$headers = 'From: noreply@domain.com' . "\r\n" .
	    'Reply-To: coffeemaster@domain.com' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	echo "Getting email address from LDAP...";

	$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
	$bind = @ldap_bind($ldap);
	$search = ldap_search($ldap, LDAP_USER_DN, "(&(uid=" . $username . ")(objectClass=Person))");
	$info = ldap_get_entries($ldap, $search);

	$email = $info[0]['mail'][0];

	echo "Sending mail...\n";
	echo "Email address: " . $email . "\n";
	$success = mail($email, $subject, $message, $headers);

	if($success)
	{
		echo "Mail sent successfully.\n";

		$coffeeUpdate = $database->prepare("
			UPDATE " . TABLE_PREFIX . "schedule
			SET mailsent = 1
			WHERE day = ? AND month = ? AND year = ? AND type = 0
		");

		$coffeeUpdate->bind_param("iii", $day, $month, $year);
		$coffeeUpdate->execute();

		echo "Set 'mail sent' flag to true.\n";
	}
	else
	{
		echo "Mail not sent successfully!\n";
	}
}
else
{
	echo "No scheduled coffee duty user found (or email already sent).\n";
}

$day = null;
$month = null;
$year = null;
$firstName = null;
$lastName = null;
$email = null;

$doughnutRota = $database->prepare("
	SELECT day, month, year, username, firstname, lastname
	FROM " . TABLE_PREFIX . "schedule AS schedule
	INNER JOIN " . TABLE_PREFIX . "users AS users
		ON schedule.userid = users.userid
	WHERE day = ? AND month = ? AND year = ? AND type = 1 AND mailsent = 0
");

$doughnutRota->bind_param("iii", $thisDay, $thisMonth, $thisYear);
$doughnutRota->bind_result($day, $month, $year, $username, $firstName, $lastName);
$doughnutRota->execute();
$doughnutRota->fetch();
$doughnutRota->close();

if($firstName)
{
	echo "Found a user on doughnut duty tomorrow: " . $firstName . " " . $lastName . "\n";

	$subject = 'Doughnut Duty Reminder';
	$date = mktime(12, 0, 0, $month, $day, $year); // midday so that we don't have problems with daylight savings time
	$dateText = gmdate('l jS F Y', $date);

	$message = 'Hello ' . $firstName . ",\r\n\r\nThis email is to remind you that you will be on doughnut duty tomorrow (" . $dateText . ").\r\n\r\nRegards,\r\n\r\nThe ACME Doughnut Facilitation Robot\r\n(Now with added sprinkles)";

	$headers = 'From: noreply@domain.com' . "\r\n" .
	    'Reply-To: coffeemaster@domain.com' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	echo "Getting email address from LDAP...";

	$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
	$bind = @ldap_bind($ldap);
	$search = ldap_search($ldap, LDAP_USER_DN, "(&(uid=" . $username . ")(objectClass=Person))");
	$info = ldap_get_entries($ldap, $search);

	$email = $info[0]['mail'][0];

	echo "Sending mail...\n";
	echo "Email address: " . $email . "\n";
	$success = mail($email, $subject, $message, $headers);

	if($success)
	{
		echo "Mail sent successfully.\n";

		$doughnutUpdate = $database->prepare("
			UPDATE " . TABLE_PREFIX . "schedule
			SET mailsent = 1
			WHERE day = ? AND month = ? AND year = ? AND type = 1
		");

		$doughnutUpdate->bind_param("iii", $day, $month, $year);
		$doughnutUpdate->execute();

		echo "Set 'mail sent' flag to true.\n";
	}
	else
	{
		echo "Mail not sent successfully!\n";
	}
}
else
{
	echo "No scheduled doughnut duty user found (or email already sent).\n";
}

?>
