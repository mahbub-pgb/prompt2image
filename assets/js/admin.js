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


    // AJAX submit


     $(document).on('submit', '#prompt2image-settings-form', function(e){
        e.preventDefault();

        var formData = $(this).serialize(); // serialize form fields

        // Add action and nonce to the serialized data
        formData += '&action=p2i_save_setting&_wpnonce=' + encodeURIComponent(PROMPT2IMAGE.nonce);

        $.post(PROMPT2IMAGE.ajax_url, formData, function(response){
            console.log(response);
            // optionally show a success message
        }).fail(function(){
            alert('Connection failed. Please try again.');
        });
    });

    /*Connect with api*/
    $('#connect-server').on('click', function(){
        $('#server-connect-modal').fadeIn(200);
    });

    $('#cancel-connect').on('click', function(){
        $('#server-connect-modal').fadeOut(200);
    });

    $(document).on('click', '#confirm-connect', function(e){
        e.preventDefault();

        var $btn = $(this);
        var $loader = $('#server-connect-loader');
        var $btnText = $btn.find('.btn-text');

        // Start loading
        $btn.prop('disabled', true);
        $btnText.text('Connecting...');
        $loader.fadeIn(150);

        $.post(PROMPT2IMAGE.ajax_url, {
            action: 'p2i_connect_server',
            _wpnonce: PROMPT2IMAGE.nonce,
        }, function(response){
            console.log(response);

            // Optional delay for smoother UI even if AJAX is instant
            setTimeout(function() {
                // Success animation
                $btnText.text('✅ Connected');
                $('#connect-server').text('✅ Connected');
                $loader.fadeOut(150);

                setTimeout(function(){
                    // Reset and close modal
                    $btn.prop('disabled', false);
                    $btnText.text('Connect');
                    $('#server-connect-modal').fadeOut(200);
                }, 800);
            }, 500);
        }).fail(function(){
            $loader.fadeOut(150);
            $btn.prop('disabled', false);
            $btnText.text('Connect');
            alert('Connection failed. Please try again.');
        });
    });

    /*Show the password*/

    $('#toggle-api-key').on('click', function() {
        var $input = $('#api_key');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('dashicons-hidden dashicons-visibility');
    });


});

// Add Disconnect button if not already added
                    if ($('#disconnect-server').length === 0) {
                        $('<button id="disconnect-server" class="button button-secondary">Disconnect</button>')
                            .insertAfter($button)
                            .on('click', function() {
                                // Handle disconnect
                                $.ajax({
                                    url: ajaxurl,
                                    method: 'POST',
                                    data: {
                                        action: 'disconnect_server',
                                        _wpnonce: prompt2image.nonce
                                    },
                                    success: function(res) {
                                        if(res.success) {
                                            alert('Disconnected!');
                                            $button.text('Connect with us').prop('disabled', false);
                                            $('#disconnect-server').remove();
                                        } else {
                                            alert(res.data.message);
                                        }
                                    }
                                });
                            });
                    }










