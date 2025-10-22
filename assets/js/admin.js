jQuery(function($){

    if ($('body').hasClass('post-type-attachment')) {

        // Insert AI button next to Bulk Select
        const $bulkButton = $('.select-mode-toggle-button');
        if ($bulkButton.length && $('.prompt2image-btn').length === 0) {
            const $aiButton = $('<button/>', {
                type: 'button',
                class: 'button prompt2image-btn',
                text: '🪄 Generate AI Image'
            });
            $bulkButton.after($aiButton);

            // Show modal on button click
            $aiButton.on('click', function(){
                $('#prompt2image-text').val('');
                $('#prompt2image-generate, #prompt2image-cancel').hide(); // Hide buttons initially
                $('#prompt2image-modal').fadeIn();

                // Optional: fade in buttons after 200ms
                setTimeout(function(){
                    $('#prompt2image-generate, #prompt2image-cancel').fadeIn();
                }, 200);
            });
        }

        // Cancel modal
        $(document).on('click', '#prompt2image-cancel, #prompt2image-modal .prompt2image-overlay', function() {
            $('#prompt2image-modal').fadeOut();
            $('#prompt2image-generate, #prompt2image-cancel').show(); // reset buttons
        });

        // Generate AI Image
        $(document).on('click', '#prompt2image-generate', function(){
            const userPrompt = $('#prompt2image-text').val().trim();
            if (!userPrompt) { alert('Please enter a prompt!'); return; }

            $('#prompt2image-generate, #prompt2image-cancel').hide();
            $('#prompt2image-loader').show();

            $.post(PROMPT2IMAGE.ajax_url, {
                action: 'generate_ai_image',
                nonce: PROMPT2IMAGE.nonce,
                prompt: userPrompt
            }, function(response){
                $('#prompt2image-loader').hide();
                $('#prompt2image-modal').fadeOut();
                $('#prompt2image-generate, #prompt2image-cancel').show(); // reset for next time

                if(response.success){
                    alert('✅ Image generated!');
                    // Optional: refresh Media Library or insert image automatically
                } else {
                    alert('❌ ' + response.data.message);
                }
            });
        });
    }
});
jQuery(document).ready(function($){

   
    // Show first tab on page load
    $('.prompt2image-field-wrap[data-tab="1"]').show().addClass('active');

    $('.nav-tab').click(function(e) {
        e.preventDefault();

        // Remove active class from all tabs
        $('.nav-tab').removeClass('nav-tab-active');

        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');

        // Get tab number
        var tab = $(this).data('tab');

        // Hide all tab contents
        $('.prompt2image-field-wrap').removeClass('active').hide();

        // Show only fields with matching data-tab instantly
        $('.prompt2image-field-wrap[data-tab="' + tab + '"]').show().addClass('active');
    });

    // Optional: Show only active tab on page load
    $('.prompt2image-field-wrap').not('.active').hide();



    // AJAX submit
    $('#prompt2image-settings-form').on('submit', function(e){
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: prompt2imageAjax.ajax_url,
            type: 'POST',
            data: formData + '&nonce=' + prompt2imageAjax.nonce,
            success: function(response){
                if(response.success){
                    alert(response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error){
                alert('AJAX error: ' + error);
            }
        });
    });
});



