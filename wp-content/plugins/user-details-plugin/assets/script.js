jQuery(document).ready(function($) {
    $('table.udp-table').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
    });

    // Add loader element (hidden by default)
    $('body').append('<div id="udp-loader" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.6); z-index:9999; text-align:center;">\
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">\
            <div class="spinner" style="border:6px solid #f3f3f3; border-top:6px solid #0073aa; border-radius:50%; width:40px; height:40px; animation: spin 1s linear infinite;"></div>\
            <p style="color:#0073aa; margin-top:10px;">Loading...</p>\
        </div>\
    </div>');

    // Spinner animation CSS
    const spinnerStyle = `
        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
    `;
    $('head').append(spinnerStyle);

    $(document).on('click', '.udp-view-user-employer', function() {
        let user_id = $(this).data('user');

        // Show loader
        $('#udp-loader').fadeIn(100);

        $.post(udp_ajax.ajax_url, {
            action: 'udp_get_user_details_employer',
            user_id: user_id
        }, function(response) {
            // Hide loader
            $('#udp-loader').fadeOut(100);

            if (response.success) {
                $('#udp-modal-content').html(response.data.html);
                $('#udp-modal').fadeIn(200);
            } else {
                alert(response.data.message);
            }
        }).fail(function() {
            $('#udp-loader').fadeOut(100);
            alert('Something went wrong while loading user details.');
        });
    });
	
	 $(document).on('click', '.udp-view-user', function() {
        let user_id = $(this).data('user');

        // Show loader
        $('#udp-loader').fadeIn(100);

        $.post(udp_ajax.ajax_url, {
            action: 'udp_get_user_details',
            user_id: user_id
        }, function(response) {
            // Hide loader
            $('#udp-loader').fadeOut(100);

            if (response.success) {
                $('#udp-modal-content').html(response.data.html);
                $('#udp-modal').fadeIn(200);
            } else {
                alert(response.data.message);
            }
        }).fail(function() {
            $('#udp-loader').fadeOut(100);
            alert('Something went wrong while loading user details.');
        });
    });
	
	

    $(document).on('click', '.udp-close', function() {
        $('#udp-modal').fadeOut(200);
    });

    $(window).on('click', function(e) {
        if ($(e.target).is('#udp-modal')) {
            $('#udp-modal').fadeOut(200);
        }
    });
});
