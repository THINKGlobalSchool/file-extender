<?php
/**
 * File-Extender Custom File Upload action
 * - Slightly customized file upload action, just outputs more data
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */

// Get variables
$title = get_input("title");
$desc = get_input("description");
$access_id = (int) get_input("access_id");
$container_guid = (int) get_input('container_guid', 0);
$group_guid = (int) get_input('group_guid', NULL);
$guid = (int) get_input('file_guid');
$tags = get_input("tags");

if ($container_guid == 0) {
	$container_guid = elgg_get_logged_in_user_guid();
}

// Check to make sure we're not already uploading to a group, and that we have a selected group
if (!elgg_instanceof(get_entity($container_guid), 'group') && $group_guid) {
	$group = get_entity($group_guid);

	// Make sure we have a group, and that the user can add items to it
	if (elgg_instanceof($group, 'group') && $group->canWriteToContainer()) {
		$container_guid = $group_guid; // We were passed a group guid
	} else {
		// Invalid group or no permissions
		register_error(elgg_echo('file-extender:grouperror'));
		forward(REFERER);
	}
}

elgg_make_sticky_form('file');

// check if upload failed
if (!empty($_FILES['upload']['name']) && $_FILES['upload']['error'] != 0) {
	register_error(elgg_echo('file:cannotload'));
	forward(REFERER);
}

// check whether this is a new file or an edit
$new_file = true;
if ($guid > 0) {
	$new_file = false;
}

if ($new_file) {
	// must have a file if a new file upload
	if (empty($_FILES['upload']['name'])) {
		$error = elgg_echo('file:nofile');
		register_error($error);
		forward(REFERER);
	}

	$file = new FilePluginFile();
	$file->subtype = "file";

	// if no title on new upload, grab filename
	if (empty($title)) {
		$title = $_FILES['upload']['name'];
	}

} else {
	// load original file object
	$file = new FilePluginFile($guid);
	if (!$file) {
		register_error(elgg_echo('file:cannotload'));
		forward(REFERER);
	}

	// user must be able to edit file
	if (!$file->canEdit()) {
		register_error(elgg_echo('file:noaccess'));
		forward(REFERER);
	}

	if (!$title) {
		// user blanked title, but we need one
		$title = $file->title;
	}
}

$file->title = $title;
$file->description = $desc;
$file->access_id = $access_id;
$file->container_guid = $container_guid;

$tags = explode(",", $tags);
$file->tags = $tags;

// we have a file upload, so process it
if (isset($_FILES['upload']['name']) && !empty($_FILES['upload']['name'])) {

	$prefix = "file/";

	// if previous file, delete it
	if ($new_file == false) {
		$filename = $file->getFilenameOnFilestore();
		if (file_exists($filename)) {
			unlink($filename);
		}

		// use same filename on the disk - ensures thumbnails are overwritten
		$filestorename = $file->getFilename();
		$filestorename = elgg_substr($filestorename, elgg_strlen($prefix));
	} else {
		$filestorename = elgg_strtolower(time().$_FILES['upload']['name']);
	}

	$mime_type = $file->detectMimeType($_FILES['upload']['tmp_name'], $_FILES['upload']['type']);
	$file->setFilename($prefix . $filestorename);
	$file->setMimeType($mime_type);
	$file->originalfilename = $_FILES['upload']['name'];
	$file->simpletype = file_get_simple_type($mime_type);

	// Open the file to guarantee the directory exists
	$file->open("write");
	$file->close();
	move_uploaded_file($_FILES['upload']['tmp_name'], $file->getFilenameOnFilestore());

	$guid = $file->save();

	// if image, we need to create thumbnails (this should be moved into a function)
	if ($guid && $file->simpletype == "image") {
		$file->icontime = time();
		
		$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
		if ($thumbnail) {
			$thumb = new ElggFile();
			$thumb->setMimeType($_FILES['upload']['type']);

			$thumb->setFilename($prefix."thumb".$filestorename);
			$thumb->open("write");
			$thumb->write($thumbnail);
			$thumb->close();

			$file->thumbnail = $prefix."thumb".$filestorename;
			unset($thumbnail);
		}

		$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
		if ($thumbsmall) {
			$thumb->setFilename($prefix."smallthumb".$filestorename);
			$thumb->open("write");
			$thumb->write($thumbsmall);
			$thumb->close();
			$file->smallthumb = $prefix."smallthumb".$filestorename;
			unset($thumbsmall);
		}

		$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
		if ($thumblarge) {
			$thumb->setFilename($prefix."largethumb".$filestorename);
			$thumb->open("write");
			$thumb->write($thumblarge);
			$thumb->close();
			$file->largethumb = $prefix."largethumb".$filestorename;
			unset($thumblarge);
		}
	}
} else {
	// not saving a file but still need to save the entity to push attributes to database
	$file->save();
}

// file saved so clear sticky form
elgg_clear_sticky_form('file');


// handle results differently for new files and file updates
if ($new_file) {
	if ($guid) {
		$message = elgg_echo("file:saved");
		system_message($message);
		add_to_river('river/object/file/create', 'create', elgg_get_logged_in_user_guid(), $file->guid);
	} else {
		// failed to save file object - nothing we can do about this
		$error = elgg_echo("file-extender:uploadfailed", array('File not saved'));
		register_error($error);
	}

	$container = get_entity($container_guid);
	if (elgg_instanceof($container, 'group')) {
		// Check XHR
		if (elgg_is_xhr()) {
			echo elgg_get_site_url() . "file/group/$container->guid/all";
		}
		forward("file/group/$container->guid/all");
	} else {
		// Check XHR
		if (elgg_is_xhr()) {
			echo elgg_get_site_url() . "file/owner/$container->username";
		}
		forward("file/owner/$container->username");
	}

} else {
	if ($guid) {
		system_message(elgg_echo("file:saved"));
	} else {
		register_error(elgg_echo("file-extender:uploadfailed", array('File not saved')));
	}

	// Check XHR
	if (elgg_is_xhr()) {
		echo $file->getURL();
	}
	forward($file->getURL());
}	
