<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extension to the basic Language library to change loading behaviours.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class IBB_Language extends CI_Language {
	/**
	 * Returns the user's selected language from the cookie, or a default language.
	 */
	public final function get() {
		// Check cookie.
		$language = get_cookie('language', TRUE);
		// If it's false, default.
		if($language == FALSE) {
			// English is default.
			return "english";
		} else {
			// Else, language.
			return strtolower($language);
		}
	}
}
/* End of file ibb_language.php */
/* Location: ./apps/libraries/ibb_language.php */