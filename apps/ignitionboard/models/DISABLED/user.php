<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User model for handling all sorts of user-related stuff. Singleton pattern and all.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class User {
	/**
	 * Storage variable for the user's ID number in the database. Defaults to -1.
	 *
	 * @var integer	The user's ID number.
	 */
	public $id = -1;
	/**
	 * Constructor.
	 * 
	 * Sets up the user model with some data.
	 *
	 * @param integer	$id		The ID of the user.
	 */
	public function __construct($id = -1) {
		// Set the ID.
		$this->id = $id;
	}
	/**
	 * Updates the user's record in the database with a value for a field.
	 *
	 * @param string	$data	Array of data to update. Same as you'd pass to a normal update() function.
	 */
	function update($data) {
		// Get a refererence of the CI object.
		$CI =& get_instance();
		// Execute query.
		$CI->db->where('id', $this->id)->update('user', $data);
		// Done.
		return TRUE;
	}
	/**
	 * Returns a user's ID number from a given email address. Returns -1 if the ID couldn't be found.
	 *
	 * @param string	$email	The email address to look up.
	 */
	function get_id_from_email($email) {
		// Get a refererence of the CI object.
		$CI =& get_instance();
		// Query it.
		$CI->db->select('id')
				 ->from('user')
				 ->where('email', $email)
				 ->limit(1);
		// Results?
		$query = $CI->db->get();
		if($query->num_rows() > 0) {
			// Return the ID.
			$id = $query->row();
			return $id->id;
		} else {
			// Return -1.
			return -1;
		}
	}
	function create() {
		$this->db->insert('user', array(
			'email' => $this->input->post('email', TRUE),
			'password' => $this->security->encode_password(
					$this->input->post('password', TRUE),
					$this->input->post('email', TRUE)),
			'display_name' => $this->input->post('display_name', TRUE)
		));
		return TRUE;
	}
}
/* End of file: member.php */
/* Location: ./apps/models/member.php */