jQuery(function ($) {
  $(".prompt2image-btn").on("click", function () {
    const prompt = prompt("Describe your image:");
    if (!prompt) return;

    $.post(
      prompt2image.ajax_url,
      {
        action: "generate_ai_image",
        nonce: prompt2image.nonce,
        prompt: prompt,
      },
      function (response) {
        if (response.success) {
          alert("✅ Image generated!");
          window.open(response.data.image_url, "_blank");
        } else {
          alert("❌ " + response.data.message);
        }
      }
    );
  });
});
