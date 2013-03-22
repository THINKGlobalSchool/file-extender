<?php
/**
 * File-Extender JS
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010
 * @link http://www.thinkglobalschool.com/
 * 
 */
?>
//<script>
elgg.provide('elgg.fileextender');

elgg.fileextender.post_max_size = <?php echo ini_get("post_max_size"); ?>;

elgg.fileextender.init = function() {

	// Change handler for the old-school browse input
	$(document).delegate('.file-browse .elgg-input-file', 'change', function(event){
		// Hide the file input and container
	});

	// Init fileupload
	$('.file-drag-upload').fileupload({
        dataType: 'json',
		dropZone: $('#file-dropzone-div'),
		fileInput: $('input.file-drag-upload'),
		drop: function (e, data) {
			// Remove drag class
			$(e.originalEvent.target).removeClass('file-dropzone-drag');

			// Make sure we're not dropping multiple files
			if (data.files.length > 1) {
				elgg.register_error(elgg.echo('file-extender:toomanyfiles'));
				e.preventDefault();
			}

			// Check file size
			if (data.files[0].size > elgg.fileextender.post_max_size) {
				var size = elgg.fileextender.bytesToSize(elgg.fileextender.post_max_size);

				elgg.register_error(elgg.echo('file-extender:filetoolarge', [size]));
				e.preventDefault();
			}
		},
		add: function (e, data) {

			// Get the dropped file
			var file = data.files[0];

			// Fade in the rest of the form
			$('.file-extender-hidden-form').fadeIn('slow');
			
			// Fade out the file input container
			$('.file-browse').toggle();

			// Set title on the form
			$('.file-drop-title').val(file.name);

			// Set file data on the input, to be used with click event later
			$('.file-drag-upload').data('data', data);

			// Remove dropzone classes and display info
			var $div = $('#file-dropzone-div');
			$div.removeClass('file-dropzone file-dropzone-background');

			var $drop_name = $(document.createElement('span'));
			$drop_name.addClass('file-name');
			$drop_name.html(file.name);

			var $drop_size = $(document.createElement('span'));
			$drop_size.addClass('file-size');
			$drop_size.html(elgg.fileextender.calculateSize(file.size));

			var $drop_info = $(document.createElement('span'));
			$drop_info.addClass('file-drop-info');
			$drop_info.append($drop_name);
			$drop_info.append($drop_size);

			$div.html($drop_info);
		},
		dragover: function (e, data) {
			// Add fancy dragover class
			$(e.originalEvent.target).addClass('file-dropzone-drag');
		}
    });

	// Click handler for the file submit button
	$('#submit-file').live('click', elgg.fileextender.submitClick);
}

// Destroy the file uploader and unbind any events
elgg.fileextender.destroy = function() {
	if ($('.file-drag-upload').fileupload()) {
		$('.file-drag-upload').fileupload('destroy');
	}
	$('#submit-file').die();
}

// Calculate file size for display
elgg.fileextender.calculateSize = function(size) {
    if (typeof size !== 'number') {
        return '';
    }
    if (size >= 1000000000) {
        return (size / 1000000000).toFixed(2) + ' GB';
    }
    if (size >= 1000000) {
        return (size / 1000000).toFixed(2) + ' MB';
    }
    return (size / 1000).toFixed(2) + ' KB';
}

// Click handler for the submit button 
elgg.fileextender.submitClick = function(event) {
	var data = $('.file-drag-upload').data('data');

	// If we're editing a file, check to see if a new file has been added..
	// data will equal 'undefined' if not.. in that case we're just updating
	// the files title/desc/tags/etc.. go ahead with a normal submit
	if ($(this).hasClass('file-editing') && data == undefined) {
		return true;
	}

	// Store the button
	var $button = $(this);

	// Show a little spinner
	$(this).replaceWith("<div id='file-upload-spinner' class='elgg-ajax-loader'></div>");

	// Make sure tinymce inputs have set the text
	if (typeof(tinyMCE) != 'undefined') {
		tinyMCE.triggerSave();
	}

	// Get file data (set in the add callback of the fileuploader)
	var data = $('.file-drag-upload').data('data');
	

	// Returns an object, with these fancy callbacks
	var jqXHR = $('.file-drag-upload').fileupload('send',{files: data.files})
		.done(function (result, textStatus, jqXHR) {
			// Success/done check elgg status's
			if (result.status != -1) {
				// Display success
				elgg.system_message(result.system_messages.success);

				// Prevent the 'are you sure you want to leave' popup
				window.onbeforeunload = function() {};

				// Good to go, forward to output
				window.location = result.output;
			} else {
				// There was an error, display it
				elgg.register_error(result.system_messages.error);

				// Enable the button (try again?)
				$('#file-upload-spinner').replaceWith($button);
			}
		})
    	.fail(function (jqXHR, textStatus, errorThrown) {
			// If we're here, there was an error making the request
			// or we got some screwy response.. display an error and log it for debugging
			elgg.register_error(elgg.echo('file-extender:uploadfailedxhr'));
			console.log('fail');
			console.log(errorThrown);
			console.log(textStatus);
			console.log(jqXHR);

			// Enable the button
			$('#file-upload-spinner').replaceWith($button);
		})
    	.always(function (result, textStatus, jqXHR) {
			// Just keeping this here for future use/testing
		});

		event.preventDefault();
}

/**
 * Convert number of bytes into human readable format
 *
 * @param integer bytes     Number of bytes to convert
 * @param integer precision Number of digits after the decimal separator
 * @return string
 */
elgg.fileextender.bytesToSize = function(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};

elgg.register_hook_handler('init', 'system', elgg.fileextender.init);