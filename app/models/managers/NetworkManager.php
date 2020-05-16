<?php
/**
 *
 */
class NetvorkManager
{

	function __construct()
	{
		// code...
	}

	function validateIp($ip = '0.0.0.0'){
		if (!filter_var($ip, FILTER_VALIDATE_IP)){
			return false;
		}
	}
}
