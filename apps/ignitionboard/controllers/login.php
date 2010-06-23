<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Login controller, which also has the registration function to save creating a new controller just
 * for a signup form.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class Login extends IBB_Controller {
	/**
	 * Load my precious login form, oh great one.
	 */
	function index() {
		if($this->member->is_logged_in() != "MEMBER") {
			// Give the user a challenge, store it in their session.
			$data['CHALLENGE'] = $this->security->generate_challenge('login');
			// And for the actual login prompt, make a seperate key.
			$challenge = substr(md5($this->security->generate_string(32, FALSE)), 0, 8);
			$this->session->set_serverdata('login_hash_challenge', $challenge);
			// Put this in the data too.
			$data['KEY'] = $challenge;
			// Parse login form bro!
			$this->parser->add('view_login', $data)->parse();
		} else {
			redirect('/home');
		}
	}
	function validate() {
		// Validate the login. Challenge good?
		if($this->security->check_challenge('challenge', 'login')) {
			// Good. Now try the whole login form.
			if($this->member->login()) {
				// Yay! Unset challenges.
				$this->security->unset_challenge('login');
				$this->session->unset_serverdata('login_hash_challenge');
				// Perform the login redirect.
				//$this->member->login_redirect();
			} else {
				echo "failure";
			}
		} else {
			// Fail.
			show_404();
		}
	}
}

/* End of file: login.php */
/* Location: ./apps/controllers/ */
