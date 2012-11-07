<?php
/**
 * File-Extender start.php
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */

// Register init
elgg_register_event_handler('init', 'system', 'file_extender_init');

// Init
function file_extender_init() {
	// Register JS
	$extender_js = elgg_get_simplecache_url('js', 'fileextender/extender');
	elgg_register_simplecache_view('js/fileextender/extender');
	elgg_register_js('elgg.fileextender', $extender_js);
	
	// Register JS File Upload
	$j_js = elgg_get_simplecache_url('js', 'jquery_file_upload');
	elgg_register_simplecache_view('js/jquery_file_upload');
	elgg_register_js('jQuery-File-Upload', $j_js);

	// Register CSS
	$extender_css = elgg_get_simplecache_url('css', 'fileextender/css');
	elgg_register_simplecache_view('css/fileextender/css');
	elgg_register_css('elgg.fileextender', $extender_css);

	// Don't override anything if we're using IE
	if (!file_extender_is_ie()) {
		// Extend file composer view
		elgg_extend_view('file/composer', 'file-extender/init', 1);

		// Unregister file/upload action
		elgg_unregister_action("file/upload");

		// Register our own action
		$action_path = elgg_get_plugins_path() . 'file-extender/actions/file';
		elgg_register_action("file/upload", "$action_path/upload.php");
	}	

	// Register a hook handler to post process file views
	elgg_register_plugin_hook_handler('view', 'object/file', 'file_extender_object_view_handler');
}

// Calculate file size for display
function file_calculate_size($size) {
    if (!is_numeric($size)) {
        return '';
    }
    if ($size >= 1000000000) {
        return number_format(($size / 1000000000), 2) . ' GB';
    }
    if ($size >= 1000000) {
        return number_format(($size / 1000000), 2) . ' MB';
    }
    return number_format(($size / 1000), 2) . ' KB';
}

/**
 * Post process file object views to replace file icon link
 *
 * @param unknown_type $hook
 * @param unknown_type $type
 * @param unknown_type $return
 * @param unknown_type $params
 * @return unknown
 */
function file_extender_object_view_handler($hook, $type, $return, $params) {
	$file = $params['vars']['entity'];

	// Replace all first occurances of the file link with a direct download link
	$download_url = elgg_get_site_url() . "file/download/$file->guid";

	$return = str_replace($file->getURL(), $download_url, $return);

	return $return;
}

function file_extender_is_ie() {
	if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
		return true;
	} else {
		return false;
	}
}