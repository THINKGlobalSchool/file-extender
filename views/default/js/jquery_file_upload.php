<?php
/**
 * Group-Extender jQuery File Upload simplecache view
 * 
 * @package Group-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */
$js_path = elgg_get_config('path');
$fileupload = "{$js_path}mod/file-extender/vendors/jQuery-File-Upload/jquery.fileupload.js";
$iframe_transport = "{$js_path}mod/file-extender/vendors/jQuery-File-Upload/jquery.iframe-transport.js";

include $fileupload;
include $iframe_transport;