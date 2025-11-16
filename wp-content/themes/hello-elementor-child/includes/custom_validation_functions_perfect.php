<?php
add_action('wp_footer', 'forminator_australia_autocomplete_post_a_job_only', 9999);
function forminator_australia_autocomplete_post_a_job_only()
{

	if (!is_page(array('advertiser-registration', 'locum-registration'))) {
		return;
	}
	?>
	<script type="module">
		(async () => {
			if (window.forminatorAuFinal2025) return;
			window.forminatorAuFinal2025 = true;

			// ✅ Load Google Maps modularly (new style)
			await google.maps.importLibrary("places");
			// console.log("✅ Google Maps 'places' library loaded (modular)");

			function initAutocomplete() {
				let attempts = 0;
				const maxAttempts = 100;
				const checker = setInterval(() => {
					attempts++;
					const originalInput = document.querySelector('input[name="address-1-street_address"]');

					if (originalInput && !originalInput.dataset.auFinal2025) {
						clearInterval(checker);
						// console.log("FOUND Forminator field – initializing FINAL 2025 Autocomplete");
						setupAutocomplete(originalInput);
					}
					if (attempts >= maxAttempts) clearInterval(checker);
				}, 200);
			}

			function setupAutocomplete(originalInput) {
				originalInput.dataset.auFinal2025 = "true";

				const gmpAutocomplete = new google.maps.places.PlaceAutocompleteElement({
					includedRegionCodes: ['au'],
				});

				gmpAutocomplete.style.width = '100%';
				gmpAutocomplete.style.fontSize = '16px';

				originalInput.style.position = 'absolute';
				originalInput.style.left = '-9999px';
				originalInput.style.width = '1px';
				originalInput.style.height = '1px';
				originalInput.style.opacity = '0';

				originalInput.parentNode.insertBefore(gmpAutocomplete, originalInput.nextSibling);

				gmpAutocomplete.addEventListener('input', () => {
					originalInput.value = gmpAutocomplete.value;
					originalInput.dispatchEvent(new Event('input', { bubbles: true }));
					originalInput.dispatchEvent(new Event('change', { bubbles: true }));
					originalInput.dispatchEvent(new Event('blur', { bubbles: true }));
				});

				gmpAutocomplete.addEventListener('gmp-placeselect', async (event) => {
					const place = event.detail.place;
					await place.fetchFields({ fields: ['address_components', 'formatted_address'] });

					const get = (type) => {
						const comp = place.address_components?.find(c => c.types.includes(type));
						return comp ? comp.long_name : '';
					};

					const streetNumber = get('street_number');
					const route = get('route');
					const suburb = get('locality') || get('sublocality_level_1') || get('neighborhood');
					const state = get('administrative_area_level_1');
					const postcode = get('postal_code');

					const streetValue = [streetNumber, route].filter(Boolean).join(' ').trim();
					originalInput.value = streetValue;
					gmpAutocomplete.value = streetValue;

					const container = originalInput.closest('#address-1') || originalInput.closest('.forminator-field-address');
					const fill = (name, val) => {
						const field = document.querySelector(`input[name="address-1-${name}"]`);
						if (field) {
							field.value = val || '';
							field.dispatchEvent(new Event('input', { bubbles: true }));
							field.dispatchEvent(new Event('change', { bubbles: true }));
							field.dispatchEvent(new Event('blur', { bubbles: true }));
						}
					};

					fill('city', suburb);
					fill('zip', postcode);

					// console.log("✅ AUSTRALIA ADDRESS AUTO-FILLED", { streetValue, suburb, state, postcode });
					document.dispatchEvent(new CustomEvent('forminator:field:validate'));
				});
			}

			// Re-run when Forminator moves between steps
			document.addEventListener('DOMContentLoaded', initAutocomplete);
			document.addEventListener('forminator:form:next', initAutocomplete);
			document.addEventListener('forminator:form:prev', initAutocomplete);
			new MutationObserver(initAutocomplete).observe(document.body, { childList: true, subtree: true });

			initAutocomplete();
		})();
	</script>
	<?php
}











