<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Login controller, which also has the registration function to save creating a new controller just
 * for a signup form.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class Logout extends IBB_Controller {
	/**
	 * Load my precious login form, oh great one.
	 */
	function index() {
		if($this->member->is_logged_in() == "GUEST") {
			redirect('/login');
		} else {
			$this->member->logout();
			redirect('/home');
		}
	}
}

/* End of file: login.php */
/* Location: ./apps/controllers/ */
