<?php

define('SCRIPT', 'login.php');

include('include.php');

if($_POST['username'] != null && $_POST['password'] != null)
{
	/* Authenticate user with LDAP */

	// connect
	$ldap = ldap_connect(LDAP_HOST);

	if(!$ldap)
	{
		die('Cannot connect to authentication server; unable to verify credentials.');
	}

	$__username = ldap_escape($_POST['username']);

	// verify password
	$bind = @ldap_bind($ldap, 'uid=' . $__username . ',' . LDAP_USER_DN, $_POST['password']);

	if($bind === true)
	{
		$loginQuery = $database->prepare("
			SELECT userid
			FROM " . TABLE_PREFIX . "users
			WHERE username = ?
		");

		$loginQuery->bind_param("s", $__username);
		$loginQuery->execute();
		$loginQuery->bind_result($userId);
		$loginQuery->fetch();
		$loginQuery->close();

		if($userId)
		{
			$updateLastLoginQuery = $database->prepare("
				UPDATE " . TABLE_PREFIX . "users
				SET lastlogin = ?
				WHERE userid = ?
			");

			$currentTime = time();

			$updateLastLoginQuery->bind_param("ii", $currentTime, $userId);
			$updateLastLoginQuery->execute();

			$_SESSION['userId'] = $userId;

			header("Location: index.php");
		}
		else
		{
			// User is new
			$templates->draw('login_user_not_registered');
		}
	}
	else
	{
		$templates->assign('badCredentials', true);
		$templates->draw('login');
	}
}
else
{
	$templates->draw('login');
}

?>
