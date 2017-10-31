jQuery(document).ready( function(){
	/*
	 * Loading Media upload for custom widget
	 */
	var uploadparent = 0;
	function media_upload( button_class) {
		var _custom_media = true; //, 
		_orig_send_attachment = wp.media.editor.send.attachment;
		jQuery('body').on('click',button_class, function(e) {
		uploadparent = jQuery(this).closest('p');
			var button_id ='#'+jQuery(this).attr('id');
			var self = jQuery(button_id);
			var send_attachment_bkp = wp.media.editor.send.attachment;
			var button = jQuery(button_id);
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				if ( _custom_media  ) {
				   uploadparent.find('.image-input').val(attachment.url);
				   uploadparent.find('.image-input').trigger('change');
				   uploadparent.find('span.image-preview > img').attr('src', attachment.url);
				} else {
					return _orig_send_attachment.apply( button_id, [props, attachment] );
				}
			}
			wp.media.editor.open(button);
			return false;
		});
	}
	media_upload( '.upload_image_button');
	
	/*
	 * The Serious Widget mode switcher
	 */
	if (jQuery('body.wp-customizer').length) {
		// we're in the customizer
		var widget_selector = 'li[id*=cryoutseriouswidget] p select.trigger'; // apply to all select triggers, not just mode
		var form_selector = 'div.form';			
	} else {
		// we're in appearance > widgets
		var widget_selector = '#presentation-page-area p select.trigger'; // apply to all select triggers, not just mode
		var form_selector = 'form';	
	}
	jQuery(document.body).on('change', widget_selector, function() {
		// this takes care of sub-options triggers
		jQuery(this).parents(form_selector).find('p').hide(0);
		jQuery(this).parents(form_selector).find('p.mode').show(0); // always!
		jQuery(this).parent().parent('p').show(0);
		var selected = jQuery(this).val();
		if (selected) {
			jQuery(this).parents(form_selector).find('p.'+selected).show(0);
			jQuery(this).parents(form_selector).find('p.'+selected+' select.trigger').trigger('change');
		}
	});
	// trigger only mode selector on page load
	jQuery('#presentation-page-area p.mode select.trigger').trigger('change');
	// customizer trigger is inline
	
}); // ready


jQuery(document).ajaxSuccess(function(e, xhr, settings) {
	var widget_id_base = 'cryoutseriouswidget';
	
	if(settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) {
		// trigger only mode selector on widget save
		jQuery('#presentation-page-area p.mode select.trigger').trigger('change');
	}
});