// zipcode validations
add_action('wp_footer', function () {

	if (is_page(array(2270, 2335))) { ?>

		<script>

			jQuery(document).on('mouseenter', '.mce-container .mce-btn', function () {
				jQuery('html').css('overflow-y', 'hidden');
			});
			jQuery(document).on('mouseleave', '.mce-container .mce-btn', function () {
				jQuery('html').css('overflow-y', 'auto');
			});


			document.addEventListener("DOMContentLoaded", function () {


				const interval = setInterval(function () {
					const zipField = document.querySelector('input[name="address-1-zip"]');

					if (zipField) {
						clearInterval(interval);

						// Create error message if not already present
						if (!zipField.nextElementSibling || !zipField.nextElementSibling.classList.contains('custom-zip-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-zip-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							zipField.insertAdjacentElement('afterend', errorDiv);
						}

						// Validation function
						const validateZip = function () {
							const errorDiv = zipField.nextElementSibling;
							let value = zipField.value;
							let error = '';

							// Remove non-numeric characters immediately
							value = value.replace(/[^0-9]/g, '');

							// Limit to max 4 digits
							if (value.length > 4) {
								value = value.slice(0, 4);
								error = 'Maximum 4 digits allowed.';
							}

							// Update the field value after corrections
							zipField.value = value;

							// Show/hide error and border
							if (error) {
								zipField.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
							} else {
								zipField.style.border = '';
								errorDiv.style.display = 'none';
							}
						};

						// Trigger on both input and blur
						zipField.addEventListener('input', validateZip);
						zipField.addEventListener('blur', validateZip);
					}

				}, 500);
			});
		</script>

		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const aaswno = document.querySelector('input[name="text-2"]');

					if (aaswno) {
						clearInterval(interval);

						// Create error message if not already present
						if (!aaswno.nextElementSibling || !aaswno.nextElementSibling.classList.contains('custom-zip-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-zip-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							aaswno.insertAdjacentElement('afterend', errorDiv);
						}

						// Validation function
						const validateZip = function () {
							const errorDiv = aaswno.nextElementSibling;
							let value = aaswno.value;
							let error = '';

							// Remove non-numeric characters immediately
							value = value.replace(/[^0-9]/g, '');

							// Limit to max 4 digits
							if (value.length > 20) {
								value = value.slice(0, 20);
								error = 'Maximum 20 digits allowed.';
							}

							// Update the field value after corrections
							aaswno.value = value;

							// Show/hide error and border
							if (error) {
								aaswno.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
							} else {
								aaswno.style.border = '';
								errorDiv.style.display = 'none';
							}
						};

						// Trigger on both input and blur
						aaswno.addEventListener('input', validateZip);
						aaswno.addEventListener('blur', validateZip);
					}
				}, 500);
			});
		</script>

		<script>
			document.addEventListener("DOMContentLoaded", function () {

				const interval = setInterval(function () {
					const yearsexp = document.querySelector('input[name="text-3"]');

					if (yearsexp) {
						clearInterval(interval);

						// Create error message if not already present
						if (!yearsexp.nextElementSibling || !yearsexp.nextElementSibling.classList.contains('custom-zip-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-year-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							yearsexp.insertAdjacentElement('afterend', errorDiv);
						}

						yearsexp.addEventListener('input', function () {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';

							// Remove non-numeric characters immediately
							value = value.replace(/[^0-9]/g, '');

							// Limit to max 6 digits
							if (value.length > 2) {
								value = value.slice(0, 8);
								error = 'Maximum 2 digits allowed.';
							}
							if (value > 40) {
								value = value.slice(0, 8);
								error = 'Please add experience below 40.';
							}

							// Update the field value after corrections
							this.value = value;

							// Show/hide error and border
							if (error) {
								this.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
							} else {
								this.style.border = '';
								errorDiv.style.display = 'none';
							}
						});
					}
				}, 500);
			});
		</script>

		<script>
			/*document.addEventListener("DOMContentLoaded", function() {

				const interval = setInterval(function() {
					const loginField = document.querySelector('input[name="text-1"]');
					//const nextButton = document.querySelector('.forminator-button-next');

					if (loginField) {
						clearInterval(interval);

						// Create error message if not already present
						if (!loginField.nextElementSibling || !loginField.nextElementSibling.classList.contains('custom-login-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-login-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							loginField.insertAdjacentElement('afterend', errorDiv);
						}

						loginField.addEventListener('blur', function() {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';

							// Remove non-alphanumeric characters and spaces immediately
							value = value.replace(/[^a-zA-Z0-9]/g, '');

							// Limit to max 20 characters
							if (value.length > 20) {
								value = value.slice(0, 20);
								error = 'Maximum 20 characters allowed.';
							}

							// Update the field value after corrections
							this.value = value;

							// Show min length error if less than 15 characters
							if (value.length < 5) {
								error = 'Minimum 5 characters required.';   
							}

							// Show/hide error and border
							if (error) {  
								this.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
		
							} else {
								this.style.border = '';
								errorDiv.style.display = 'none';
		
							}
						});
					}
				}, 500);
			});*/
		</script>

		<script>
			// Check email validation
			document.addEventListener("DOMContentLoaded", function () {
				//console.log('55');
				const interval = setInterval(function () {
					const emailField = document.querySelector('input[name="email-1"]');
					const email_field = jQuery('input[name="email-1"]'); // jQuery object
					const nextButton = document.querySelector('.forminator-button-next');

					if (emailField) {
						clearInterval(interval);

						// Create error message if not already present
						if (!emailField.nextElementSibling || !emailField.nextElementSibling.classList.contains('custom-login-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-login-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							emailField.insertAdjacentElement('afterend', errorDiv);
						}

						['input', 'blur'].forEach(evt => {
							emailField.addEventListener(evt, function () {
								const email = this.value.trim();
								const errorDiv = this.nextElementSibling;

								if (!email) return;

								jQuery.post(
									'<?php echo admin_url("admin-ajax.php"); ?>',
									{
										action: 'check_email_exists',
										email: email
									},
									function (data) {
										if (!data.success) {
											// Email exists or invalid
											jQuery(emailField).css('border', '2px solid red');
											jQuery(errorDiv).text(data.data.message).show();
											if (nextButton) {
												nextButton.disabled = true;
												nextButton.style.opacity = '0.5';
												nextButton.style.pointerEvents = 'none';
											}
										} else {
											// Email available
											jQuery(emailField).css('border', '');
											jQuery(errorDiv).hide();
											if (nextButton) {
												nextButton.disabled = false;
												nextButton.style.opacity = '1';
												nextButton.style.pointerEvents = 'auto';
											}
										}
									}
								);
							});
						});
					}
				}, 500);
			});
		</script>

		<script>
			/*document.addEventListener("DOMContentLoaded", function() {
	
				const interval = setInterval(function() {
					const addressField = document.querySelector('input[name="address-1-street_address"]');

					if (addressField) {
						clearInterval(interval);

						// Create error message container if not present
						if (!addressField.nextElementSibling || !addressField.nextElementSibling.classList.contains('custom-address-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-address-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							addressField.insertAdjacentElement('afterend', errorDiv);
						}

						addressField.addEventListener('input', function() {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';

							// Remove invalid special characters
							const validPattern = /^[a-zA-Z0-9\s,.\-\/]*$/;

							if (!validPattern.test(value)) {
								error = 'Only letters, numbers, spaces, commas, periods, hyphens, and slashes are allowed.';
								// Remove invalid chars live
								value = value.replace(/[^a-zA-Z0-9\s,.\-\/]/g, '');
							}

							// Limit max 100 characters
							if (value.length > 100) {
								value = value.slice(0, 100);
								error = 'Maximum 100 characters allowed.';
							}

							// Update field value
							this.value = value;

							if (errorDiv) {	
								// Show or hide error
								if (error) {
									this.style.border = '2px solid red';
									errorDiv.textContent = error;
									errorDiv.style.display = 'block';
								} else {
									this.style.border = '';
									errorDiv.style.display = 'none';
								}
							}
						});
					}
	
	
 
				}, 500);
			});*/

		</script>

		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const cityField = document.querySelector('input[name="address-1-city"]');

					if (cityField) {
						clearInterval(interval);

						// Create error message container if not already present
						if (!cityField.nextElementSibling || !cityField.nextElementSibling.classList.contains('custom-city-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-city-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							cityField.insertAdjacentElement('afterend', errorDiv);
						}

						cityField.addEventListener('input', function () {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';

							// Allow only letters and spaces
							const validPattern = /^[a-zA-Z\s.,-]*$/;

							if (!validPattern.test(value)) {
								error = 'Only letters, spaces, commas, periods, and hyphens are allowed.';
								value = value.replace(/[^a-zA-Z\s.,-]/g, '');
							}

							// Limit to max 50 characters
							if (value.length > 50) {
								value = value.slice(0, 50);
								error = 'Maximum 50 characters allowed.';
							}

							// Update field value
							this.value = value;

							// Show or hide error
							if (error) {
								this.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
							} else {
								this.style.border = '';
								errorDiv.style.display = 'none';
							}
						});
					}
				}, 500);
			});
		</script>

		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const companyField = document.querySelector('input[name="name-1"]');
					//const currentStep = companyField?.closest('.forminator-pagination');
					const nextButton = document.querySelector('.forminator-button-next');

					if (companyField) {
						clearInterval(interval);

						// Create error message container if not already present
						if (!companyField.nextElementSibling || !companyField.nextElementSibling.classList.contains('custom-city-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-company-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							companyField.insertAdjacentElement('afterend', errorDiv);
						}

						companyField.addEventListener('input', function () {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';
							let hasError = false;

							// Allow only letters and spaces
							const validPattern = /^[a-zA-Z\s]*$/;

							if (!validPattern.test(value)) {
								error = 'Only letters and spaces are allowed.';
								value = value.replace(/[^a-zA-Z\s]/g, '');
								hasError = true;
							}

							// Limit to max 50 characters
							if (value.length > 50) {
								value = value.slice(0, 50);
								error = 'Maximum 50 characters allowed.';
								hasError = true;
							}

							// Update field value
							this.value = value;

							// Show or hide error
							if (error) {
								this.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
								if (nextButton) {
									nextButton.disabled = hasError;
									nextButton.style.opacity = hasError ? '0.5' : '1';
									nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
								}
							} else {
								this.style.border = '';
								errorDiv.style.display = 'none';
								if (nextButton) {
									nextButton.disabled = hasError;
									nextButton.style.opacity = hasError ? '0.5' : '1';
									nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
								}
							}
						});
					}
				}, 500);
			});
		</script>

		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const accredition = document.querySelector('input[name="text-4"]');
					const nextButton = document.querySelector('.forminator-button-next');

					if (accredition) {
						clearInterval(interval);

						// Create error message container if not already present
						if (!accredition.nextElementSibling || !accredition.nextElementSibling.classList.contains('custom-acc-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-company-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							accredition.insertAdjacentElement('afterend', errorDiv);
						}

						accredition.addEventListener('input', function () {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';
							let hasError = false;

							// Allow only letters and spaces
							const validPattern = /^[a-zA-Z\s]*$/;

							if (!validPattern.test(value)) {
								error = 'Only letters and spaces are allowed.';
								value = value.replace(/[^a-zA-Z\s]/g, '');
								hasError = true;
							}

							// Limit to max 50 characters
							if (value.length > 100) {
								value = value.slice(0, 100);
								error = 'Maximum 100 characters allowed.';
								hasError = true;
							}

							// Update field value
							this.value = value;

							// Show or hide error
							if (error) {
								this.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
								if (nextButton) {
									nextButton.disabled = hasError;
									nextButton.style.opacity = hasError ? '0.5' : '1';
									nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
								}
							} else {
								this.style.border = '';
								errorDiv.style.display = 'none';
								if (nextButton) {
									nextButton.disabled = hasError;
									nextButton.style.opacity = hasError ? '0.5' : '1';
									nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
								}
							}
						});
					}
				}, 500);
			});
		</script>


		<script>
			// Area of specialization
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const specialization = document.querySelector('input[name="text-5"]');
					const nextButton = document.querySelector('.forminator-button-next');

					if (specialization) {
						clearInterval(interval);

						// Create error message container if not already present
						if (!specialization.nextElementSibling || !specialization.nextElementSibling.classList.contains('custom-city-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-company-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							specialization.insertAdjacentElement('afterend', errorDiv);
						}

						specialization.addEventListener('input', function () {
							const errorDiv = this.nextElementSibling;
							let value = this.value;
							let error = '';
							let hasError = false;

							// Allow only letters and spaces
							const validPattern = /^[a-zA-Z\s,]*$/;

							if (!validPattern.test(value)) {
								error = 'Only letters and spaces are allowed.';
								value = value.replace(/[^a-zA-Z\s,]/g, '');
								hasError = true;
							}

							// Limit to max 50 characters
							if (value.length > 300) {
								value = value.slice(0, 300);
								error = 'Maximum 300 characters allowed.';
								hasError = true;
							}

							// Update field value
							this.value = value;

							// Show or hide error
							if (error) {
								this.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
								if (nextButton) {
									nextButton.disabled = hasError;
									nextButton.style.opacity = hasError ? '0.5' : '1';
									nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
								}
							} else {
								this.style.border = '';
								errorDiv.style.display = 'none';
								if (nextButton) {
									nextButton.disabled = hasError;
									nextButton.style.opacity = hasError ? '0.5' : '1';
									nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
								}
							}
						});
					}
				}, 500);
			});
		</script>


		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const firstNameField = document.querySelector('input[name="name-2"]');
					const lastNameField = document.querySelector('input[name="name-3"]');

					const nextButton = document.querySelector('.forminator-button-next');

					if (firstNameField && lastNameField) {
						clearInterval(interval);

						// Generic validation function
						function setupValidation(field, maxLength) {
							// Create error container if missing
							if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('custom-error')) {
								const errorDiv = document.createElement('div');
								errorDiv.className = 'custom-error';
								errorDiv.style.color = 'red';
								errorDiv.style.marginTop = '5px';
								errorDiv.style.display = 'none';
								field.insertAdjacentElement('afterend', errorDiv);
							}

							['input', 'blur'].forEach(evt => {
								field.addEventListener(evt, function () {
									const errorDiv = this.nextElementSibling;
									let value = this.value.trim();
									let error = '';
									let hasError = false;

									const validPattern = /^[a-zA-Z\s]*$/;

									if (!validPattern.test(value)) {
										error = 'Only letters and spaces are allowed.';
										value = value.replace(/[^a-zA-Z\s]/g, '');
										hasError = true;
									}

									if (value.length > maxLength) {
										value = value.slice(0, maxLength);
										error = `Maximum ${maxLength} characters allowed.`;
										hasError = true;
									}

									this.value = value;

									if (error) {
										this.style.border = '2px solid red';
										errorDiv.textContent = error;
										errorDiv.style.display = 'block';
										if (nextButton) {
											nextButton.disabled = hasError;
											nextButton.style.opacity = hasError ? '0.5' : '1';
											nextButton.style.pointerEvents = hasError ? 'none' : 'auto';
										}
									} else {
										this.style.border = '';
										errorDiv.style.display = 'none';
										if (nextButton) {
											nextButton.disabled = false;
											nextButton.style.opacity = '1';
											nextButton.style.pointerEvents = 'auto';
										}
									}


								});
							});
						}

						// Apply validation
						setupValidation(firstNameField, 20); // First name
						setupValidation(lastNameField, 20);  // Last name
					}
				}, 500);
			});


		</script>

		<?php
	}
});


