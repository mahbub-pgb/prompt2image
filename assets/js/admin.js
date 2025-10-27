jQuery(document).ready(function ($) {

    /** =========================
     * 1. Variables
     * ========================= */
    let lastPrompt = '';

    /** =========================
     * 2. Only on attachment post type
     * ========================= */
    if ($('body').hasClass('post-type-attachment')) {

        /** ===== Add AI Button ===== */
        const $bulkButton = $('.select-mode-toggle-button');

        if ($bulkButton.length && $('.prompt2image-btn').length === 0) {
            const $aiButton = $('<button/>', {
                type: 'button',
                class: 'button prompt2image-btn',
                text: '🪄 Generate AI Image'
            });
            $bulkButton.after($aiButton);

            $aiButton.on('click', function () {
                $('#prompt2image-text').val('');
                $('#prompt2image-loader').hide();
                $('#prompt2image-generate, #prompt2image-cancel').show();
                $('#prompt2image-modal').fadeIn();
                $('#gemini-output-single').html('');
                $('#prompt2image-text').prop('disabled', false);
            });
        }

        /** ===== Cancel Modal ===== */
        $(document).on('click', '#prompt2image-cancel, #prompt2image-modal .prompt2image-overlay', function () {
            $('#prompt2image-modal').fadeOut();
        });

        /** ===== Generate AI Image ===== */
        $(document).on('click', '#prompt2image-generate', function () {
            const userPrompt = $('#prompt2image-text').val().trim();

            if (!userPrompt) {
                alert('Please enter a prompt!');
                return;
            }

            lastPrompt = userPrompt;

            $('#p2i-loader').fadeIn(150);
            $('#prompt2image-generate, #prompt2image-cancel').hide();
            $('#prompt2image-text').prop('disabled', true);

            $.post(PROMPT2IMAGE.ajax_url, {
                action: 'generate_ai_image',
                nonce: PROMPT2IMAGE.nonce,
                prompt: userPrompt
            }, function (response) {
               $('#p2i-loader').fadeOut(150);
                $('#prompt2image-modal').hide();

                let html = '<div class="gemini-candidate">';
                html += '<h3 style="margin-top:0;">AI Image Preview</h3>';

                if (response.success && response.data.candidates && response.data.candidates.length > 0) {
                    const candidate = response.data.candidates[0];

                    if (candidate.content && candidate.content.parts) {
                        let foundImage = false;

                        candidate.content.parts.forEach(function (part) {
                            if (part.inlineData && part.inlineData.data && part.inlineData.data.trim() !== '') {
                                const base64Data = part.inlineData.data;
                                const mimeType = part.inlineData.mimeType;
                                const filename = 'ai-image.png';

                                html += `
                                    <img src="data:${mimeType};base64,${base64Data}" 
                                         class="ai-preview-img"
                                         style="max-width:100%; border-radius:8px; margin-top:10px; display:block; cursor:pointer; margin:auto;">
                                    <div style="margin-top:15px; text-align:center;">
                                        <button class="p2i-save-image button button-primary" 
                                            data-base64="${base64Data}" 
                                            data-mime="${mimeType}" 
                                            data-filename="${filename}">
                                            💾 Save to Media Library
                                        </button>
                                        <button class="p2i-regenerate-image button" style="margin-left:10px;">
                                            🔄 Regenerate Image
                                        </button>
                                    </div>
                                `;
                                foundImage = true;
                            }
                        });

                        if (!foundImage) html += '<p>No image returned.</p>';
                    }

                } else {
                    html += '<p>Error generating image.</p>';
                }

                html += '</div>';

                $('#prompt2image-result-body').html(html);
                $('#prompt2image-result-modal').fadeIn();
            });
        });

        /** ===== Save Image ===== */
        $(document).on('click', '.p2i-save-image', function () {
            const $btn = $(this);
            const base64Data = $btn.data('base64');
            const mimeType = $btn.data('mime');
            const filename = $btn.data('filename');

            $btn.prop('disabled', true).text('Saving...');
            $('#p2i-loader').fadeIn(150);

            $.post(PROMPT2IMAGE.ajax_url, {
                action: 'p2i_save_image_media',
                _wpnonce: PROMPT2IMAGE.nonce,
                image_data: base64Data,
                mime_type: mimeType,
                filename: filename
            }, function (saveResp) {
                $('#p2i-loader').fadeOut(150);

                if (saveResp.success && saveResp.data.url) {
                    $('#prompt2image-result-modal').fadeOut();
                    window.location.reload();
                } else {
                    alert('Failed to save image.');
                    $btn.prop('disabled', false).text('Save to Media Library');
                }
            });
        });

        /** ===== Regenerate Image ===== */
        $(document).on('click', '.p2i-regenerate-image', function () {
            $('#prompt2image-result-modal').fadeOut();
            $('#gemini-output-single').html('');
            $('#prompt2image-modal').fadeIn();
            $('#prompt2image-generate, #prompt2image-cancel').fadeIn();
            $('#prompt2image-text').prop('disabled', false).val(lastPrompt);
        });

    }

    /** =========================
     * 3. Image Preview Modal
     * ========================= */
    $(document).on('click', '.ai-preview-img', function () {
        const src = $(this).attr('src');
        $('#gemini-preview-modal img').attr('src', src);
        $('#gemini-preview-modal').fadeIn();
    });

    $(document).on('click', '#gemini-preview-modal, #gemini-preview-modal .close-preview', function (e) {
        if (e.target.id === 'gemini-preview-modal' || $(e.target).hasClass('close-preview')) {
            $('#gemini-preview-modal').fadeOut();
        }
    });

    /** Close result modal */
    $(document).on('click', '.prompt2image-result-close, .close-preview', function (e) {
        e.preventDefault();
        $('#prompt2image-result-modal, #gemini-preview-modal').fadeOut();
    });

    /** =========================
     * 4. Settings Form AJAX Submit
     * ========================= */
    $(document).on('submit', '#prompt2image-settings-form', function (e) {
        e.preventDefault();
        $('#p2i-loader').fadeIn();

        let formData = $(this).serialize();
        formData += '&action=p2i_save_setting&_wpnonce=' + encodeURIComponent(PROMPT2IMAGE.nonce);

        $.post(PROMPT2IMAGE.ajax_url, formData)
            .done(function (response) {
                console.log(response);
            })
            .fail(function () {
                alert('Connection failed. Please try again.');
            })
            .always(function () {
                $('#p2i-loader').fadeOut();
            });
    });

    /** =========================
     * 5. Server Connect / Disconnect
     * ========================= */
    $('#connect-server').on('click', function () {
        $('#server-connect-modal').fadeIn(200);
    });

    $('#cancel-connect').on('click', function () {
        $('#server-connect-modal').fadeOut(200);
    });

    $(document).on('click', '#confirm-connect', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $loader = $('#p2i-loader');
        const $btnText = $btn.find('.btn-text');

        $btn.prop('disabled', true);
        $btnText.text('Connecting...');
        $loader.fadeIn(150);

        $.post(PROMPT2IMAGE.ajax_url, {
            action: 'p2i_connect_server',
            _wpnonce: PROMPT2IMAGE.nonce,
        }).done(function (response) {
            setTimeout(function () {
                $btnText.text('✅ Connected');
                $('#connect-server').text('✅ Connected');
                $loader.fadeOut(150);
                window.location.reload();
            }, 500);
        }).fail(function () {
            $loader.fadeOut(150);
            $btn.prop('disabled', false);
            $btnText.text('Connect');
            alert('Connection failed. Please try again.');
        });
    });

    $('#disconnect-server').on('click', function (e) {
        $('#p2i-loader').fadeIn(150);
        e.preventDefault();
        $.post(PROMPT2IMAGE.ajax_url, {
            action: 'disconnect_server',
            _wpnonce: PROMPT2IMAGE.nonce,
        }).done(function (response) {
            $('#p2i-loader').fadeOut(150);
            window.location.reload();
        }).fail(function () {
            $('#p2i-loader').fadeOut(150);
            alert('Disconnect failed. Please try again.');
        });
    });

    /** =========================
     * 6. Toggle API Key Visibility
     * ========================= */
    $('#toggle-api-key').on('click', function () {
        const $input = $('#api_key');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('dashicons-hidden dashicons-visibility');
    });

});
