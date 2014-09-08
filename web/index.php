<?php

include('include.php');

$templates->assign('isAdministrator', $coffeeUser->isAdministrator());

if(empty($_GET['do'])) {
	$_GET['do'] = 'viewcalendar';
}

if($_GET['do'] == 'maketransaction')
{
	if($coffeeUser->isAdministrator())
	{
		if(intval($_POST['userid']) && $_POST['comment'] != null && floatval($_POST['amount']) != 0)
		{
			$transactionQuery = $database->prepare("
				INSERT INTO " . TABLE_PREFIX . "transactions (comment, userid, amount, time)
				VALUES (?, ?, ?, ?)
			");

			$transactionQuery->bind_param("sidi", $_POST['comment'], $_POST['userid'], $_POST['amount'], time());
			$transactionQuery->execute();

			$templates->draw('make_transaction_success');
		}
		else
		{
			$usersQuery = $database->query("
				SELECT userid, firstname, lastname
				FROM " . TABLE_PREFIX . "users
				ORDER BY lastname ASC
			");

			$users = array();

			while($user = $usersQuery->fetch_assoc())
			{
				$users[$user['userid']] = $user;
			}

			if($_POST['userid'] != null)
			{
				if($_POST['comment'] == null)
				{
					$templates->assign('noCommentSupplied', true);
				}

				if(floatval($_POST['amount']) == 0)
				{
					$templates->assign('noAmountSupplied', true);
				}
			}

			$templates->assign('users', $users);

			if(intval($_POST['userid']))
			{
				$templates->assign('selectedUserId', intval($_POST['userid']));
			}
			else
			{
				$templates->assign('selectedUserId', intval($_GET['userid']));
			}

			$templates->assign('selectedComment', htmlspecialchars($_POST['comment'], ENT_QUOTES));

			if(floatval($_POST['amount']))
			{
				$templates->assign('selectedAmount', sprintf("%.2f", $_POST['amount']));
			}
			else
			{
				$templates->assign('selectedAmount', "0.00");
			}

			$templates->draw('make_transaction');
		}
	}
}
elseif($_GET['do'] == 'viewcalendar')
{
	require_once('Calendar/Month/Weekdays.php');
	require_once('Calendar/Day.php');

	if(!isset($_GET['y']))
	{
		$_GET['y'] = date('Y');
	}

	if(!isset($_GET['m']))
	{
		$_GET['m'] = date('m');
	}

	if(!isset($_GET['d']))
	{
		$_GET['d'] = date('d');
	}

	// Build the month
	$weekDays = new Calendar_Month_Weekdays($_GET['y'], $_GET['m']);
	$weekDays->defineFirstDayOfWeek(1);
	$weekDays->build();

	$previousMonth = $weekDays->prevMonth(true);
	$nextMonth = $weekDays->nextMonth(true);

	$templates->assign('month', date('F Y', $weekDays->getTimeStamp()));
	$templates->assign('previousMonthLink', '?do=viewcalendar&amp;y=' . date('Y', $previousMonth) . '&amp;m=' . date('n', $previousMonth));
	$templates->assign('nextMonthLink', '?do=viewcalendar&amp;y=' . date('Y', $nextMonth) . '&amp;m=' . date('n', $nextMonth));

	// Get calendar data
	$calendarQuery = $database->prepare("
		SELECT day, month, year, users.firstname, users.lastname, type
		FROM " . TABLE_PREFIX . "schedule AS schedule
		INNER JOIN " . TABLE_PREFIX . "users AS users
			ON schedule.userid = users.userid
		WHERE (month = ? AND year = ?) OR (month = ? AND year = ?) OR (month = ? AND year = ?)
	");

	$firstDayInMonth = $weekDays->fetch();

	while($day = $weekDays->fetch())
	{
		$lastDayInMonth = $day;
	}

	$nextMonth = $lastDayInMonth->thisMonth();
	$nextYear = $lastDayInMonth->thisYear();

	$calendarQuery->bind_param("iiiiii", $firstDayInMonth->thisMonth(), $firstDayInMonth->thisYear(), $_GET['m'], $_GET['y'], $nextMonth, $nextYear);
	$calendarQuery->bind_result($day, $month, $year, $firstName, $lastName, $type);
	$calendarQuery->execute();

	$coffeeDays = array();
	$doughnutDays = array();

	while($calendarQuery->fetch())
	{
		if($type == 0)
		{
			$coffeeDays[] = array(
				'day'		=>	$day,
				'month'		=>	$month,
				'year'		=>	$year,
				'firstName'	=>	$firstName,
				'lastName'	=>	$lastName,
				'type'		=>	$type
			);
		}
		else
		{
			$doughnutDays[] = array(
				'day'		=>	$day,
				'month'		=>	$month,
				'year'		=>	$year,
				'firstName'	=>	$firstName,
				'lastName'	=>	$lastName,
				'type'		=>	$type
			);
		}
	}

	$calendarDays = "";

	while($weekDay = $weekDays->fetch())
	{
		$weekDay->build();

		$day = $weekDay->thisDay();
		$month = $weekDay->thisMonth();
		$year = $weekDay->thisYear();

		if($weekDay->isFirst())
		{
			$calendarDays .= "<tr>\n";
		}

		$coffee = false;

		foreach($coffeeDays as $thisCoffeeDay)
		{
			if($thisCoffeeDay['day'] == $day && $thisCoffeeDay['month'] == $month && $thisCoffeeDay['year'] == $year)
			{
				$coffee = true;
				$coffeeDay = $thisCoffeeDay;
			}
		}

		$doughnuts = false;

		foreach($doughnutDays as $thisDoughnutDay)
		{
			if($thisDoughnutDay['day'] == $day && $thisDoughnutDay['month'] == $month && $thisDoughnutDay['year'] == $year)
			{
				$doughnuts = true;
				$doughnutDay = $thisDoughnutDay;
			}
		}

		if($coffee)
		{
			if($coffeeUser->isAdministrator())
			{
				$adminEdit = "<p><a href=\"?do=removerotauser&amp;day=" . $weekDay->thisDay() . "&amp;month=" . $weekDay->thisMonth() . "&amp;year=" . $weekDay->thisYear() . "&amp;type=0\">Delete</a></p>";
			}
			else
			{
				$adminEdit = "";
			}

			$calendarDays .= "<td class=\"selected\"><p>" . $weekDay->thisDay() . "</p><p><strong>Coffee this week</strong>: " . $coffeeDay['firstName'] . " " . $coffeeDay['lastName'] . "</p>" . $adminEdit . "</td>\n";
		}
		elseif($doughnuts)
		{
			if($coffeeUser->isAdministrator())
			{
				$adminEdit = "<p><a href=\"?do=removerotauser&amp;day=" . $weekDay->thisDay() . "&amp;month=" . $weekDay->thisMonth() . "&amp;year=" . $weekDay->thisYear() . "&amp;type=1\">Delete</a></p>";
			}
			else
			{
				$adminEdit = "";
			}

			$calendarDays .= "<td class=\"selected\"><p>" . $weekDay->thisDay() . "</p><p><strong>Doughnuts</strong>: " . $doughnutDay['firstName'] . " " . $doughnutDay['lastName'] . "</p>" . $adminEdit . "</td>\n";
		}
		else
		{
			$calendarDays .= "<td class=\"unselected\"><p>" . $weekDay->thisDay() . "</p></td>\n";
		}

		if($weekDay->isLast())
		{
			$calendarDays .= "</tr>\n";
		}
	}

	$templates->assign('calendarDays', $calendarDays);
	$templates->draw('view_calendar');
}
elseif($_GET['do'] == 'viewusers')
{
	if($coffeeUser->isAdministrator())
	{
		$usersQuery = $database->prepare("
			SELECT users.userid, users.username, users.firstname, users.lastname, users.lastlogin, users.startingbalance, SUM(transactions.amount) AS transactionbalance, users.weeklypayment
			FROM " . TABLE_PREFIX . "users AS users
			LEFT JOIN " . TABLE_PREFIX . "transactions AS transactions
			ON users.userid = transactions.userid
			GROUP BY users.userid
			ORDER BY lastname ASC
		");

		$usersQuery->bind_result($userId, $username, $firstName, $lastName, $lastLogin, $startingBalance, $transactionBalance, $weeklyPayment);
		$usersQuery->execute();

		$users = array();

		while($usersQuery->fetch())
		{
			$users[$userId] = array(
				'username'		=>	$username,
				'firstname'		=>	$firstName,
				'lastname'		=>	$lastName,
				'lastlogin'		=>	$lastLogin,
				'balance'		=>	$startingBalance + $transactionBalance,
				'weeklyPayment'		=>	$weeklyPayment
			);
		}

		$usersQuery->close();

		$templates->assign('users', $users);
		$templates->draw('view_users');
	}
}
elseif($_GET['do'] == 'addrotauser')
{
	if($coffeeUser->isAdministrator())
	{
		if(intval($_POST['userid']) > 0 && intval($_POST['day']) > 0 && intval($_POST['month']) > 0 && intval($_POST['year']) > 0 && intval($_POST['type']) >= 0 && intval($_POST['type'] <= 1))
		{
			$addRotaQuery = $database->prepare("
				INSERT INTO " . TABLE_PREFIX . "schedule (userid, day, month, year, type)
				VALUES (?, ?, ?, ?, ?)
			");

			$addRotaQuery->bind_param("iiiii", intval($_POST['userid']), intval($_POST['day']), intval($_POST['month']), intval($_POST['year']), intval($_POST['type']));
			$addRotaQuery->execute();
			$addRotaQuery->close();

			$templates->draw('add_rota_user_success');
		}
		else
		{
			$usersQuery = $database->query("
				SELECT userid, firstname, lastname
				FROM " . TABLE_PREFIX . "users
				ORDER BY lastname ASC
			");

			$users = array();

			while($user = $usersQuery->fetch_assoc())
			{
				$users[$user['userid']] = $user;
			}

			$templates->assign('users', $users);

			$templates->draw('add_rota_user');
		}
	}
}
elseif($_GET['do'] == 'removerotauser' && intval($_GET['day']) > 0 && intval($_GET['month']) > 0 && intval($_GET['year']) > 0 && $_GET['type'] != null)
{
	if($coffeeUser->isAdministrator())
	{
		$rotaQuery = $database->prepare("
			SELECT day, month, year, type, firstname, lastname
			FROM " . TABLE_PREFIX . "schedule AS schedule
				INNER JOIN " . TABLE_PREFIX . "users AS users
					ON schedule.userid = users.userid
			WHERE day = ? AND month = ? AND year = ? AND type = ?
		");

		$rotaQuery->bind_param("iiii", intval($_GET['day']), intval($_GET['month']), intval($_GET['year']), intval($_GET['type']));
		$rotaQuery->bind_result($day, $month, $year, $type, $firstName, $lastName);
		$rotaQuery->execute();
		$rotaQuery->fetch();
		$rotaQuery->close();

		if($firstName)
		{
			if($_POST['confirm'] != true)
			{
				$templates->assign('rotaFirstName', $firstName);
				$templates->assign('rotaLastName', $lastName);
				$templates->assign('rotaType', $type);
				$templates->assign('rotaDay', $day);
				$templates->assign('rotaMonth', $month);
				$templates->assign('rotaYear', $year);
				$templates->draw('remove_rota_user');
			}
			else
			{
				$removeRotaQuery = $database->prepare("
					DELETE FROM " . TABLE_PREFIX . "schedule
					WHERE day = ? AND month = ? AND year = ? AND type = ?
				");

				$removeRotaQuery->bind_param("iiii", $day, $month, $year, $type);
				$removeRotaQuery->execute();

				$templates->draw('remove_rota_user_success');
			}
		}
	}
}
elseif($_GET['do'] == 'adduser')
{
	if($coffeeUser->isAdministrator())
	{
		if($_POST['username'] != null)
		{
			// Check username isn't already in use
			$checkUserQuery = $database->prepare("
				SELECT username
				FROM " . TABLE_PREFIX . "users
				WHERE username = ?
			");

			$checkUserQuery->bind_param("s", $_POST['username']);
			$checkUserQuery->bind_result($__username);
			$checkUserQuery->execute();
			$checkUserQuery->fetch();
			$checkUserQuery->close();

			if($__username)
			{
				// A user with that username already exists
				$templates->draw('add_user_failure');
			}
			else
			{
				$addUserQuery = $database->prepare("
					INSERT INTO " . TABLE_PREFIX . "users (username, firstname, lastname, startingbalance, weeklypayment)
					VALUES (?, ?, ?, ?, ?)
				");

				$addUserQuery->bind_param("sssdd", $_POST['username'], $_POST['firstname'], $_POST['lastname'], $_POST['startingbalance'], $_POST['weeklypayment']);
				$addUserQuery->execute();
				$addUserQuery->close();

				$templates->assign('addedUser', htmlspecialchars($_POST['username'], ENT_QUOTES));
				$templates->draw('add_user_success');
			}
		}
		else
		{
			// get a list of users from LDAP
			$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
			$bind = @ldap_bind($ldap);
			$search = ldap_search($ldap, LDAP_USER_DN, "(objectClass=Person)");
			$info = ldap_get_entries($ldap, $search);

			$newUsers = array();

			foreach($info as $key => $value)
			{
				$newUsers[] = $info[$key]['uid'][0];
			}

			$templates->assign('users', $newUsers);

			$templates->draw('add_user');
		}
	}
}
elseif($_GET['do'] == 'deleteuser')
{
	if($coffeeUser->isAdministrator())
	{
		if(intval($_GET['userid']))
		{
			$userQuery = $database->prepare("
				SELECT userid, username, firstname, lastname
				FROM " . TABLE_PREFIX . "users
				WHERE userid = ?
			");

			$userQuery->bind_param("i", $_GET['userid']);
			$userQuery->bind_result($userId, $username, $firstName, $lastName);
			$userQuery->execute();
			$userQuery->fetch();
			$userQuery->close();

			$templates->assign('deleteUserFirstName', $firstName);
			$templates->assign('deleteUserLastName', $lastName);

			if($userId)
			{
				if($userId != SUPER_ADMINISTRATOR)
				{
					if($_POST['confirm'] == 'true')
					{
						$database->autocommit(false);

						$removeUserQuery = $database->prepare("
							DELETE FROM " . TABLE_PREFIX . "users
							WHERE userid = ?
						");

						$removeUserQuery->bind_param("i", $userId);
						$removeUserQuery->execute();
						$removeUserQuery->close();

						$removeAdministratorQuery = $database->prepare("
							DELETE FROM " . TABLE_PREFIX . "administrators
							WHERE userid = ?
						");

						$removeAdministratorQuery->bind_param("i", $userId);
						$removeAdministratorQuery->execute();
						$removeAdministratorQuery->close();

						$removeTransactionsQuery = $database->prepare("
							DELETE FROM " . TABLE_PREFIX . "transactions
							WHERE userid = ?
						");

						$removeTransactionsQuery->bind_param("i", $userId);
						$removeTransactionsQuery->execute();
						$removeTransactionsQuery->close();

						$removeScheduleQuery = $database->prepare("
							DELETE FROM " . TABLE_PREFIX . "schedule
							WHERE userid = ?
						");

						$removeScheduleQuery->bind_param("i", $userId);
						$removeScheduleQuery->execute();
						$removeScheduleQuery->close();

						$database->commit();
						$database->autocommit(true);

						$templates->draw('delete_user_success');
					}
					else
					{
						$templates->assign('userToDelete', $firstName . " " . $lastName);
						$templates->assign('userIdToDelete', $userId);
						$templates->draw('delete_user');
					}
				}
				else
				{
					$templates->draw('delete_user_failure');
				}
			}
		}
	}
}
elseif($_GET['do'] == 'viewtransactions' && intval($_GET['userid']))
{
	if($coffeeUser->isAdministrator())
	{
		$nameQuery = $database->prepare("
			SELECT firstname, lastname, startingbalance, SUM(transactions.amount)
			FROM " . TABLE_PREFIX . "users AS users
				LEFT JOIN " . TABLE_PREFIX . "transactions AS transactions
					ON users.userid = transactions.userid
			WHERE users.userid = ?
			GROUP BY users.userid
		");

		$nameQuery->bind_param("i", $_GET['userid']);
		$nameQuery->bind_result($firstName, $lastName, $startingBalance, $transactionBalance);
		$nameQuery->execute();
		$nameQuery->fetch();
		$nameQuery->close();

		if($firstName != null)
		{
			$transactionsQuery = $database->prepare("
				SELECT transactionid, comment, amount, time
				FROM " . TABLE_PREFIX . "transactions
				WHERE userid = ?
				ORDER BY time DESC
			");

			$transactionsQuery->bind_param("i", $_GET['userid']);
			$transactionsQuery->bind_result($transactionId, $comment, $amount, $time);
			$transactionsQuery->execute();

			$currentBalance = $startingBalance + $transactionBalance;
			$balance = $currentBalance;

			$transactions = array();

			while($transactionsQuery->fetch())
			{
				$transactions[$transactionId] = array(
					'comment'		=>	$comment,
					'amount'		=>	$amount,
					'time'			=>	$time,
					'runningbalance'	=>	$balance
				);

				$balance -= floatval($amount);
			}

			$templates->assign('userToView', $firstName . " " . $lastName);
			$templates->assign('startingBalance', $startingBalance);
			$templates->assign('currentBalance', $currentBalance);
			$templates->assign('transactions', $transactions);
			$templates->draw('view_other_user_transactions');
		}
	}
}
elseif($_GET['do'] == 'removetransaction' && intval($_GET['transactionid']))
{
	if($coffeeUser->isAdministrator())
	{
		$transactionQuery = $database->prepare("
			SELECT transactionid, firstname, lastname
			FROM " . TABLE_PREFIX . "transactions AS transactions
				INNER JOIN " . TABLE_PREFIX . "users AS users
					ON transactions.userid = users.userid
			WHERE transactionid = ?
		");

		$transactionQuery->bind_param("i", intval($_GET['transactionid']));
		$transactionQuery->bind_result($transactionId, $firstName, $lastName);
		$transactionQuery->execute();
		$transactionQuery->fetch();
		$transactionQuery->close();

		if($transactionId)
		{
			if($_POST['confirm'] != true)
			{
				$templates->assign('transactionId', $transactionId);
				$templates->assign('transactionFirstName', $firstName);
				$templates->assign('transactionLastName', $lastName);
				$templates->draw('remove_transaction');
			}
			else
			{
				$removeTransactionsQuery = $database->prepare("
					DELETE FROM " . TABLE_PREFIX . "transactions
					WHERE transactionid = ?
				");

				$removeTransactionsQuery->bind_param("i", $transactionId);
				$removeTransactionsQuery->execute();

				$templates->draw('remove_transaction_success');
			}
		}
	}
}
elseif($_GET['do'] == 'viewusertransactions')
{
	$amountQuery = $database->prepare("
		SELECT startingbalance, SUM(transactions.amount)
		FROM " . TABLE_PREFIX . "users AS users
			LEFT JOIN " . TABLE_PREFIX . "transactions AS transactions
				ON users.userid = transactions.userid
		WHERE users.userid = ?
		GROUP BY users.userid
	");

	$amountQuery->bind_param("i", $coffeeUser->getUserId());
	$amountQuery->bind_result($startingBalance, $transactionBalance);
	$amountQuery->execute();
	$amountQuery->fetch();
	$amountQuery->close();

	$transactionsQuery = $database->prepare("
		SELECT transactionid, comment, amount, time
		FROM " . TABLE_PREFIX . "transactions
		WHERE userid = ?
		ORDER BY time DESC
	");

	$transactionsQuery->bind_param("i", $coffeeUser->getUserId());
	$transactionsQuery->bind_result($transactionId, $comment, $amount, $time);
	$transactionsQuery->execute();

	$balance = $startingBalance + $transactionBalance;

	// assign $balance before it changes (it is used in the loop below)
	$templates->assign('balance', $balance);

	$transactions = array();

	while($transactionsQuery->fetch())
	{
		$transactions[$transactionId] = array(
			'comment'		=>	$comment,
			'amount'		=>	$amount,
			'time'			=>	$time,
			'runningbalance'	=>	$balance
		);

		$balance -= floatval($amount);
	}

	$templates->assign('startingBalance', $startingBalance);
	$templates->assign('transactions', $transactions);
	$templates->draw('view_user_transactions');
}
elseif($_GET['do'] == 'changeotheruserdetails' && intval($_GET['userid']))
{
	if($coffeeUser->isAdministrator())
	{
		$userQuery = $database->prepare("
			SELECT userid, username, firstname, lastname, startingbalance, weeklypayment
			FROM " . TABLE_PREFIX . "users
			WHERE userid = ?
		");

		$userQuery->bind_param("i", intval($_GET['userid']));
		$userQuery->bind_result($userId, $username, $firstName, $lastName, $startingBalance, $weeklyPayment);
		$userQuery->execute();
		$userQuery->fetch();
		$userQuery->close();

		if($userId != null)
		{
			$templates->assign('otherFirstName', $firstName);
			$templates->assign('otherLastName', $lastName);

			if($_POST['username'] != null && $_POST['firstname'] != null && $_POST['lastname'] != null)
			{
				$userUpdateQuery = $database->prepare("
					UPDATE " . TABLE_PREFIX . "users
					SET username = ?, firstname = ?, lastname = ?, startingbalance = ?, weeklypayment = ?
					WHERE userid = ?
				");

				$userUpdateQuery->bind_param("sssddi", $_POST['username'], $_POST['firstname'], $_POST['lastname'], floatval($_POST['startingbalance']), floatval($_POST['weeklypayment']), $userId);

				$userUpdateQuery->execute();
				$userUpdateQuery->close();

				$templates->draw('change_other_user_details_success');
			}
			else
			{
				$templates->assign('otherUserId', intval($_GET['userid']));
				$templates->assign('otherUsername', $username);
				$templates->assign('otherStartingBalance', sprintf("%.2f", $startingBalance));
				$templates->assign('otherWeeklyPayment', sprintf("%.2f", $weeklyPayment));
				$templates->draw('change_other_user_details');
			}
		}
	}
}
elseif($_GET['do'] == 'logout')
{
	session_destroy();

	unset($coffeeUser);
	$templates->assign('loggedIn', false);

	$templates->draw('logout');
}
elseif($_GET['do'] == 'test')
{
	print_r($rota);
}

?>
