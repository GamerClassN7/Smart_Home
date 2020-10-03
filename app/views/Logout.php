<?php
class Logout extends Template
{
	function __construct()
	{
		$userManager = new UserManager ();
		$userManager->logout();
		header('Location: ' . BASEURL . 'login');
		die();
	}
}
