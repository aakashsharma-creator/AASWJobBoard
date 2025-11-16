jQuery(document).ready(function($) {
	
	
	$(document).on('click', '.remove-card', function(e) {
		e.preventDefault();

		const token_id = $(this).attr('id');

		console.log(token_id);  
		//return false;
		
		$.post(myAjax.ajax_url, {  
			action: 'delete_saved_card',
			token_id: token_id,
			nonce: myAjax.nonce
		}, function(response) {
			if (response.success) {
				alert('Card deleted successfully');
				location.reload();
			} else {
				alert(response.data || 'Error deleting card');
			}
		});
	});


    /*const stripe = Stripe(AddCardAjax.publishable_key);
    const elements = stripe.elements();
    const card = elements.create('card', {
	    hidePostalCode: true
	});
    let clientSecret;

    // Mount the card field when modal opens
    $('#openAddCardModal').on('click', function() {
        $('#addCardModalOverlay').fadeIn();
        if ($('#card-element').is(':empty')) card.mount('#card-element');
    });

    // Close modal
    $('#closeAddCardModal').on('click', function() {
        $('#addCardModalOverlay').fadeOut();
    });

    // Create SetupIntent and confirm
    $('#addCardForm').on('submit', function(e) {
        e.preventDefault();
        $('#saveCardBtn').prop('disabled', true).text('Saving...');
        $('#cardStatusMsg').text('');

        $.post(AddCardAjax.ajax_url, {
            action: 'create_stripe_setup_intent',
            nonce: AddCardAjax.nonce
        }, function(res) {
			console.log(res);
            if (!res.success) {
                $('#cardStatusMsg').text(res.data.message);
                $('#saveCardBtn').prop('disabled', false).text('Save Card');
                return;
            }

            clientSecret = res.data.client_secret;

            stripe.confirmCardSetup(clientSecret, {
                payment_method: { card: card }
            }).then(function(result) {
                if (result.error) {
                    $('#cardStatusMsg').text(result.error.message);
                    $('#saveCardBtn').prop('disabled', false).text('Save Card');
                } else {
                    // Save card to WooCommerce
                    $.post(AddCardAjax.ajax_url, {
                        action: 'save_stripe_payment_method',
                        nonce: AddCardAjax.nonce,
                        payment_method: result.setupIntent.payment_method
                    }, function(saveRes) {
                        if (saveRes.success) {
                            $('#cardStatusMsg').text('Card added successfully!');
                            setTimeout(() => {
                                $('#addCardModalOverlay').fadeOut();
                                reloadPaymentMethods();
                            }, 1000);
                        } else {
                            $('#cardStatusMsg').text(saveRes.data.message);
                        }
                        $('#saveCardBtn').prop('disabled', false).text('Save Card');
                    });
                }
            });
        });
    });*/
	
});