// For logged-in and logged-out users
add_action('wp_ajax_check_email_exists', 'check_email_exists_callback');
add_action('wp_ajax_nopriv_check_email_exists', 'check_email_exists_callback');

function check_email_exists_callback()
{
	if (!isset($_POST['email'])) {
		wp_send_json_error(['message' => 'No email provided']);
	}

	$email = sanitize_email($_POST['email']);

	if (email_exists($email)) {
		wp_send_json_error(['message' => 'Email already registered']);
	} else {
		wp_send_json_success(['message' => 'Email available']);
	}
}



// Enqueue Select2 and initialize it on page load on woocommerce checkout
add_action('wp_footer', function () {
	if (is_page(array(2171))) {
		?>
		<script>
			jQuery(document).ready(function ($) {
				$('input#wc-stripe-new-payment-method').prop('checked', true);
			});
			document.addEventListener('DOMContentLoaded', function () {
				// Wait for jQuery and Select2 to load
				const interval = setInterval(function () {
					if (typeof jQuery !== 'undefined' && jQuery().select2) {
						clearInterval(interval);

						// Replace 'select[name="your_field_name"]' with your target dropdown selector
						jQuery('select[name="billing_city"]').select2({
							placeholder: 'Select a suburb',
							allowClear: true,
							width: '100%'
						});
					}
				}, 300);
			});
		</script>



		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const zipField = document.querySelector('input[name="billing_postcode"]');

					if (zipField) {
						clearInterval(interval);

						// Create error message if not already present
						if (!zipField.nextElementSibling || !zipField.nextElementSibling.classList.contains('custom-zip-error')) {
							const errorDiv = document.createElement('div');
							errorDiv.className = 'custom-zip-error';
							errorDiv.style.color = 'red';
							errorDiv.style.marginTop = '5px';
							errorDiv.style.display = 'none';
							zipField.insertAdjacentElement('afterend', errorDiv);
						}

						// Validation function
						const validateZipW = function () {
							const errorDiv = zipField.nextElementSibling;
							let value = zipField.value;
							let error = '';

							// Remove non-numeric characters immediately
							value = value.replace(/[^0-9]/g, '');

							// Limit to max 4 digits
							if (value.length > 4) {
								value = value.slice(0, 4);
								error = 'Maximum 4 digits allowed.';
							}

							// Update the field value after corrections
							zipField.value = value;

							// Show/hide error and border
							if (error) {
								zipField.style.border = '2px solid red';
								errorDiv.textContent = error;
								errorDiv.style.display = 'block';
							} else {
								zipField.style.border = '';
								errorDiv.style.display = 'none';
							}
						};

						// Trigger on both input and blur
						zipField.addEventListener('input', validateZipW);
						zipField.addEventListener('blur', validateZipW);
					}
				}, 500);
			});  
		</script>
		<?php
	}
});



