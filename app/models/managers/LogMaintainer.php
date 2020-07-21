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
<<<<<<< HEAD
				$this->cleaningDir ($dir . $file . "/", $seconds);
=======
				$this->cleaningDir ($path . $file . "/", $seconds);
>>>>>>> 1a448663f05f2b4ad7456a89d50312be302cd494
			}
		}
	}

	public function purge ($days) {
		$seconds = $days * 86400;
		$this->cleaningDir ('../logs/', $seconds);
	}
<<<<<<< HEAD
}
=======
}
>>>>>>> 1a448663f05f2b4ad7456a89d50312be302cd494
