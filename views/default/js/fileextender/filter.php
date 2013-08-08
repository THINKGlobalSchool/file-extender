<?php
/**
 * File-Extender Filter input JS
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2013
 * @link http://www.thinkglobalschool.com/
 * 
 */
?>
//<script>
elgg.provide('elgg.file_filter');

// File type filter init
elgg.file_filter.init = function() {
	// Bind change to select element
	$('#file-extender-type-filter').live('change', elgg.file_filter.change);
}

// Change handler for select element
elgg.file_filter.change = function(event) {
	// Re-locate to generated type url
	window.location.href = elgg.file_filter.getTypeURL($(this).val(), $(this).data('page_owner_guid'));
}

// Helper function: generates file type url based on type
elgg.file_filter.getTypeURL = function(type) {
	var url = location.protocol + '//' + location.host + location.pathname;

	if (type != "all") {
		url += "?md_type=simpletype&tag=" + encodeURIComponent(type);
	}

	return url;
}

elgg.register_hook_handler('init', 'system', elgg.file_filter.init);