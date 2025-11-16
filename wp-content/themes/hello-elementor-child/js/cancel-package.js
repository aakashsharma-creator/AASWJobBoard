jQuery(document).ready(function($) {

    var package_id = '';
    var user_id = '';
    var $btn = '';
	
    // Cancel Plan Button Click
    $('.subplan-card .cancle_plan').on('click', function(e) {
        e.preventDefault();

        package_id = $(this).data('package-id');
        user_id = $(this).attr('id');
        $btn = $(this);
		//console.warn('package_id::- '+package_id);  
		
        // Open Elementor Popup safely
        if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
            elementorProFrontend.modules.popup.showPopup({ id: 3508 });
			var limit_remain = $('#limit_remains').text().trim();
			var plan_expiry = $('#plan_expiry_date').text().trim();
			$('#cancel_alert_popup span#job_remaining').text(limit_remain);
			$('#cancel_alert_popup span#job_expiry_date').text(plan_expiry);  
			//console.warn('limit_remain::- '+limit_remain);  
        }
    });

    // YES button click inside Elementor popup
    $(document).on('click', '#cncl_yes', function(event) {
        event.preventDefault();

        if (!package_id || !user_id) {
            console.warn('Missing package_id or user_id');
            return;
        }

        var $yesBtn = $(this);
        $yesBtn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: cancelPackageAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'cancel_user_package',
                package_id: package_id,
                user_id: user_id,
                nonce: cancelPackageAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.text('Cancelled');
                } else {
                    alert(response.data || 'Something went wrong.');
                }
            },
            complete: function() {
                $yesBtn.prop('disabled', false).text('Yes');
                jQuery('.elementor-popup-modal').removeClass('elementor-active').hide();
				jQuery('body').removeClass('elementor-popup-modal-open');
				
				// Open Elementor Popup safely
				if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
					elementorProFrontend.modules.popup.showPopup({ id: 3514 }); 
				}
            }
        });
    });

    // NO button click inside Elementor popup
    $(document).on('click', '#cncl_no', function(event) {
        event.preventDefault();
		jQuery('.elementor-popup-modal').removeClass('elementor-active').hide();
		jQuery('body').removeClass('elementor-popup-modal-open');
    });
	
	$(document).on('click', '#change_plan', function(event) {
        event.preventDefault();
		if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
			elementorProFrontend.modules.popup.showPopup({ id: 3672 }); 
		}
		//jQuery('.elementor-popup-modal').removeClass('elementor-active').hide();
		//jQuery('body').removeClass('elementor-popup-modal-open');
    });
	
	 $(document).on('click', '#upgrade_no', function(event) {
        event.preventDefault();
		jQuery('.elementor-popup-modal').removeClass('elementor-active').hide();
		jQuery('body').removeClass('elementor-popup-modal-open');
    });

		
});
