<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

require('config.php');

include_once('./classes/CoffeeUser.php');
include_once('./classes/TemplateEngine.php');
include_once('./functions/Functions.php');

/*
* Useful functions
*/

/**
 * function ldap_escape
 * @author Chris Wright
 * @version 2.0
 * @param string $subject The subject string
 * @param bool $dn Treat subject as a DN if TRUE
 * @param string|array $ignore Set of characters to leave untouched
 * @return string The escaped string
 */
function ldap_escape($subject, $dn = FALSE, $ignore = NULL) {

    // The base array of characters to escape
    // Flip to keys for easy use of unset()
    $search = array_flip($dn ? array('\\', ',', '=', '+', '<', '>', ';', '"', '#') : array('\\', '*', '(', ')', "\x00"));

    // Process characters to ignore
    if (is_array($ignore)) {
        $ignore = array_values($ignore);
    }
    for ($char = 0; isset($ignore[$char]); $char++) {
        unset($search[$ignore[$char]]);
    }

    // Flip $search back to values and build $replace array
    $search = array_keys($search); 
    $replace = array();
    foreach ($search as $char) {
        $replace[] = sprintf('\\%02x', ord($char));
    }

    // Do the main replacement
    $result = str_replace($search, $replace, $subject);

    // Encode leading/trailing spaces in DN values
    if ($dn) {
        if ($result[0] == ' ') {
            $result = '\\20'.substr($result, 1);
        }
        if ($result[strlen($result) - 1] == ' ') {
            $result = substr($result, 0, -1).'\\20';
        }
    }

    return $result;
}

/*
* Database
*/

$database = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);

if($database->connect_errno)
{
	die("<p>Failed to connect to database.</p>");
}

/*
* Templates
*/

$templates = new TemplateEngine();

/*
* Statistics
*/

$totalSpentQuery = $database->query("
	SELECT SUM(amount) AS total
	FROM " . TABLE_PREFIX . "transactions
	WHERE amount < 0
");

$result = $totalSpentQuery->fetch_assoc();
$totalSpent = $result['total'];
$coffeeAndDonutPersonMonths = -$totalSpent / 6;
$templates->assign('cupsOfCoffee', round($coffeeAndDonutPersonMonths * 20));
$templates->assign('doughnuts', round($coffeeAndDonutPersonMonths * 4));

/*
* Session
*/

session_start();

if(($_SESSION['userId'] == null) && SCRIPT != 'login.php')
{
	header("Location: login.php");
}
else
{
	if(SCRIPT != 'login.php')
	{
		$loginQuery = $database->prepare("
			SELECT users.userid, users.username, users.firstname, users.lastname, users.lastlogin, users.startingbalance, administrators.userid AS administratoruserid
			FROM " . TABLE_PREFIX . "users AS users
			LEFT JOIN " . TABLE_PREFIX . "administrators AS administrators
			ON users.userid = administrators.userid
			WHERE users.userid = ?
		");

		$loginQuery->bind_param("i", $_SESSION['userId']);
		$loginQuery->bind_result($userId, $username, $firstName, $lastName, $lastLogin, $startingBalance, $administratorUserId);
		$loginQuery->execute();
		$loginQuery->fetch();
		$loginQuery->close();

		if($userId)
		{
			$coffeeUser = new CoffeeUser($userId, $username, $firstName, $lastName, $lastLogin, $startingBalance, ($administratorUserId != 0) ? true : false);

			$templates->assign('loggedIn', true);
			$templates->assign('firstName', $coffeeUser->getFirstName());

			// get user's next rota
			$rotaQuery = $database->prepare("
				SELECT day, month, year, type
				FROM " . TABLE_PREFIX . "schedule
				WHERE userid = ? AND mailsent = 0
				ORDER BY year ASC, month ASC, day ASC
				LIMIT 1
			");

			$rotaQuery->bind_param("i", $userId);
			$rotaQuery->bind_result($__day, $__month, $__year, $__type);
			$rotaQuery->execute();
			$rotaQuery->fetch();
			$rotaQuery->close();
			$rota = array($__day, $__month, $__year, $__type);

			$templates->assign('rota', $rota);
		}
		else
		{
			die("Corrupt session data");
		}
	}
}

/*
* Set title
*/

$titles = array(
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Coffee Management Evolved",
	"Cup/Mug Exception",
	"Crunchy Marshmallow Epidemic",
	"Can't Masticate Espresso",
	"Coffee Machine Exhausted",
	"Cappuccino Mocha Extravaganza"
);

$templates->assign('title', $titles[array_rand($titles)]);

?>
