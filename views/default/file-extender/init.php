<?php
/**
 * File-Extender Composer Init
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */
?>
<script>
$(document).ready(function() {
	elgg.fileextender.destroy(); // Destroy first
	elgg.fileextender.init(); // Re-init
});
</script>