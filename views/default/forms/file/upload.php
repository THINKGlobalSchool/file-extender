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
if (file_extender_is_ie()) {
	echo elgg_view('forms/file/upload_ie', $vars);
	return;
}

elgg_load_js('elgg.fileextender');
elgg_load_css('elgg.fileextender');

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
	$submit_class = 'file-editing';

	// Hidden file input
	$file_hidden = elgg_view('input/hidden', array(
		'name' => 'file_guid', 
		'value' => $guid
	));

	$file = get_entity($guid);
	$filename = $file->originalfilename;
	$filesize = file_calculate_size($file->size());
	$filereplace = elgg_echo('file-extender:replace');

	$drop_zone = <<<HTML
		<div id='file-dropzone-div'>
			<span class='file-drop-info'>
				<span class='file-name'>$filename</span>
				<span class='file-size'>$filesize</span>
				<spac class='file-replace'>$filereplace</span>
			</span>
		</div>
HTML;
} else {
	$file_label = elgg_echo("file:file");
	$submit_label = elgg_echo('upload');
	$hidden_class = 'file-extender-hidden-form';
	$drop_zone = "<div id='file-dropzone-div' class='file-dropzone file-dropzone-background'></div>";
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


if (!elgg_instanceof(elgg_get_page_owner_entity(), 'group') && !$guid) {
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
}

$submit_input = elgg_view('input/submit', array(
	'value' => $submit_label,
	'id' => 'submit-file',
	'class' => "elgg-button elgg-button-submit $submit_class",
));

$upload_notice = elgg_echo('file-extender:upload_notice');
$upload_limit = elgg_echo('file-extender:upload_limit', array(file_calculate_size(elgg_get_ini_setting_in_bytes("post_max_size"))));

$content = <<<HTML
	<span class="mbm message warning">$upload_notice</span>
	<span class="mbm elgg-text-help">$upload_limit</span>
	$drop_zone
	<div class='file-browse'>
		<div class='file-upload-or'>OR</div>
		<center>$file_input</center>
	</div>
	<div class='$hidden_class'>
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
