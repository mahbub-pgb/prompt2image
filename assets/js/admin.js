jQuery(document).ready(function($) {

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
            $aiButton.on('click', function() {
                $('#prompt2image-text').val('');
                $('#prompt2image-loader').hide();
                $('#prompt2image-generate, #prompt2image-cancel').show();
                $('#prompt2image-modal').fadeIn();
                $('#gemini-output-single').html(''); // clear previous preview
            });
        }

        // Cancel modal
        $(document).on('click', '#prompt2image-cancel, #prompt2image-modal .prompt2image-overlay', function() {
            $('#prompt2image-modal').fadeOut();
        });

            // Generate AI Image
    // Generate AI Image
    $(document).on('click', '#prompt2image-generate', function() {
        const userPrompt = $('#prompt2image-text').val().trim();
        if (!userPrompt) { alert('Please enter a prompt!'); return; }

        lastPrompt = userPrompt; // Save the prompt for regeneration

        $('#prompt2image-loader').show();
        $('#prompt2image-generate, #prompt2image-cancel').hide();
        $('#prompt2image-text').prop('disabled', true);

        $.post(PROMPT2IMAGE.ajax_url, {
            action: 'generate_ai_image',
            nonce: PROMPT2IMAGE.nonce,
            prompt: userPrompt
        }, function(response) {

            $('#prompt2image-loader').hide();
            $('#prompt2image-modal').hide();

            let html = '<div class="gemini-candidate">';
            html += '<h4>AI Image Preview</h4>';

            if (response.success && response.data.candidates && response.data.candidates.length > 0) {
                const candidate = response.data.candidates[0];
                if (candidate.content && candidate.content.parts) {
                    let foundImage = false;

                    for (let i = 0; i < candidate.content.parts.length; i++) {
                        const part = candidate.content.parts[i];
                        if (part.inlineData && part.inlineData.data && part.inlineData.data.trim() !== '') {
                            const base64Data = part.inlineData.data;
                            const mimeType   = part.inlineData.mimeType;
                            const filename   = 'ai-image.png';

                            // Show image
                            html += '<img src="data:' + mimeType + ';base64,' + base64Data + '" style="max-width:300px; margin-top:10px; display:block;">';

                            // Buttons: Save + Regenerate
                            html += '<div style="margin-top:10px;">';
                            html += '<button class="p2i-save-image button" data-base64="' + base64Data + '" data-mime="' + mimeType + '" data-filename="' + filename + '">Save to Media Library</button>';
                            html += '<button class="p2i-regenerate-image button" style="margin-left:5px;">Regenerate Image</button>';
                            html += '</div>';

                            foundImage = true;
                            break; // show only first image
                        }
                    }
                    if (!foundImage) html += '<p>No image returned.</p>';
                }
            } else {
                html += '<p>Error generating image.</p>';
            }

            html += '</div>';

            // Insert the preview **inside modal**
            $('#gemini-output-single').html(html);

            // DO NOT hide the modal here
            // $('#prompt2image-modal').fadeOut();  <-- remove this line
        });
    });



        // Save image to Media Library
        $(document).on('click', '.p2i-save-image', function() {
            const $btn = $(this);
            const base64Data = $btn.data('base64');
            const mimeType = $btn.data('mime');
            const filename = $btn.data('filename');

            $btn.prop('disabled', true);


            $.post(PROMPT2IMAGE.ajax_url, {
                action: 'p2i_save_image_media',
                _wpnonce: PROMPT2IMAGE.nonce,
                image_data: base64Data,
                mime_type: mimeType,
                filename: filename
            }, function(saveResp) {
                if (saveResp.success && saveResp.data.url) {
                    alert('Image saved! You can view it in Media Library.');
                    $('#prompt2image-modal').fadeOut();
                    window.location.reload();
                } else {
                    alert('Failed to save image.');
                    $btn.prop('disabled', false).text('Save to Media Library');
                }
            });
        });

       // Regenerate button click handler
        $(document).on('click', '.p2i-regenerate-image', function() {
            // Clear preview
            $('#gemini-output-single').html('');

            // Show modal and buttons
            $('#prompt2image-modal').fadeIn();
            $('#prompt2image-generate, #prompt2image-cancel').fadeIn();

            // Enable input and set it to last prompt
            $('#prompt2image-text').prop('disabled', false).val(lastPrompt);
        });


    }

     // Click on AI image in the preview area
    $(document).on('click', '#gemini-output-single img', function() {
        const src = $(this).attr('src');

        // Set clicked image in modal
        $('#gemini-preview-modal img').attr('src', src);

        // Show modal
        $('#gemini-preview-modal').fadeIn();
    });

    // Close modal when clicking overlay or close button
    $(document).on('click', '#gemini-preview-modal .prompt2image-overlay, #gemini-preview-modal .close-preview', function() {
        $('#gemini-preview-modal').fadeOut();
    });

});





jQuery(document).ready(function($) {
    /**************************************
     * 3. Settings Form AJAX Submit
     **************************************/
    $(document).on('submit', '#prompt2image-settings-form', function(e){
        e.preventDefault();

        let formData = $(this).serialize();
        formData += '&action=p2i_save_setting&_wpnonce=' + encodeURIComponent(PROMPT2IMAGE.nonce);

        $.post(PROMPT2IMAGE.ajax_url, formData, function(response){
            console.log(response);
        }).fail(function(){
            alert('Connection failed. Please try again.');
        });
    });


    /**************************************
     * 4. Server Connect / Disconnect
     **************************************/
    $('#connect-server').on('click', function(){
        $('#server-connect-modal').fadeIn(200);
    });

    $('#cancel-connect').on('click', function(){
        $('#server-connect-modal').fadeOut(200);
    });

    $(document).on('click', '#confirm-connect', function(e){
        e.preventDefault();
        const $btn = $(this);
        const $loader = $('#server-connect-loader');
        const $btnText = $btn.find('.btn-text');

        $btn.prop('disabled', true);
        $btnText.text('Connecting...');
        $loader.fadeIn(150);

        $.post(PROMPT2IMAGE.ajax_url, {
            action: 'p2i_connect_server',
            _wpnonce: PROMPT2IMAGE.nonce,
        }, function(response){
            setTimeout(function() {
                $btnText.text('✅ Connected');
                $('#connect-server').text('✅ Connected');
                $loader.fadeOut(150);
                window.location.reload();                
            }, 500);
        }).fail(function(){
            $loader.fadeOut(150);
            $btn.prop('disabled', false);
            $btnText.text('Connect');
            alert('Connection failed. Please try again.');
        });
    });

    $('#disconnect-server').on('click', function(e) {
        e.preventDefault();
        $.post(PROMPT2IMAGE.ajax_url, {
            action: 'disconnect_server',
            _wpnonce: PROMPT2IMAGE.nonce,
        }, function(response){
            console.log(response);
            window.location.reload(); 
        }).fail(function(){
            alert('Disconnect failed. Please try again.');
        });
    });


    /**************************************
     * 5. Toggle API Key Visibility
     **************************************/
    $('#toggle-api-key').on('click', function() {
        const $input = $('#api_key');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('dashicons-hidden dashicons-visibility');
    });

});
