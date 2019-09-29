<?php
/**
*
*/
class Utilities
{
	function cleanString($text) {
		$utf8 = array(
			'/[áàâãªä]/u'   =>   'a',
			'/[ÁÀÂÃÄ]/u'    =>   'A',
			'/[ÍÌÎÏ]/u'     =>   'I',
			'/[íìîï]/u'     =>   'i',
			'/[ěéèêë]/u'     =>   'e',
			'/[ĚÉÈÊË]/u'     =>   'E',
			'/[óòôõºö]/u'   =>   'o',
			'/[ÓÒÔÕÖ]/u'    =>   'O',
			'/[úùûü]/u'     =>   'u',
			'/[ÚÙÛÜ]/u'     =>   'U',
			'/Š/'     		=>   'S',
			'/š/'     		=>   's',
			'/Č/'     		=>   'C',
			'/č/'     		=>   'c',
			'/ř/'     		=>   'r',
			'/Ř/'     		=>   'R',
			'/Ý/'     		=>   'Y',
			'/ý/'     		=>   'y',
			'/ç/'           =>   'c',
			'/Ç/'           =>   'C',
			'/ñ/'           =>   'n',
			'/Ñ/'           =>   'N',
			'/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
			'/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
			'/[“”«»„]/u'    =>   ' ', // Double quote
			'/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
		);
		return preg_replace(array_keys($utf8), array_values($utf8), $text);
	}

	function stringInsert($str,$insertstr,$pos)
	{
		$str = substr($str, 0, $pos) . $insertstr . substr($str, $pos);
		return $str;
	}

	/**
	 * [generateGraphJson description]
	 * @param  string $type    [line/bar]
	 * @param  array  $data    [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */

	function generateGraphJson(string $type = 'line', array $data = [], array $options = []){
		$array = [
			'type' => $type,
			'data' => [
				'datasets' => [
					[
						'data' => $data
					]
				]
			],
			'options' => [
				'scales' => [
					'xAxes' => [
						[
							'type' => 'time',
							'distribution' => 'linear',
						]
					],
					'yAxes' => [
						[
							'ticks' => [
								'min' => $options['min'],
								'max' => $options['max'],
								'steps' => $options['scale']
							]
						]
					]
				],
				'legend' => [
					'display' => false
				],
				'tooltips' => [
					'enabled' => true
				],
				'hover' => [
					'mode' => true
				]
			]
		];
		return json_encode($array, JSON_PRETTY_PRINT);
	}
}
