<?php
/**
 * File-Extender language file
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */

$english = array(
	'file-extender:group' => 'Post to group',
	'file-extender:none' => 'None',
	'file-extender:toomanyfiles' => 'You can only upload one file at a time',
	'file-extender:filetoolarge' => 'File size must be less than %s',
	'file-extender:grouperror' => 'You cannot post files to this group',
	'file-extender:replace' => '(Drop new file to replace)',

	// More useful upload failed errors
	'file-extender:uploadfailed' => 'Sorry; we could not save your file. (%s)',
	'file-extender:uploadfailedxhr' => 'Sorry; we could not save your file. (XHR)', 
);

add_translation('en',$english);
