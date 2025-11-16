const stripe = Stripe('pk_test_51SBJ1uL8PddfJrLCQOd3oNwIQ1cgeqnB6jGksIkVNeKkkwm2Hkwuw6ZONVwds7vVJNNCReBXWM2wyPDtPMvYf1Zm0034681fcZ');
const elements = stripe.elements();
const card = elements.create('card', {
	    hidePostalCode: true
	});
cardElement.mount('#card-element');

document.querySelector('#add-card-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const { paymentMethod, error } = await stripe.createPaymentMethod({
    type: 'card',
    card: cardElement,
  });

  if (error) {
    document.querySelector('#card-errors').textContent = error.message;
  } else {
    // Send to your custom endpoint
    jQuery.post(ajaxurl, {
      action: 'save_stripe_payment_method',
      payment_method: paymentMethod.id
    }, function(response){
      alert('Card saved!');
    });
  }
});
