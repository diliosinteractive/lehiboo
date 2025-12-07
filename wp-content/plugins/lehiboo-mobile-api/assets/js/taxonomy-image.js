/**
 * Taxonomy Image Upload Handler
 */
jQuery(document).ready(function($) {
    var frame;

    // Upload button click
    $('#upload-thematique-image').on('click', function(e) {
        e.preventDefault();

        // If frame already exists, reopen it
        if (frame) {
            frame.open();
            return;
        }

        // Create media frame
        frame = wp.media({
            title: 'Sélectionner une image pour la thématique',
            button: {
                text: 'Utiliser cette image'
            },
            multiple: false
        });

        // When image is selected
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();

            $('#thematique_image').val(attachment.id);
            $('#thematique-image-preview')
                .attr('src', attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url)
                .show();
            $('#remove-thematique-image').show();
        });

        frame.open();
    });

    // Remove button click
    $('#remove-thematique-image').on('click', function(e) {
        e.preventDefault();

        $('#thematique_image').val('');
        $('#thematique-image-preview').attr('src', '').hide();
        $(this).hide();
    });

    // Reset on add new term form submit
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.data && settings.data.indexOf('action=add-tag') !== -1) {
            $('#thematique_image').val('');
            $('#thematique-image-preview').attr('src', '').hide();
            $('#remove-thematique-image').hide();
        }
    });
});
