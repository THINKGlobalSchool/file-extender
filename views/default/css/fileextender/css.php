<?php
/**
 * File-Extender CSS
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */
?>

.file-drag-upload {
	display: none;
}

.file-dropzone {
	-webkit-border-radius: 26px;
	-moz-border-radius: 26px;
	border-radius: 26px;
    margin-left: auto;
    margin-right: auto;
	margin-bottom: 10px;
	width: 200px;
	height: 150px;
}

.file-dropzone-background {
	background-image: url('<?php echo elgg_get_site_url() . 'mod/file-extender/graphics/filedropzone.png' ?>');
}

.file-dropzone-drag {
	-moz-box-shadow: 0px 0px 15px Green;
	-webkit-box-shadow: 0px 0px 15px Green;
	box-shadow: 0px 0px 15px Green;
}

.file-extender-hidden-form {
	display: none;
}

.file-drop-info {
	-webkit-border-radius: 6px;
	-moz-border-radius: 6px;
	border-radius: 6px;
	border: 1px solid #CCCCCC;
	display: block;
	margin-top: 1px;
	padding: 10px;
}

.file-drop-info .file-size {
	color: #666666;
	font-size: 1.2em;
	margin-left: 20px;
}

.file-drop-info .file-name {
	color: #333333;
	font-size: 1.2em;
	font-weight: bold;
}

.file-drop-info .file-replace {
	font-size: 1.2em;
	color: #AAAAAA;
	float: right;
}