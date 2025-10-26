jQuery(document).ready(function($) {

    /******************************
     * 1. AI Image Generation UI
     ******************************/
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

                let html = '<div class="gemini-generation" style="border:1px solid #ccc; padding:10px; margin-bottom:20px;">';
                html += '<h3>Prompt: ' + userPrompt + '</h3>';

                if(response.success){
                    const output = response.data;

                    // Loop through all candidates
                    if(output.candidates && output.candidates.length > 0){
                        output.candidates.forEach(function(candidate, index){
                            html += '<div class="gemini-candidate" style="margin-bottom:15px;">';
                            html += '<h4>Candidate ' + (index + 1) + ':</h4>';

                            if(candidate.content && candidate.content.parts){
                                candidate.content.parts.forEach(function(part){
                                    // Text
                                    if(part.text){
                                        html += '<p>' + part.text + '</p>';
                                    }
                                    // Image
                                    if(part.inlineData && part.inlineData.data){
                                        html += '<img src="data:' + part.inlineData.mimeType + ';base64,' + part.inlineData.data + '" style="max-width:400px; margin-top:10px; display:block;"/>';
                                        html += '<a href="data:' + part.inlineData.mimeType + ';base64,' + part.inlineData.data + '" download="ai-image.png" class="button" style="margin-top:5px; display:inline-block;">Download Image</a>';
                                    }
                                });
                            }

                            html += '</div>';
                        });
                    } else {
                        html += '<p>No output returned from AI.</p>';
                    }

                } else {
                    html += '<p>Error: ' + response.data + '</p>';
                }

                html += '</div>';

                // Append to history in footer
                $('#gemini-output-history').prepend(html);

                // Scroll to latest generation
                $('html, body').animate({
                    scrollTop: $('#gemini-output-history').offset().top - 100
                }, 600);

                // Optionally close modal
                $('#prompt2image-modal').fadeOut();
            });
        });
    }


    /******************************
     * 2. Settings Form AJAX Submit
     ******************************/
    $(document).on('submit', '#prompt2image-settings-form', function(e){
        e.preventDefault();

        let formData = $(this).serialize(); // serialize form fields
        formData += '&action=p2i_save_setting&_wpnonce=' + encodeURIComponent(PROMPT2IMAGE.nonce);

        $.post(PROMPT2IMAGE.ajax_url, formData, function(response){
            console.log(response);
            // optionally show a success message
        }).fail(function(){
            alert('Connection failed. Please try again.');
        });
    });


    /******************************
     * 3. Server Connect Modal
     ******************************/
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

        // Start loading
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


    /******************************
     * 4. Toggle API Key Visibility
     ******************************/
    $('#toggle-api-key').on('click', function() {
        const $input = $('#api_key');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('dashicons-hidden dashicons-visibility');
    });


    /******************************
     * 5. Disconnect Server
     ******************************/
    const $disconnectButton = $('#disconnect-server');

    $disconnectButton.on('click', function(e) {
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

});
