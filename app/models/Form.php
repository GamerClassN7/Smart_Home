<?php
/**
* [InputTypes datatype for input types]
*/
class InputTypes
{
	const TEXT = 'text';
	const NUMBER = 'number';
	const COLOR = 'color';
	const CHECK = 'checkbox';
	const BUTTON = 'button';
	const DATE = 'date';
	const DATETIME = 'datetime';
	const SUBMIT = 'submit';
	const HIDEN = 'hidden';
	const EMAIL = 'email';
}
/**
* [Form Form Generator Class]
*/
class Form {

	public $formContent = "";
	private $formName;
	private $formId;
	private $method;
	private $action;

	/**
	 * [__construct description]
	 * @param String $name   [description]
	 * @param String $id     [description]
	 * @param String $method [description]
	 * @param String $action [description]
	 */
	function __construct(String $name, String $id, String $method, String $action) {
		if ($name != "") {
			$this->formName = 'name="'.$name.'"';
		}
		if ($id != "") {
			$this->formId = 'id="'.$id.'"';
		}
		if ($method != "") {
			$this->method = 'method="'.$method.'"';
		}
		if ($action != "") {
			$this->$action = 'action="'.$action.'"';
		}
	}
	/**
	 * [addInput description]
	 * @param String     $type    Type of input element (text, number, color,checkbox, button, date, datetime, submit)
	 * @param String     $name    name of element
	 * @param String     $id      id of element
	 * @param String     $label   label of element
	 * @param String     $value   value of element
	 * @param boolean    $require require selector toggle
	 * @param boolean    $enabled enable selector toggle
	 */
	function addInput(String $type, String $name, String $id, String $label, String $value, Bool $require = false, Bool $enabled = true){
		$this->formContent .= '<div class="field">';
		if ($label != "") {
			$this->formContent .= '<div class="label">'.$label.'</div>';
		}
		$this->formContent .= '<input class="input" type="'.$type.'" name="'.$name.'" value="'.$value.'" ' . ($enabled ? '' : 'disabled')  . ($require ? '' : 'required') .'>';
		$this->formContent .= '</div>';
	}

	//TODO: add Group support
	/**
	 * [addSelect description]
	 * @param String  $name     name of element
	 * @param String  $id       id of element
	 * @param String  $label    label of element
	 * @param Array   $data     array of options [value => valueName]
	 * @param boolean $multiple multiple selector toggle
	 * @param boolean $enabled  enable selector toggle
	 */
	function addSelect(String $name, String $id, String $label, Array $data, Bool $multiple = false, Bool $require = false, Bool $enabled = true){
		$this->formContent .= '<div class="field">';
		if ($label != "") {
			$this->formContent .= '<div class="label">'.$label.'</div>';
		}
		$this->formContent .= '<select class="input"' . ($multiple ? '' : 'multiple') . ($enabled ? '' : 'disabled') . ($require ? '' : 'required') .'>';
		foreach ($data as $value => $text) {
			$this->formContent .= '<option value="' . $value . '">' . $text . '</option>';
		}
		$this->formContent .= '</select>';
		$this->formContent .= '</div>';
	}

	/**
	 * [addTextarea description]
	 * @param String     $name     name of element
	 * @param String     $id       id of element
	 * @param String     $label    label of element
	 * @param String     $value   value of element
	 * @param boolean    $enabled enable selector toggle
	 */
	function addTextarea(String $name, String $id, String $label, Array $value, Bool $require = false, Bool $enabled = true){
		$this->formContent .= '<div class="field">';
		if ($label != "") {
			$this->formContent .= '<div class="label">'.$label.'</div>';
		}
		$this->formContent .= '<textarea class="input"'  . ($enabled ? '' : 'disabled')  . ($require ? '' : 'required') .'>';
		$this->formContent .= $value;
		$this->formContent .= '</textarea>';
		$this->formContent .= '</div>';
	}

	/**
	 * [render function whitch dysplay generated form]
	 */
	function render(){
		self::addInput(InputTypes::SUBMIT, 'formSubmit', '', '', 'Submit');
		$form = '<form '.$this->formName.$this->formId.$this->method.$this->action.'">';
		$form .= $this->formContent;
		$form .= '</form>';
		echo 	$form;
	}
}