// Enqueue Select2 and initialize it on page load on woocommerce checkout
add_action('wp_footer', function () {
	if (is_page(array(260))) {
		?>
		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const firstNameField = document.querySelector('input[name="name-1"]');
					const subject = document.querySelector('input[name="text-1"]');

					//const nextButton = document.querySelector('.forminator-button-next');

					if (firstNameField && subject) {
						clearInterval(interval);

						// Generic validation function
						function setupValidation(field, maxLength) {
							// Create error container if missing
							if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('custom-error')) {
								const errorDiv = document.createElement('div');
								errorDiv.className = 'custom-error';
								errorDiv.style.color = 'red';
								errorDiv.style.marginTop = '5px';
								errorDiv.style.display = 'none';
								field.insertAdjacentElement('afterend', errorDiv);
							}

							['input', 'blur'].forEach(evt => {
								field.addEventListener(evt, function () {
									const errorDiv = this.nextElementSibling;
									let value = this.value;
									let error = '';
									let hasError = false;

									// Allow only alphabets and spaces
									const validPattern = /^[A-Za-z\s]*$/;

									if (!validPattern.test(value)) {
										error = 'Only letters and spaces are allowed.';
										// Remove invalid characters, but keep valid letters & spaces
										value = value.replace(/[^A-Za-z\s]/g, '');
										hasError = true;
									}

									if (value.length > maxLength) {
										value = value.slice(0, maxLength);
										error = `Maximum ${maxLength} characters allowed.`;
										hasError = true;
									}

									this.value = value;

									if (error) {
										this.style.border = '2px solid red';
										errorDiv.textContent = error;
										errorDiv.style.display = 'block';
									} else {
										this.style.border = '';
										errorDiv.style.display = 'none';
									}
								});
							});

						}

						// Apply validation
						setupValidation(firstNameField, 30); // First name
						setupValidation(subject, 50); // First name      
					}
				}, 500);
			});
		</script>
		<?php
	}
});


