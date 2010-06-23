<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Login controller, which also has the registration function to save creating a new controller just
 * for a signup form.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class Register extends Controller {
	
	function index() {
		$this->parser->add('view_register')->parse();
	}
	function create_user() {
		
		$this->form_validation->set_rules('display_name','Display name','alpha|trim|required|unique[user.display_name]|callback_name_check');
		$this->form_validation->set_rules('email','E-mail','trim|required|valid_email|unique[user.email]');
		$this->form_validation->set_rules('password','Password','trim|required|min_length[6]|max_length[32]');
		$this->form_validation->set_rules('password_confirm','Password confirmation','trim|required|matches[password]');
		
		if($this->form_validation->run() == FALSE) {
			$this->parser->add('view_register');
		} else {
			$this->load->model('membership');
			
			if($query = $this->membership->create_user())
			{
				$this->parser->add('register_success');
			}
			else
			{
				$this->parser->add('register_form');
			}
		}
	}
	function name_check($str) {
		$dk = "/\bdk|dk\b/i";
		if(preg_match($dk, $str)) {
			$this->form_validation->set_message('name_check', 'The %s cannot contain \'dk\' since it means you\'re Danish');
			return false;
		} else {
			return true;
		}
	}
}

/* End of file: auth.php */
/* Location: ./apps/controllers/ */
