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
	
	// Register jquery ui widget (for jquery file upload)
	$js = elgg_get_simplecache_url('js', 'jquery_ui_widget');
	elgg_register_simplecache_view('js/jquery_ui_widget');
	elgg_register_js('jquery.ui.widget', $js);
	
	// Register JS File Upload
	$j_js = elgg_get_simplecache_url('js', 'jquery_file_upload');
	elgg_register_simplecache_view('js/jquery_file_upload');
	elgg_register_js('jquery-file-upload', $j_js);

	// Register JS jquery.iframe-transport (for jquery-file-upload)
	$j_js = elgg_get_simplecache_url('js', 'jquery_iframe_transport');
	elgg_register_simplecache_view('js/jquery_iframe_transport');
	elgg_register_js('jquery.iframe-transport', $j_js);

	// Register CSS
	$extender_css = elgg_get_simplecache_url('css', 'fileextender/css');
	elgg_register_simplecache_view('css/fileextender/css');
	elgg_register_css('elgg.fileextender', $extender_css);

	// Register a page handler, so we can have nice URLs
	elgg_unregister_page_handler('file');

	elgg_register_page_handler('file', 'file_extender_page_handler');

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

/**
 * Dispatches file pages.
 * URLs take the form of
 *  All files:       file/all
 *  User's files:    file/owner/<username>
 *  Friends' files:  file/friends/<username>
 *  View file:       file/view/<guid>/<title>
 *  New file:        file/add/<guid>
 *  Edit file:       file/edit/<guid>
 *  Group files:     file/group/<guid>/all
 *  Download:        file/download/<guid>
 *
 * Title is ignored
 *
 * @param array $page
 * @return bool
 */
function file_extender_page_handler($page) {

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	$file_dir = elgg_get_plugins_path() . 'file/pages/file';

	$page_type = $page[0];
	switch ($page_type) {
		case 'owner':
			include "$file_dir/owner.php";
			break;
		case 'friends':
			include "$file_dir/friends.php";
			break;
		case 'read': // Elgg 1.7 compatibility
			register_error(elgg_echo("changebookmark"));
			forward("file/view/{$page[1]}");
			break;
		case 'view':
			set_input('guid', $page[1]);
			include "$file_dir/view.php";
			break;
		case 'add':
			include "$file_dir/upload.php";
			break;
		case 'edit':
			set_input('guid', $page[1]);
			include "$file_dir/edit.php";
			break;
		case 'search':
			include "$file_dir/search.php";
			break;
		case 'group':
			include "$file_dir/owner.php";
			break;
		case 'all':
			include "$file_dir/world.php";
			break;
		case 'download':
			set_input('guid', $page[1]);
			include "$file_dir/download.php";
			break;
		default:
			return false;
	}
	return true;
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

	// Replace the first occurance (the file icon's) link with a direct download link
	$download_url = elgg_get_site_url() . "file/download/$file->guid";

	$return = (substr_replace($return, $download_url, strpos($return, $file->getURL()), strlen($file->getURL())));

	return $return;
}

function file_extender_is_ie() {
	if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
		return true;
	} else {
		return false;
	}
}