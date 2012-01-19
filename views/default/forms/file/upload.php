<?php
/**
 * File-Extender JS
 * - Override for regular forms/file/upload
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */

// once elgg_view stops throwing all sorts of junk into $vars, we can use 
$title = elgg_extract('title', $vars, '');
$desc = elgg_extract('description', $vars, '');
$tags = elgg_extract('tags', $vars, '');
$access_id = elgg_extract('access_id', $vars, ACCESS_DEFAULT);
$container_guid = elgg_extract('container_guid', $vars);

if (!$container_guid) {
	$container_guid = elgg_get_logged_in_user_guid();
}

$guid = elgg_extract('guid', $vars, null);

// If we have a guid we're editing
if ($guid) {
	$file_label = elgg_echo("file:replace");
	$submit_label = elgg_echo('save');

	// Hidden file input
	$file_hidden = elgg_view('input/hidden', array(
		'name' => 'file_guid', 
		'value' => $guid
	));
} else {
	$file_label = elgg_echo("file:file");
	$submit_label = elgg_echo('upload');
}

$file_input = elgg_view('input/file', array(
	'name' => 'upload',
	'class' => 'file-drag-upload',
));

$title_label = elgg_echo('title');
$title_input = elgg_view('input/text', array(
	'class' => 'file-drop-title',
	'name' => 'title', 
	'value' => $title
));

$description_label = elgg_echo('description');
$description_input = elgg_view('input/longtext', array(
	'name' => 'description',
	'value' => $desc
));

$tags_label = elgg_echo('tags');
$tags_input = elgg_view('input/tags', array(
	'name' => 'tags',
	'value' => $tags
));

$access_label = elgg_echo('access');
$access_input = elgg_view('input/access', array(
	'name' => 'access_id', 
	'value' => $access_id
));

$container_hidden = elgg_view('input/hidden', array(
	'name' => 'container_guid',
	'value' => $container_guid
));

// Get logged in user groups
$groups = elgg_get_entities_from_relationship_count(array(
	'type' => 'group',
	'relationship' => 'member',
	'relationship_guid' => elgg_get_logged_in_user_guid(),
	'inverse_relationship' => FALSE,
	'full_view' => FALSE,
));

if (count($groups)) {
	$groups_array = array('' => elgg_echo('file-extender:none'));

	// Add each group to group array for dropdown
	foreach ($groups as $g) {
		$groups_array[$g->guid] = $g->name;
	}
	
	$group_label = elgg_echo('file-extender:group');

	$group_select = elgg_view('input/dropdown', array(
		'name' => 'group_guid',
		// 'value' => $group->getURL(), @TODO show current group if viewing a group?
		'options_values' => $groups_array,
	));
	
	$group_input = <<<HTML
		<div>
			<label>$group_label</label>
			$group_select
		</div><br />
HTML;
}

$submit_input = elgg_view('input/submit', array(
	'value' => $submit_label,
	'id' => 'submit-file',
));

$content = <<<HTML
	<div class='file-dropzone file-dropzone-background'>
	</div>
	$file_input
	<div class='file-extender-hidden-form'>
		<div>
			<label>$title_label</label><br />
			$title_input
		</div><br />
		<div>
			<label>$description_label</label><br />
			$description_input
		</div><br />
		<div>
			<label>$tags_label</label><br />
			$tags_input
		</div><br />
		<div>
			<label>$access_label</label>
			$access_input
		</div><br />
		$group_input
		<div class='elgg-foot'>
			$container_hidden
			$file_hidden
			$submit_input
		</div>
	</div>
HTML;

echo $content;
