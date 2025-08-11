jQuery(document).ready(function($){
    var mediaUploader;
    
    $('#bkoimadhk_upload_button').click(function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create a new media uploader
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select or Upload Marker Icon',
            button: {
                text: 'Use this image'
            },
            multiple: false // Restrict to one image
        });

        // When an image is selected, set the field value and preview the image
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#bkoimadhk_map_checkout_custom_marker_icon').val(attachment.url);
            $('#bkoimadhk_map_checkout_custom_marker_icon').next('img').remove();
            $('#bkoimadhk_map_checkout_custom_marker_icon').after('<br><img src="' + attachment.url + '" alt="Marker Icon" style="max-width: 100px; height: auto;" />');
        });
        
        mediaUploader.open();
    });
});