////////

add_action('wp_footer', function () {
	if (is_page(array(260))) {
		?>
		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
					const firstNameField = document.querySelector('input[name="name-1"]');
					const subject = document.querySelector('input[name="text-1"]');

					//const nextButton = document.querySelector('.forminator-button-next');

					if (firstNameField && subject) {
						clearInterval(interval);

						// Generic validation function
						function setupValidation(field, maxLength) {
							// Create error container if missing
							if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('custom-error')) {
								const errorDiv = document.createElement('div');
								errorDiv.className = 'custom-error';
								errorDiv.style.color = 'red';
								errorDiv.style.marginTop = '5px';
								errorDiv.style.display = 'none';
								field.insertAdjacentElement('afterend', errorDiv);
							}

							['input', 'blur'].forEach(evt => {
								field.addEventListener(evt, function () {
									const errorDiv = this.nextElementSibling;
									let value = this.value;
									let error = '';
									let hasError = false;

									// Allow only alphabets and spaces
									const validPattern = /^[A-Za-z\s]*$/;

									if (!validPattern.test(value)) {
										error = 'Only letters and spaces are allowed.';
										// Remove invalid characters, but keep valid letters & spaces
										value = value.replace(/[^A-Za-z\s]/g, '');
										hasError = true;
									}

									if (value.length > maxLength) {
										value = value.slice(0, maxLength);
										error = `Maximum ${maxLength} characters allowed.`;
										hasError = true;
									}

									this.value = value;

									if (error) {
										this.style.border = '2px solid red';
										errorDiv.textContent = error;
										errorDiv.style.display = 'block';
									} else {
										this.style.border = '';
										errorDiv.style.display = 'none';
									}
								});
							});

						}

						// Apply validation
						setupValidation(firstNameField, 30); // First name
						setupValidation(subject, 50); // First name      
					}
				}, 500);
			});
		</script>
		<?php
	}
});

