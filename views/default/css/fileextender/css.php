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