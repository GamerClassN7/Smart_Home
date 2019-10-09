<?php
class ChartScale{
	const HOUR = 'info';
	const DAY = 'warning';
	const MONTH = 'warning';
	const YEAR = 'error';
}
class ChartManager{
	function generateChart($data, $min = 0, $max = 100) {

		echo '<br>Aktuální Hodnota: '.$data[0]['value'];
		echo "<style>
		.sloupec {
			border-top: solid 2px red;
		}
		</style>";
		echo '<div class=graph>';
		echo '<div class="posuv " graf-max="'.$max.'" graf-min='.$min.'>';
		for ($valuesRow = 0; $valuesRow < count($data); $valuesRow++) {
			$row = $data[$valuesRow];

			echo '<div class="sloupec " name="sloupec" value="' . $row['value'] . '" data-toggle="tooltip" title=""></div>';
		}
		echo '</div>';
		echo '</div>';
		echo '<script src="./include/js/chartDrwer.js"></script>';
		echo 'Poslední Update: ';

		echo '<style>
		.graph {
			width: 100%;
			overflow: hidden;

			margin-top: auto;
		}


		.posuv {
			display: flex;
			height: 200px;
			background-image: url(./img/graph.png);
			padding: 20px;
			background-repeat: repeat;
			border-bottom: 1px solid black;
		}

		.sloupec {
			border-top: solid 2px blue;
			background-color: grey;
			float: left;
			margin: auto 0 0;
			display: inline-block;
			width: 1%;
		}

		</style>
		<script>
		var posuvList = document.getElementsByClassName("posuv");
		var maxHeight = posuvList[0].clientHeight;
		for (i = 0; i < posuvList.length; i++) {
			var maxPx = 0;
			var grafMax = Number(posuvList[i].getAttribute("graf-max")); //100%
			var grafMin = Number(posuvList[i].getAttribute("graf-min")); //0%
			if (grafMin == 0 && grafMax == 100) {
				var onePercent = 1;
			} else {
				var stepsBetWene = grafMax;
				if (grafMin !== 0) {
					if (grafMin < 0) {
						stepsBetWene = grafMax + Math.abs(grafMin);
					}
					if (grafMin > 0) {
						stepsBetWene = grafMax - grafMin;
					}
				}
				var onePercent = stepsBetWene / 100;
			}
			var sloupceList = posuvList[i].querySelectorAll(".sloupec");
			for (ai = 0; ai < sloupceList.length; ai++) {
				var onePxPercent = maxHeight / 100;
				var heightInPercent =
				Math.abs(sloupceList[ai].getAttribute("value")) / onePercent;
				var outputPx = onePxPercent * heightInPercent;

				sloupceList[ai].style.height = outputPx + "px";
			}
		}
		</script>';
	}

	function generateChartData(int $subDeviceId, string $periode, string $groupBy) {
		$chartData = [];

		$subDevice = SubDeviceManager::getSubDevice($subDeviceId);
		$records = RecordManager::getAllRecordForGraph($subDeviceId, $periode, $groupBy);

		$array = array_column($records, 'value');
		$arrayTime = array_column($records, 'time');
		$output = [];

		foreach ($array as $key => $value) {
			$output[$key]['y'] = $value;
			if ($subDevice['type'] == 'light'){
				if ($value > 810){
					$output[$key]['y'] = 1;
				} else {
					$output[$key]['y'] = 0;
				}
			}

			$timeStamp = new DateTime($arrayTime[$key]);
			$output[$key]['t'] = $timeStamp->format("Y-m-d") . 'T' . $timeStamp->format("H:i:s") . 'Z';
		}

		$data = json_encode($output);
		$data = $output;
		$arrayTimeStamps = array_column($records, 'time');
		foreach ($arrayTimeStamps as $key => $value) {
			$arrayTimeStamps[$key] = (new DateTime($value))->format(TIMEFORMAT);
		}

		$chartData['graphRange'] = RANGES[$subDevice['type']];
		$chartData['graphType'] = RANGES[$subDevice['type']]['graph'];
		$chartData['graphData'] = $data;

		return $chartData;
	}
}
?>
