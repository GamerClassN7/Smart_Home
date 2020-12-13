<?php
class Logout extends Template
{
	function __construct()
	{
		global $userManager;
		$userManager->logout();
		header('Location: ' . BASEDIR . 'login');
		die();
	}
}
