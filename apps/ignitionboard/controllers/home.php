<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Home controller is the board "index", displays the categories and their boards, latest post data,
 * statistics, etc.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class Home extends IBB_Controller {
	/**
	 * Main view of the Home controller. Nothing fancy here, just parse the view_categories template.
	 */
	function index() {
		$user = $this->content->user('-1');
		echo "User State: " . $this->authentication->user->login->status . "<br />";
		echo "User State (alt #1): " . $user->login->status . "<br />";
		$user = $this->content->user('SELF');
		echo "User State (alt #2): " . $user->login->status . "<br />";
		$user = $this->content->user('2');
		echo "User State (alt #2): " . $user->login->status . "<br />";
		echo	form_open('home/login') .
				form_input('login_email', 'daniel@danielyates.me.uk') .
				form_input('login_password', hash('ripemd160', 'penis' . hash('ripemd160', 'password'))) .
				form_checkbox('login_remember', '1', FALSE) .
				form_input('login_challenge', 'penis') .
				form_submit("login", "login test") .
				form_close();
	}
	function login() {
		if($this->authentication->login('post') == FALSE) {
			$this->authentication->logout(TRUE);
		} else {
			echo "Logged in.";
		}
	}
}

/* End of file: login.php */
/* Location: ./apps/controllers/ */
