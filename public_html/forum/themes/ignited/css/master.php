<?php
/**
 * -------------------------------------------------------------------
 * CSS COMPRESSION UTILITY
 * -------------------------------------------------------------------
 * Why does this exist?
 *
 * Some shared hosts (and even some normal servers) don't enable mod_deflate or mod_gzip by default. These two
 * Apache modules are critical in reducing file sizes of files across transfers. PHP files are easy to
 * compress without these modules, however CSS/JS files are a bit different. You either write a ton of
 * .htaccess rules, which again aren't guaranteed to work on every server, or you pass the css files manually
 * through the PHP parser.
 *
 * How is this used?
 *
 * The $config->board->compression->css variable defines whether or not this is used. All it does is change
 * the header so that this file is loaded instead of main.css. This enables output buffering and compresses
 * the contents of the file.
 *
 * Does this impact performance?
 *
 * Load times will sharply decrease overall, however server load may increase.
 */
// Is zlib already enabled? If so, double-compression causes an error.
if(ini_get('zlib.output_compression') == FALSE) {
	// Enable output buffering, pass this through the GZIP handler.
	ob_start('ob_gzhandler');
}
// Send out a fake text/css MIME type in a header. This stops the browser thinking it's a PHP script.
header("Content-type: text/css");
// Put in some cache-control headers.
header('Cache-Control: max-age=290304000, public');
// Include other CSS files as normal.
echo "@charset 'utf-8'; /* Prevents issues. */\n\n";
include('reset.css');
echo "\n\n";
include('board.css');
// Only close the bufferer if needed.
if(ini_get('zlib.output_compression') == FALSE) {
	// Close the output bufferer.
	ob_end_flush();
}
?>