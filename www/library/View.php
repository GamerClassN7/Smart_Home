<?php

class View{
	protected $_content = "";
	protected $_layout = 'default';

	protected $_viewEnabled = true;
	protected $_layoutEnabled = true;

	protected $_data = array();

	public function disableLayout(){
	  $this->_layoutEnabled = false;
	}

	public function enableLayout(){
	  $this->_layoutEnabled = false;
	}

	public function setLayout($layout){
		$this->_layout = $layout;
	}

	public function disableView(){
	  $this->_viewEnabled = false;
	}

	public function __set($key, $value){
		$this->_data[$key] = $value;
	}

	public function __get($key){
		if(array_key_exists($key, $this->_data)){
			return $this->_data[$key];
		}

		return null;
	}

	public function content(){
		return $this->_content;
	}

	public function render($template){
		if($template && $this->_viewEnabled){
			$this->_fetchContent($template);
		}

		if($this->_layoutEnabled){
			include('../app/views/layouts/' . $this->_layout . '.phtml');
		} else {
			echo $this->_content;
		}
	}

	protected function _fetchContent($template){
		ob_start();

		include('../app/views/templates/' . $template);

		$this->_content = ob_get_clean();
	}
}
