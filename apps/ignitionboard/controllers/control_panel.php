<?php
/**
 * User control panel, and all that comes with it.
 * 
 * @author Daniel Yates & Dale Emasiri
 */

class Control_Panel extends Controller {
	
	function user() {
		if($this->logged_in()) {
			$this->parser->add('usercp');
		} else {
			die('not logged in');
		}
	}
	
	function logged_in() {
		if($this->session->serverdata('logged_in') != '1') {
			echo 'You do not have access! Please '.anchor('auth','login').' here.';
			die();
		} else {
			return true;
		}
	}
}
