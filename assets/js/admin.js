jQuery(document).ready(function($) {

    /**************************************
     * 1. AI Image Generation UI & Modal
     **************************************/
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

            // Show modal on AI button click
            $aiButton.on('click', function(){
                $('#prompt2image-text').val('');
                $('#prompt2image-loader').hide();
                $('#prompt2image-generate, #prompt2image-cancel').show();
                $('#prompt2image-modal').fadeIn();
            });
        }

        // Cancel modal
        $(document).on('click', '#prompt2image-cancel, #prompt2image-modal .prompt2image-overlay', function() {
            $('#prompt2image-modal').fadeOut();
        });

        // Generate AI Image
        $(document).on('click', '#prompt2image-generate', function(){
            const userPrompt = $('#prompt2image-text').val().trim();
            if (!userPrompt) { 
                alert('Please enter a prompt!'); 
                return; 
            }

            // Show loader & hide buttons
            $('#prompt2image-loader').show();
            $('#prompt2image-generate, #prompt2image-cancel').hide();

            $.post(PROMPT2IMAGE.ajax_url, {
                action: 'generate_ai_image',
                nonce: PROMPT2IMAGE.nonce,
                prompt: userPrompt
            }, function(response){
                $('#prompt2image-loader').hide();
                $('#prompt2image-generate, #prompt2image-cancel').show();

                console.log( response );

                renderGeminiResponse(response, userPrompt);

                // Close modal after rendering
                $('#prompt2image-modal').fadeOut();
            });
        });
    }


    /**************************************
     * 2. Render AI Response (Text + Images)
     **************************************/
    function renderGeminiResponse(response, prompt) {
        let html = '<div class="gemini-generation" style="border:1px solid #ccc; padding:10px; margin-bottom:20px;">';
        html += '<h3>Prompt: ' + prompt + '</h3>';

        if(response.success && response.data.candidates && response.data.candidates.length > 0){
            response.data.candidates.forEach(function(candidate, candIndex){
                html += '<div class="gemini-candidate" style="margin-bottom:15px;">';
                html += '<h4>Candidate ' + (candIndex + 1) + ':</h4>';

                if(candidate.content && candidate.content.parts){
                    candidate.content.parts.forEach(function(part, partIndex){
                        // Text
                        if(part.text){
                            html += '<p>' + part.text + '</p>';
                        }

                        // Image
                        if(part.inlineData && part.inlineData.data){
                            if(part.inlineData.data.trim() !== ''){
                                html += '<img src="data:' + part.inlineData.mimeType + ';base64,' + part.inlineData.data + '" style="max-width:400px; display:block; margin-top:10px;"/>';
                                html += '<a href="data:' + part.inlineData.mimeType + ';base64,' + part.inlineData.data + '" download="ai-image-' + candIndex + '-' + partIndex + '.png" class="button" style="margin-top:5px; display:inline-block;">Download Image</a>';
                            } else {
                                html += '<p><em>Image data not available.</em></p>';
                            }
                        }
                    });
                }

                html += '</div>';
            });
        } else {
            html += '<p>No output returned from AI.</p>';
        }

        html += '</div>';

        // Append to history container
        $('#gemini-output-history').prepend(html);

        // Scroll to latest output
        $('html, body').animate({
            scrollTop: $('#gemini-output-history').offset().top - 100
        }, 600);
    }


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
