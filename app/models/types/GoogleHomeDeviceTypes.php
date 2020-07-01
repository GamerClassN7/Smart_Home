<?php
class GoogleHomeDeviceTypes {
	/*const AirConditioningUnit	   = 'action.devices.types.AC_UNIT';
	const AirFreshener 			   = 'action.devices.types.AIRFRESHENER';
	const AirPurifier             = 'action.devices.types.AIRPURIFIER';
	const Awning 					   = 'action.devices.types.AWNING';
	const Bathtub 					   = 'action.devices.types.BATHTUB';
	const Bed 					 	   = 'action.devices.types.BED';
	const Blender				 	   = 'action.devices.types.BLENDER';
	const Blinds					   = 'action.devices.types.BLINDS';
	const Boiler					   = 'action.devices.types.BOILER';
	const Camera					   = 'action.devices.types.CAMERA';
	const CarbonMonoxideDetector	= 'action.devices.types.CARBON_MONOXIDE_DETECTOR';
	const Charger						= 'action.devices.types.CHARGER';
	const Closet						= 'action.devices.types.CLOSET';
	const CoffeeMaker					= 'action.devices.types.COFFEE_MAKER';
	const Cooktop						= 'action.devices.types.COOKTOP';
	const Curtain						= 'action.devices.types.CURTAIN';
	const Dehumidifier				= 'action.devices.types.DEHUMIDIFIER';
	const Dehydrator					= 'action.devices.types.DEHYDRATOR';
	const Dishwasher					= 'action.devices.types.DISHWASHER';
	const Door							= 'action.devices.types.DOOR';
	const Drawer						= 'action.devices.types.DRAWER';
	const Dryer							= 'action.devices.types.DRYER';
	const Fan							= 'action.devices.types.FAN';
	const Faucet						= 'action.devices.types.FAUCET';
	const Fireplace					= 'action.devices.types.FIREPLACE';
	const Fryer                   = 'action.devices.types.FRYER';
	const GarageDoor              = 'action.devices.types.GARAGE';
	const Gate							= 'action.devices.types.GATE';
	const Grill							= 'action.devices.types.GRILL';
	const Heater						= 'action.devices.types.HEATER';
	const Hood							= 'action.devices.types.HOOD';
	const Humidifier					= 'action.devices.types.HUMIDIFIER';
	const Kettle						= 'action.devices.types.KETTLE';
	const Light							= 'action.devices.types.LIGHT';
	const Lock							= 'action.devices.types.LOCK';
	const MediaRemote					= 'action.devices.types.REMOTECONTROL';
	const Mop							= 'action.devices.types.MOP';
	const Mower							= 'action.devices.types.MOWER';
	const Microwave					= 'action.devices.types.MICROWAVE';
	const Multicooker					= 'action.devices.types.MULTICOOKER';
	const Network						= 'action.devices.types.NETWORK';

	const Oven							= 'action.devices.types.OVEN';
	const Pergola						= 'action.devices.types.PERGOLA';
	const PetFeeder					= 'action.devices.types.PETFEEDER';
	const PressureCooker				= 'action.devices.types.PRESSURECOOKER';
	const Radiator						= 'action.devices.types.RADIATOR';
	const Refrigerator				= 'action.devices.types.REFRIGERATOR';
	const Router						= 'action.devices.types.ROUTER';
	const Scene							= 'action.devices.types.SCENE';
	const Sensor						= 'action.devices.types.SENSOR';
	const SecuritySystem				= 'action.devices.types.SECURITYSYSTEM';
	const SettopBox					= 'action.devices.types.SETTOP';
	const Shutter						= 'action.devices.types.SHUTTER';
	const Shower						= 'action.devices.types.SHOWER';
	const SmokeDetector				= 'action.devices.types.SMOKE_DETECTOR';
	const SousVide						= 'action.devices.types.SOUSVIDE';
	const Sprinkler					= 'action.devices.types.SPRINKLER';
	const StandMixer					= 'action.devices.types.STANDMIXER';
	const Switch						= 'action.devices.types.SWITCH';
	const Television					= 'action.devices.types.TV';
	const Thermostat					= 'action.devices.types.THERMOSTAT';
	const Vacuum						= 'action.devices.types.VACUUM';
	const Valve							= 'action.devices.types.VALVE';
	const Washer						= 'action.devices.types.WASHER';
	const WaterHeater					= 'action.devices.types.WATERHEATER';
	const WaterPurifier				= 'action.devices.types.WATERPURIFIER';
	const WaterSoftener				= 'action.devices.types.WATERSOFTENER';
	const Window						= 'action.devices.types.WINDOW';
	const YogurtMaker					= 'action.devices.types.YOGURTMAKER';*/

	private static $actionWordBook = [
		'control-light' 			=> 'action.devices.types.LIGHT',
		'control-socket' 			=> 'action.devices.types.OUTLET',
		'control-temp'				=> 'action.devices.types.THERMOSTAT',
		'control-media'			=> 'action.devices.types.REMOTE',
	];

	private static $traidWordBook = [
		'on/off' 					=> 'action.devices.traits.OnOff',
		'temp_cont' 				=> 'action.devices.traits.TemperatureSetting',
		'vol_cont'					=> 'action.devices.traits.Volume',
	];

	private static $commandWordBook = [
		'action.devices.commands.OnOff' => 'on/off',
		'action.devices.commands.ThermostatTemperatureSetpoint' => 'temp_cont',
		'action.devices.commands.ThermostatSetMode' => 'temp_cont',
		'action.devices.commands.setVolume' => 'vol_cont',
	];

	private static $attributeWordBook = [
		'temp_cont' 				=> [
			'availableThermostatModes' => 'off,heat',
			'thermostatTemperatureUnit' => 'C'
		],
		'vol_cont' 				=> [
			'volumeCanMuteAndUnmute' => false,
			'volumeDefaultPercentage' => 6,
			'volumeMaxLevel' => 100,
			'levelStepSize' => 2,
			'commandOnlyVolume' => true,
		],
		'media_status'=> [
			'transportControlSupportedCommands' => [
				"NEXT",
				"PREVIOUS",
				"PAUSE",
				"STOP",
				"RESUME",
			],
		],
		'media_apps' => [
			"availableApplications" => [
				[
					"key" => "kodi",
					"names" => [
						"name_synonym" => [
							"Kodi",
						],
						"lang" => "en"
					],
				],
			],
		],
		'media_inputs' => [
			"availableApplications" => [
				"key" => "pc",
				"names" => [
					"name_synonym"  => [
						"PC",
					],
					"lang"  => "en",
				],

			],
		],
	];

	static function getAction($deviceType){
		if (!isset(self::$actionWordBook[$deviceType])) return;
		return self::$actionWordBook[$deviceType];
	}

	static function getTraid($subDeviceType){
		if (!isset(self::$traidWordBook[$subDeviceType])) return;
		return self::$traidWordBook[$subDeviceType];
	}

	static function getType($subDeviceCommand){
		if (!isset(self::$commandWordBook[$subDeviceCommand])) return;
		return self::$commandWordBook[$subDeviceCommand];
	}

	static function getAttribute($subDeviceType){
		if (!isset(self::$attributeWordBook[$subDeviceType])) return;
		return self::$attributeWordBook[$subDeviceType];
	}

	static function getQueryJson($deviceType, $type){
		return self::$wordBook[$type];
	}
}
