jQuery(document).ready(function ($) {
    var token = $('#qrmv_token').val();
    var ajaxUrl = $('#qrmv_ajax_url').val();
    var pollInterval;

    if (!token || !ajaxUrl) {
        return; // Not on the verification page
    }

    function checkStatus() {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'qrmv_check_status',
                token: token
            },
            success: function (response) {
                if (response.success && response.data.status === 'verified') {
                    // Success!
                    clearInterval(pollInterval);
                    $('#qrmv-status').html('<span style="color:green; font-size:1.2em;">✅ 驗證成功！</span><br>正在跳轉...');

                    // Redirect or Unlock content
                    setTimeout(function () {
                        alert('🎉 恭喜！您已通過真人驗證！(模擬轉址)');
                        // location.reload(); // Or redirect to specific URL
                    }, 1000);
                }
            }
        });
    }

    // Start polling every 2 seconds
    pollInterval = setInterval(checkStatus, 2000);
});
