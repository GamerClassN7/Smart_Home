<?php
/**
*
*/
class Utilities
{
	static function cleanString($text) {
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

	static function stringInsert($str,$insertstr,$pos)
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

	static function generateGraphJson(string $type = 'line', array $data = [], array $options = []){
		$array = [
			'type' => $type,
			'data' => [
				'datasets' => [
					[
						'data' => $data,
						'borderColor' => "#d4def7",
						'backgroundColor' => "#d4def7"
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

	static function ago( $datetime )
	{
		$interval = date_create('now')->diff( $datetime );
		$suffix = ( $interval->invert ? ' ago' : '' );
		if ( $v = $interval->y >= 1 ) return self::pluralize( $interval->m, 'month' ) . $suffix;
		if ( $v = $interval->d >= 1 ) return self::pluralize( $interval->d, 'day' ) . $suffix;
		if ( $v = $interval->h >= 1 ) return self::pluralize( $interval->h, 'hour' ) . $suffix;
		if ( $v = $interval->i >= 1 ) return self::pluralize( $interval->i, 'minute' ) . $suffix;
		return self::pluralize( $interval->s, 'second' ) . $suffix;
	}

	static function pluralize( $count, $text )
	{
		return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
	}

	static function checkOperator($value1, $operator, $value2) {
		switch ($operator) {
			case '<': // Less than
			return $value1 < $value2;
			case '<=': // Less than or equal to
			return $value1 <= $value2;
			case '>': // Greater than
			return $value1 > $value2;
			case '>=': // Greater than or equal to
			return $value1 >= $value2;
			case '==': // Equal
			return ($value1 == $value2);
			case '===': // Identical
			return $value1 === $value2;
			case '!==': // Not Identical
			return $value1 !== $value2;
			case '!=': // Not equal
			case '<>': // Not equal
			return $value1 != $value2;
			case '||': // Or
			case 'or': // Or
			return $value1 || $value2;
			case '&&': // And
			case 'and': // And
			return $value1 && $value2;
			case 'xor': // Or
			return $value1 xor $value2;
			default:
			return FALSE;
		} // end switch
	}
}
