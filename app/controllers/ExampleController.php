<?php

class ExampleController extends Controller{

	public function index(){
		$this->view->title = 'Example title';
		$this->view->render('example.phtml');
	}

	public function subpage(){
		echo 'subpage';
	}

}
