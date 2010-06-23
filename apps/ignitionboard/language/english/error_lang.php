<?php
/**
 |---------------------------------------------------------------
 | LANGUAGE FILE: ERRORS
 | 
 | CATEGORY: GENERIC/PHP
 |---------------------------------------------------------------
 */
$lang['error_generic'] =
		'An unknown error occurred.';
$lang['error_function_not_yet_implemented'] =
		'The \'%f\' function in the \'%c\' class is not yet implemented.';
$lang['error_function_obsolete'] =
		'The \'%f\' function in the \'%c\' class is considered obsolete, and cannot be used.
		Instead, use the \'%nf\' function in the \'%nc\' class.';
$lang['error_server_teapot'] =
		'This HTCPCP server is a teapot.';
/**
 |---------------------------------------------------------------
 | CATEGORY: LOADER
 |---------------------------------------------------------------
 */
$lang['error_loader_object_not_found'] = 'The object \'%s\' could not be created, as it does not exist.';
/**
 |---------------------------------------------------------------
 | CATEGORY: DATABASE
 |---------------------------------------------------------------
 */
$lang['error_database_table_initialized'] = 'The \'%s\' field cannot be added to the table as it has already been initialized.';
$lang['error_database_field_not_found'] = 'The \'%f\' field does not exist in the \'%c\' model.';
/**
 |---------------------------------------------------------------
 | CATEGORY: SESSIONS
 |---------------------------------------------------------------
 */
$lang['error_session_ownership'] =
		'There was an error verifying your ownership of the current session.';
$lang['error_session_update'] =
		'There was an error updating your session data.';
/**
 |---------------------------------------------------------------
 | CATEGORY: SECURITY
 |---------------------------------------------------------------
 */
$lang['error_security_missing_salt'] =
		'There was an error encoding or decoding the password.'; // Be very generic. This is logged anyhow.
$lang['error_security_password_length'] =
		'The given password is too short to decode.';
$lang['error_security_file_not_writeable'] =
		'The cryptography settings file is not writeable.';
/**
 |---------------------------------------------------------------
 | CATEGORY: FILE
 |---------------------------------------------------------------
 */
$lang['error_file_not_writeable'] =
		'The file at \'%s\' is not writeable.';
/**
 |---------------------------------------------------------------
 | CATEGORY: PARSER
 |---------------------------------------------------------------
 */
$lang['error_template_not_found'] =
		'The template file at \'%s\' does not exist.';
$lang['error_template_css_not_found'] =
		'The master CSS file for the \'%s\' theme does not exist.';
$lang['error_template_css_not_compressible'] =
		'The master CSS compression utility for the \'%s\' theme does not exist.';
$lang['error_template_js_not_compressible'] =
		'The master JS compression utility for the \'%s\' theme does not exist.';
/**
 |---------------------------------------------------------------
 | CATEGORY: USER
 |---------------------------------------------------------------
 */
$lang['error_user_login_incorrect'] =
		'Your login details are incorrect.';
$lang['error_large_post_count'] =
		'Your post count is over 9000. Such awesomeness is beyond the board\'s comprehension.';

/* End of file error_lang.php */
/* Location: ./apps/language/english/error_lang.php */