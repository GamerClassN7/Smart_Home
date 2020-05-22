<?php

class Debugger {
	private static $flags = [];
	private static $tracker = [];
	private static $backtrace;

	public static function trackStart ($key){
		self::$tracker[$key] = array(
			'start' => microtime(true),
			'stop' => 0
		);
	}

	public static function trackStop ($key){
		self::$tracker[$key]['stop'] = microtime(true);
	}

	public static function showTracker(){
		$ret = '';

		foreach(self::$tracker as $key => $track){
			$ret .= $key.': '.number_format($track['stop']-$track['start'], 4, '.', ' ') .' ms<br>';
		}

		return $ret;
	}

	public static function flag ($text) {
		$flag = array(
			'time' => microtime(true)*1000,
			'flag' => $text
		);
		self::$flags[] = $flag;
	}

	public static function showFlags($asHtml = true){
		$ret = '';
		$nl = $asHtml ? "<br>\n": "\n";

		$size = count(self::$flags);
		for($i=0; $i<$size - 1; $i++){
			$ret .= self::$flags[$i]['flag'];
			$ret .= '-';
			$ret .= self::$flags[$i+1]['flag'];
			$ret .= ' '. number_format(self::$flags[$i+1]['time'] - self::$flags[$i]['time'], 0, '.', ' ') .' ms';
			$ret .= $nl;
		}
		if($size > 1){
			$ret .= 'TOTAL: '.number_format(self::$flags[$size-1]['time'] - self::$flags[0]['time'], 0, '.' ,' ') .' ms';
		}
		return $ret;
	}

	public static function debug($backtrace){
		self::$backtrace = $backtrace;
	}

	public static function showDebug(){
		return self::$backtrace;
	}
}
?>
