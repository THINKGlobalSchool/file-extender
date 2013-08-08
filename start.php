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

	$filter_js = elgg_get_simplecache_url('js', 'fileextender/filter');
	elgg_register_simplecache_view('js/fileextender/filter');
	elgg_register_js('elgg.file_filter', $filter_js);
	
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

	// Hook into menu:page registration
	elgg_register_plugin_hook_handler('register', 'menu:page', 'file_extender_page_menu_handler', 9999);
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

	elgg_load_js('elgg.file_filter');

	$page_type = $page[0];
	switch ($page_type) {
		case 'owner':
			file_extender_handle_owner_page();
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
		case 'group':
			file_extender_handle_owner_page();
			break;
		case 'all':
			file_extender_handle_all_page();
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

/**
 * Hook into page menu registration to modify items
 */
function file_extender_page_menu_handler($hook, $type, $items, $params) {
	// Remove file type menu items
	foreach ($items as $idx => $item) {
		if (strpos($item->getName(), 'file:') === 0) {
		//	unset($items[$idx]);
		}
	}

	return $items;
}

function file_extender_is_ie() {
	if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
		return true;
	} else {
		return false;
	}
}

/**
 * Alternate all page handler content
 */
function file_extender_handle_all_page() {
	file_extender_register_type_menu_items();

	elgg_register_title_button();

	// Get input
	$md_type = 'simpletype';
	// avoid reflected XSS attacks by only allowing alnum characters
	$file_type = preg_replace('[\W]', '', get_input('tag'));

	if ($file_type) {
		elgg_push_breadcrumb(elgg_echo("file:type:$file_type"));
		$title = elgg_echo('all') . ' ' . elgg_echo("file:type:$file_type");
	} else {
		elgg_push_breadcrumb(elgg_echo('file'));
		$title = elgg_echo('file:all');
	}

	$limit = get_input("limit", 10);

	$content = elgg_view_menu('file_type_filter');

	$params = array(
		'type' => 'object',
		'subtype' => 'file',
		'limit' => $limit,
		'full_view' => FALSE
	);

	if ($file_type) {
		$params['metadata_name'] = $md_type;
		$params['metadata_value'] = $file_type;
		$content .= elgg_list_entities_from_metadata($params);
	} else {
		$content .= elgg_list_entities($params);
	}

	if (!$content) {
		$content = elgg_echo('file:none');
	}

	$sidebar = elgg_view('file/sidebar');

	$body = elgg_view_layout('content', array(
		'filter_context' => 'all',
		'content' => $content,
		'title' => $title,
		'sidebar' => $sidebar,
	));

	echo elgg_view_page($title, $body);
}

/**
 * Alternate owner page handler content
 */
function file_extender_handle_owner_page() {
	file_extender_register_type_menu_items(elgg_get_page_owner_guid());
	// access check for closed groups
	group_gatekeeper();

	$owner = elgg_get_page_owner_entity();
	if (!$owner) {
		forward('file/all');
	}

	// Get file type inputs
	$md_type = 'simpletype';
	// avoid reflected XSS attacks by only allowing alnum characters
	$file_type = preg_replace('[\W]', '', get_input('tag'));

	// breadcrumbs
	elgg_push_breadcrumb(elgg_echo('file'), "file/all");

	if (elgg_instanceof($owner, 'user')) {
		elgg_push_breadcrumb($owner->name, "file/owner/$owner->username");
	} else {
		elgg_push_breadcrumb($owner->name, "file/group/$owner->guid/all");
	}

	if ($file_type) {
		elgg_push_breadcrumb(elgg_echo("file:type:$file_type"));
		// title
		$type_string = elgg_echo("file:type:$file_type");
		$title = elgg_echo('file:list:title', array($owner->name, $friend_string, $type_string));
	} else {
		elgg_push_breadcrumb(elgg_echo('all'));
		$title = elgg_echo("file:user", array($owner->name));
	}

	elgg_register_title_button();

	$params = array();

	if ($owner->guid == elgg_get_logged_in_user_guid()) {
		// user looking at own files
		$params['filter_context'] = 'mine';
	} else if (elgg_instanceof($owner, 'user')) {
		// someone else's files
		// do not show select a tab when viewing someone else's posts
		$params['filter_context'] = 'none';
	} else {
		// group files
		$params['filter'] = ' ';
	}

	$content = elgg_view_menu('file_type_filter');

	$options = array(
		'type' => 'object',
		'subtype' => 'file',
		'container_guid' => $owner->guid,
		'limit' => 10,
		'full_view' => false,
	);

	if ($file_type) {
		$options['metadata_name'] = $md_type;
		$options['metadata_value'] = $file_type;
		$content .= elgg_list_entities_from_metadata($options);
	} else {
		$content .= elgg_list_entities($options);
	}

	if (!$content) {
		$content = elgg_echo("file:none");
	}

	$sidebar = elgg_view('file/sidebar');

	$params['content'] = $content;
	$params['title'] = $title;
	$params['sidebar'] = $sidebar;

	$body = elgg_view_layout('content', $params);

	echo elgg_view_page($title, $body);
}

/**
 * Registers file types to the file type filter menu (modified from: file_get_type_cloud())
 *
 * @param int       $container_guid The GUID of the container of the files
 * @return void
 */
function file_extender_register_type_menu_items($container_guid = "", $friends = false) {
	$container_guids = $container_guid;

	elgg_register_tag_metadata_name('simpletype');
	$options = array(
		'type' => 'object',
		'subtype' => 'file',
		'container_guids' => $container_guids,
		'threshold' => 0,
		'limit' => 10,
		'tag_names' => array('simpletype')
	);
	$types = elgg_get_tags($options);

	// register menu items here
	if (!$types) {
		return true;
	}

	$all = new stdClass;
	$all->tag = "all";

	$types_options = array(
		'all' => elgg_echo('all')
	);

	foreach ($types as $type) {
		$types_options[$type->tag] = elgg_echo("file:type:$type->tag");
	}

	$filter_dropdown = elgg_view('input/filter_dropdown', array(
		'label' => elgg_echo('file-extender:filter'),
		'options_values' => $types_options,
		'id' => 'file-extender-type-filter',
		'value' => get_input('tag', false),
	));

	elgg_register_menu_item('file_type_filter', array(
		'name' => 'file_extender:types',
		'text' => $filter_dropdown,
		'href' => false
	));
}
