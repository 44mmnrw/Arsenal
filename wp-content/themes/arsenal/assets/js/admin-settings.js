/**
 * Arsenal Settings Page - Admin JavaScript
 */

jQuery(document).ready(function($) {
	'use strict';
	
	// Загрузчик изображений
	$('.arsenal-upload-button').on('click', function(e) {
		e.preventDefault();
		
		var button = $(this);
		var targetId = button.data('target');
		var container = button.closest('.arsenal-image-upload');
		
		var mediaUploader = wp.media({
			title: 'Выберите изображение',
			button: {
				text: 'Использовать это изображение'
			},
			multiple: false
		});
		
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			
			$('#' + targetId).val(attachment.url);
			container.find('.arsenal-preview-image').attr('src', attachment.url).show();
			container.find('.arsenal-remove-button').show();
		});
		
		mediaUploader.open();
	});
	
	// Удаление изображения
	$('.arsenal-remove-button').on('click', function(e) {
		e.preventDefault();
		
		var button = $(this);
		var container = button.closest('.arsenal-image-upload');
		var input = container.find('input[type="hidden"]');
		
		input.val('');
		container.find('.arsenal-preview-image').attr('src', '').hide();
		button.hide();
	});
});
