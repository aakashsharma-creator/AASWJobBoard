jQuery(document).ready(function ($) {
    $(document).on('click', '#logout-button', function (e) {
        e.preventDefault();

        $.ajax({
            url: ajax_object.ajax_url, // ✅ this now comes from PHP
            type: 'POST',
            data: {
                action: 'custom_user_logout',
                security: ajax_object.nonce
            },
            success: function (response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data.message || 'Logout failed.');
                }
            },
            error: function () {
                alert('Error logging out. Please try again.');
            }
        });
    });
});
