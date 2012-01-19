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
	elgg_register_simplecache_view('js/fileextender/css');
	elgg_register_css('elgg.fileextender', $extender_css);

	// Extend file composer view
	elgg_extend_view('file/composer', 'file-extender/init', 1);

	// Unregister file/upload action
	elgg_unregister_action("file/upload");

	// Register our own action
	$action_path = elgg_get_plugins_path() . 'file-extender/actions/file';
	elgg_register_action("file/upload", "$action_path/upload.php");
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