add_filter('woocommerce_checkout_fields', 'aakash_add_phone_placeholder');
function aakash_add_phone_placeholder($fields)
{
	// Change the placeholder for billing phone
	$fields['billing']['billing_phone']['placeholder'] = '+61 412 100 200';
	return $fields;
}

add_action('wp_footer', 'aakash_checkout_phone_inline_validation_js');
function aakash_checkout_phone_inline_validation_js()
{
	if (is_checkout() && !is_order_received_page()): ?>
		<script type="text/javascript">
			jQuery(function ($) {

				// Function to validate Australian phone numbers
				function validatePhone(phone) {
					const clean = phone.replace(/\s|-/g, '');
					const auPattern = /^(?:\+?61|0)[2-478][0-9]{8}$/;
					return auPattern.test(clean) && clean.length <= 12;
				}

				// Select WooCommerce phone field
				const $phoneField = $('#billing_phone');

				// Create inline error container
				const $errorMsg = $('<span class="aakash-phone-error" style="color:#b81c23; font-size:13px; display:block; margin-top:3px;"></span>');
				$phoneField.after($errorMsg);

				// Listen for user input
				$phoneField.on('input blur', function () {
					const value = $(this).val().trim();

					if (value === '') {
						$errorMsg.text('Please enter your phone number.');
						$(this).addClass('woocommerce-invalid');
					} else if (!validatePhone(value)) {
						$errorMsg.text('Please enter a valid Australian phone number (max 12 digits).');
						$(this).addClass('woocommerce-invalid');
					} else {
						$errorMsg.text('');
						$(this).removeClass('woocommerce-invalid');
					}
				});

				// Block checkout if invalid
				$('form.checkout').on('checkout_place_order', function () {
					const phoneVal = $phoneField.val().trim();
					if (!validatePhone(phoneVal)) {
						$phoneField.focus();
						$('html, body').animate({ scrollTop: $phoneField.offset().top - 100 }, 400);
						return false; // prevent form submit
					}
				});
			});
		</script>
	<?php endif;
}

