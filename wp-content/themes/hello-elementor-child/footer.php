<?php
/**
 * The template for displaying the footer.
 *
 * Contains the body & html closing tags.
 *
 * @package HelloElementor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

//Get WooCommerce checkout page url
$checkout_page_url = wc_get_checkout_url();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	if ( hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-footer' );
		} else {
			get_template_part( 'template-parts/footer' );
		}
	}
}
?>

<?php wp_footer(); ?>

<script>

jQuery(document).ready(function($){
	
	// Wait until TinyMCE is ready
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.forminator-textarea',
            menubar: false,
            statusbar: false,
            toolbar: 'bold italic underline bullist numlist link unlink undo redo',
            height: 200,
            wpautop: true,
            branding: false,
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save(); // ensure content updates textarea
                });
            }
        });
    }

	
	<?php 
	if(is_page( array( 2270, 2335 ) )){
	?>
		// Show the loader immediately when the page starts loading
		//$('#loader').show(); 
		console.log('loading');
		// Hide it after 2 seconds (2000 milliseconds)
		setTimeout(function() {
			$('#loader').fadeOut('slow'); // you can use .hide() if you don't want animation
			$('.forminator-checkbox').removeAttr('title');   
			$('body').css('overflow','unset'); 
		}, 2000);
		
	<?php } ?>
	

	// eye button toggle js for rooms for hire signup form
	$(document).on('click', '.hire-registration-form .toggle-password-eye', function() {
		const $icon = $(this);
		// --- FIX: This selector is more robust ---
		const $input = $icon.closest('.password-input-container').find('input');
		
		if ($input.attr('type') === 'password') {
			$input.attr('type', 'text');
			$icon.removeClass('fa-eye').addClass('fa-eye-slash');
		} else {
			$input.attr('type', 'password');
			$icon.removeClass('fa-eye-slash').addClass('fa-eye');
		}
	});
	  
}); 

 
	<?php 
	// show popup for employer signup success
	if(is_page(2270)){
	?>
		jQuery(window).on('elementor/frontend/init', function() {
		  if (window.location.href.indexOf("success=true") > -1) {
			// Delay until popup module is ready
			var checkPopup = setInterval(function() {
			  if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
				clearInterval(checkPopup);
				//console.log("Popup module ready, opening...");
				elementorProFrontend.modules.popup.showPopup({ id: 2325 }); // Replace with your Popup ID
			  }
			}, 1500);
		  }
		}); 
	
	<?php 
	}
	// show popup for locum signup success
	if(is_page(2335)){
	?>
		jQuery(window).on('elementor/frontend/init', function() {
		  if (window.location.href.indexOf("success=true") > -1) {
			// Delay until popup module is ready
			var checkPopup = setInterval(function() {
			  if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
				clearInterval(checkPopup);
				//console.log("Popup module ready, opening...");
				elementorProFrontend.modules.popup.showPopup({ id: 2672 }); // Replace with your Popup ID
			  }
			}, 1500);
		  }
		}); 
	<?php 
	}
	?>

	document.addEventListener('DOMContentLoaded', function() {
		// Select the link (<a> tag) inside the heading with our class
		const myLinkedHeading = document.querySelector('.post-job a');

		if (myLinkedHeading) {
			myLinkedHeading.addEventListener('click', function(event) {
			  if (!document.body.classList.contains('logged-in')) {
				event.preventDefault();
				elementorProFrontend.modules.popup.showPopup({ id: 2326 });
			  }
			});
		}
	});


///////  

<?php 
if(is_page( array( 2270, 2335 ) )){  ?>

	jQuery(document).ready(function($) {

		// Wait for Forminator to load fields (handles AJAX rendering)
		const interval = setInterval(function() {
			const $passwordField = $('input[name="password-1"]');
			const $confirmField = $('input[name="confirm_password-1"]');

			if ($passwordField.length || $confirmField.length) {
				clearInterval(interval);
				//console.log('Fields found');  

				const $fields = $passwordField.add($confirmField);

				$fields.each(function() {
					const $input = $(this);

					// Avoid duplicates
					if ($input.parent().find('.toggle-eye').length) return;

					// Create Font Awesome icon
					const $eye = $('<i class="fa-solid fa-eye toggle-eye"></i>').css({
						position: 'absolute',
						right: '10px',
						top: '50%',
						transform: 'translateY(-50%)',
						cursor: 'pointer',
						'font-size': '16px',
						color: '#555'
					});

					// Ensure parent has positioning
					const $parent = $input.parent();
					if ($parent.css('position') === 'static') {
						$parent.css('position', 'relative');
					}

					// Add icon
					$input.after($eye);

					// Toggle password visibility
					$eye.on('click', function() {
						const type = $input.attr('type') === 'password' ? 'text' : 'password';
						$input.attr('type', type);
						$(this).toggleClass('fa-eye fa-eye-slash');
					});
				});
			}
		}, 500);
	});

<?php } ?>
  
</script>
</body>  
</html>
