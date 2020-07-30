<?php
class LogMaintainer
{
	private function cleaningDir ($dir, $seconds) {
		$todayFileName = date ("Y-m-d").'.log';
		$logFiles = scandir ($dir);
		foreach ($logFiles as $key => $file) {
			if (in_array ($file,array (".", "..", ".gitkeep", $todayFileName)))
			{
				continue;
			}
			if (!is_dir($dir . $file)) {
				if (strtotime(str_replace(".log", "", $file)) < (strtotime("now") - $seconds)) {
					unlink ($dir . $file);
				}
			} else {
				$this->cleaningDir ($dir . $file . "/", $seconds);
			}
		}
	}

	public function purge ($days) {
		$seconds = $days * 86400;
		$this->cleaningDir ('../logs/', $seconds);
	}

	public static function getStats(){
		$stats = array(
			'ERROR' => 0,
			'WARNING' => 0,
			'EXEPTION' => 0,
			'INFO' => 0,
		);

		$result = array();
		$result = self::logFinder ('../logs/', $result);

		foreach ($result as $path => $files) {
			foreach ($files as $file) {
		
				# code...
				$matches = array();

				$re = '/\[(?:warning|error|info)\]/';
				$str = file_get_contents($path . $file);
				preg_match_all($re, $str, $matches);
				if (count($matches[0]) == 0) continue;
				
				foreach ($matches[0] as $match) {
					switch($match){
						case '[error]': $stats['ERROR']++; break;
						case '[warning]': $stats['WARNING']++; break;
						case '[exeption]': $stats['EXEPTION']++; break;
						default: $stats['INFO']++;  break;
					}
				}
			}
		}

		return $stats;
	}

	private static function logFinder ($dir, $result) {
		$logFiles = scandir ($dir);
		foreach ($logFiles as $key => $file) {
			if (in_array ($file,array (".", "..", ".gitkeep")))
			{
				continue;
			}
			if (!is_dir($dir . $file)) {
				$result[$dir][] = $file;
			} else {
				$result = self::logFinder ($dir . $file . "/", $result);
			}
		}
		return $result;
	}
}