add_filter('woocommerce_checkout_fields', 'make_billing_phone_required');
function make_billing_phone_required($fields)
{
	$fields['billing']['billing_phone']['required'] = true; // Make it required
	$fields['billing']['billing_phone']['label'] = 'Phone Number'; // Optional: change label
	return $fields;
}

// Show loader on checkout
add_action('wp_footer', function () {

	if (is_checkout()) {
		if (is_user_logged_in()) {
			?>
			<script>
				jQuery(function ($) {
					var loader = $('#loader');
					loader.show(); // you can use .hide() if you don't want animation

					// Show loader when processing starts   
					$(document).ajaxSend(function (event, xhr, settings) {
						if (settings.url.indexOf('wc-ajax=checkout') !== -1 ||
							settings.url.indexOf('update_order_review') !== -1 ||
							settings.url.indexOf('wc-ajax=update_order_review') !== -1) {
							loader.fadeIn(100);
							//console.log('dfdfsdfd');
						}
					});

					// Intercept WooCommerce checkout response
					$(document).ajaxSuccess(function (event, xhr, settings) {
						if (settings.url.indexOf('wc-ajax=checkout') !== -1) {
							try {
								var response = JSON.parse(xhr.responseText);

								if (response.result === 'success') {
									// Checkout passed validation
									console.log("Checkout success - show popup");
									loader.fadeOut(200);

								} else if (response.result === 'failure') {
									// Validation errors present
									console.log("Checkout failed - show error popup");
									loader.fadeOut(200);

									// Show your error popup here
									if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
										elementorProFrontend.modules.popup.showPopup({
											id: 2720
										});
									}
								}
							} catch (e) {
								console.error("Error parsing checkout response", e);
							}
						}
					});

					// Hide loader when processing ends
					$(document).on('ajaxStop', function () {
						setTimeout(function () {
							loader.fadeOut(200);
							jQuery('body').css('overflow', 'unset');
						}, 2000);
					});

					// Also cover WooCommerce's built-in class toggles
					$(document).on('change', 'form.checkout', function () {
						if ($('form.checkout').hasClass('processing')) {
							loader.fadeIn(200);
							console.log('processing start2');
						} else {
							setTimeout(function () {
								loader.fadeOut(200);
								jQuery('body').css('overflow', 'unset');
							}, 2000);
						}
					});
				});
			</script>

		<?php } else {
			?>
			<script>
				jQuery(function ($) {
					$('#login-popup-id').on('click', function () {
						if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
							//console.log("Popup module ready, opening...");
							elementorProFrontend.modules.popup.showPopup({ id: 2326 }); // Replace with your Popup ID
						}
					});
				});
			</script>
			<?php
		}


	}
});


/* Add JS on Add Payment Methods page 
for showing custom loader
*/
add_action('wp_footer', function () {
	if (is_page(array(2172))) {
		?>
		<script>
			jQuery(function ($) {
				$('li.menu-item-2691').addClass('current-menu-item');

				$('#place_order').on('click', function () {

					var loader = $('#loader');
					loader.show();

					// Show loader when processing starts   
					$(document).ajaxSend(function (event, xhr, settings) {
						if (
							settings.url.indexOf('wc-ajax=add_payment_method') !== -1 ||
							settings.url.indexOf('wc-ajax=checkout') !== -1 ||
							settings.url.indexOf('update_order_review') !== -1
						) {
							loader.fadeIn(150);
						}
					});

					$(document).ajaxComplete(function (event, xhr, settings) {
						if (
							settings.url.indexOf('wc-ajax=add_payment_method') !== -1 ||
							settings.url.indexOf('wc-ajax=checkout') !== -1 ||
							settings.url.indexOf('update_order_review') !== -1
						) {
							loader.fadeOut(150);
						}
					});

					setTimeout(function () {
						loader.fadeOut(200);
						jQuery('body').css('overflow', 'unset');
					}, 5000);

				});
			});
		</script>
		<?php
	}
});