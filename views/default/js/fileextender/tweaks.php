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
elgg.provide('elgg.fileextender_tweaks');

// File type filter init
elgg.fileextender_tweaks.init = function() {
	// Bind change to select element
	$('#file-extender-type-filter').live('change', elgg.fileextender_tweaks.change);

	$('.elgg-content > .elgg-image-block > .elgg-image > img').css('cursor', 'pointer');

	// Bind icon click to file download in file full view
	$(document).on('click', '.elgg-content > .elgg-image-block > .elgg-image > img', function(event) {
		window.open($('.elgg-menu-item-download > a').attr('href'), '_blank');
	});
}

// Change handler for select element
elgg.fileextender_tweaks.change = function(event) {
	// Re-locate to generated type url
	window.location.href = elgg.fileextender_tweaks.getTypeURL($(this).val(), $(this).data('page_owner_guid'));
}

// Helper function: generates file type url based on type
elgg.fileextender_tweaks.getTypeURL = function(type) {
	var url = location.protocol + '//' + location.host + location.pathname;

	if (type != "all") {
		url += "?md_type=simpletype&tag=" + encodeURIComponent(type);
	}

	return url;
}

elgg.register_hook_handler('init', 'system', elgg.fileextender_tweaks.init);