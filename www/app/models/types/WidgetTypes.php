<?php

class WidgetTypes {
	const VALUE 	= 0;
	const ICON 		= 1;
	const BUTTON 	= 2;
	const SWITH  	= 3;
	const RANGE 	= 4;
	const CUSTOM	= 5;

	private $types = [
		self::VALUE => [
			'name' => 'value',
			'active' => false
		],
		self::ICON => [
			'name' => 'icon',
			'active' => false
		],
		self::BUTTON => [
			'name' => 'button',
			'active' => true
		],
		self::SWITH => [
			'name' => 'switch',
			'active' => true
		],
		self::RANGE => [
			'name' => 'range',
			'active' => true
		],
		self::CUSTOM => [
			'name' => 'custom',
			'active' => true
		],
	];

	public static function getName($type){
		return self::$types[$type];
	}

	public static function isActive($type){
		return isset(self::$types[$type]) && self::$types[$type]['active'];
	}
}
