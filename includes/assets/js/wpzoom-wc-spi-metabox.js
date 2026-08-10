window.WpzoomWcSecondaryImage = {

    l10n: ( window.wp && window.wp.i18n ) ? {
	    setThumbnail: wp.i18n.__( 'Set featured image' ),
	    saving: wp.i18n.__( 'Saving...' ),
	    error: wp.i18n.__( 'Could not set that as the thumbnail image. Try a different attachment.' ),
	    done: wp.i18n.__( 'Done' )
    } : { setThumbnail: '', saving: '', error: '', done: '' },

    // The media modal runs on the edit screen, the legacy uploader popup does not.
    getPostId: function(){
	    return jQuery('#post_ID').val() || window.post_id;
    },

    setThumbnailHTML: function(html, id, post_type){
	    jQuery('.inside', '#' + post_type + '-' + id).html(html);
    },

    setThumbnailID: function(thumb_id, id, post_type){
	    var field = jQuery('input[value=_' + post_type + '_' + id + '_thumbnail_id]', '#list-table');
	    // .size() was removed in jQuery 3.0.
	    if ( field.length > 0 ) {
		    jQuery('#meta\\[' + field.attr('id').match(/[0-9]+/) + '\\]\\[value\\]').text(thumb_id);
	    }
    },

    removeThumbnail: function(id, post_type, nonce){
	    jQuery.post(ajaxurl, {
		    action:'set-' + post_type + '-' + id + '-thumbnail', post_id: WpzoomWcSecondaryImage.getPostId(), thumbnail_id: -1, _ajax_nonce: nonce, cookie: encodeURIComponent(document.cookie)
	    }, function(str){
		    if ( str == '0' ) {
			    alert( WpzoomWcSecondaryImage.l10n.error );
		    } else {
			    WpzoomWcSecondaryImage.setThumbnailHTML(str, id, post_type);
		    }
	    }
	    );
    },


    setAsThumbnail: function(thumb_id, id, post_type, nonce){
	    var $link = jQuery('a#' + post_type + '-' + id + '-thumbnail-' + thumb_id);
		$link.data('thumbnail_id', thumb_id);
	    $link.text( WpzoomWcSecondaryImage.l10n.saving );
	    jQuery.post(ajaxurl, {
		    action:'set-' + post_type + '-' + id + '-thumbnail', post_id: WpzoomWcSecondaryImage.getPostId(), thumbnail_id: thumb_id, _ajax_nonce: nonce, cookie: encodeURIComponent(document.cookie)
	    }, function(str){
		    var win = window.dialogArguments || opener || parent || top;
		    $link.text( WpzoomWcSecondaryImage.l10n.setThumbnail );
		    if ( str == '0' ) {
			    alert( WpzoomWcSecondaryImage.l10n.error );
		    } else {
			    $link.show();
			    $link.text( WpzoomWcSecondaryImage.l10n.done );
			    $link.fadeOut( 2000, function() {
				    jQuery('tr.' + post_type + '-' + id + '-thumbnail').hide();
			    });
			    win.WpzoomWcSecondaryImage.setThumbnailID(thumb_id, id, post_type);
			    win.WpzoomWcSecondaryImage.setThumbnailHTML(str, id, post_type);
		    }
	    }
	    );
    }
}