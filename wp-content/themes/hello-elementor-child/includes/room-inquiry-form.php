<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the shortcode: [room_inquiry_form]
 */
function rif_register_contact_form_shortcode() {
    add_shortcode( 'room_inquiry_form', 'rif_render_contact_form' );
}
add_action( 'init', 'rif_register_contact_form_shortcode' );

/**
 * Handle the form submission and render the shortcode content.
 */
function rif_render_contact_form( $atts ) {
    
    // --- --- --- --- --- --- --- --- --- ---
    // --- CONFIGURE YOUR FORM HERE ---
    // --- --- --- --- --- --- --- --- --- ---

    // SET THE EMAIL ADDRESS WHERE NOTIFICATIONS WILL BE SENT
    // This now dynamically gets the email from the 'contact_email' ACF custom field

    global $post; // Get the global post object
    $recipient_email = ''; // Initialize the variable

    // Check if we are on a valid post and the ACF function 'get_field' exists
    if ( isset( $post ) && function_exists( 'get_field' ) ) {
        
        // Get the email from your 'contact_email' field for the current post
        $recipient_email = get_field( 'contact_email', $post->ID );
    }

    // FALLBACK: If the 'contact_email' field is empty, or ACF isn't active,
    // or we aren't on a post, send to the site admin so the inquiry is not lost.
    if ( empty( $recipient_email ) || ! is_email( $recipient_email ) ) {
        $recipient_email = get_option( 'admin_email' );
    }

    // SET THE DEFAULT FORM TITLE
    $default_title = 'Learn More About These Rooms';
    
    // --- --- --- --- --- --- --- --- --- ---
    // --- END CONFIGURATION ---
    // --- --- --- --- --- --- --- --- --- ---


    // Process shortcode attributes (e.g., [learn_more_contact_form title="New Title"])
    $attributes = shortcode_atts(
        [
            'title' => $default_title,
        ],
        $atts
    );

    // Variables for form messages
    $form_message = '';
    $form_message_type = ''; // 'success' or 'error'
    $errors = []; // Array to hold individual field errors

    // Process the form submission
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['rif_contact_submit'] ) ) {

        // Verify the nonce for security
        if ( ! isset( $_POST['rif_nonce'] ) || ! wp_verify_nonce( $_POST['rif_nonce'], 'rif_contact_form_action' ) ) {
            $form_message = 'Security check failed. Please try again.';
            $form_message_type = 'error';
        } else {
            
            // Sanitize and retrieve form fields
            $name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( $_POST['contact_name'] ) : '';
            $email   = isset( $_POST['contact_email'] ) ? sanitize_email( $_POST['contact_email'] ) : '';
            $phone   = isset( $_POST['contact_phone'] ) ? sanitize_text_field( $_POST['contact_phone'] ) : '';
            $subject = isset( $_POST['contact_subject'] ) ? sanitize_textarea_field( $_POST['contact_subject'] ) : '';

            // --- Individual Field Validation ---
            if ( empty( $name ) ) {
                $errors['name'] = 'This field is required. Please input your name.';
            }
            if ( empty( $email ) ) {
                $errors['email'] = 'This field is required. Please input your email.';
            } elseif ( ! is_email( $email ) ) {
                $errors['email'] = 'Please provide a valid email address.';
            }
            if ( empty( $phone ) ) {
                $errors['phone'] = 'This field is required. Please input your phone number.';
            }
            if ( empty( $subject ) ) {
                $errors['subject'] = 'This field is required. Please input your subject.';
            }
            
            // Check if there are any errors
            if ( ! empty( $errors ) ) {
                $form_message = 'Please correct the errors below and try again.';
                $form_message_type = 'error';
            } else {
                
                // All good, let's build and send the email
                $email_subject = 'New Inquiry: ' . $name;
                $email_headers = [ 'Reply-To: ' . $name . ' <' . $email . '>' ];
                
                $email_body  = "You have received a new message from your website contact form.\n\n";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Phone: $phone\n";
                $email_body .= "Subject: \n$subject\n";

                // Send the email
                if ( wp_mail( $recipient_email, $email_subject, $email_body, $email_headers ) ) {
                    $form_message = 'Message sent successfully! The owner will respond shortly.';
                    $form_message_type = 'success';
                } else {
                    $form_message = 'There was an error sending your message. Please try again later.';
                    $form_message_type = 'error';
                }
            }
        }
    }

    // Start output buffering to capture all HTML/CSS
    ob_start();
    ?>
    
    <style>
        .rif-contact-form-wrapper {
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }
        .rif-contact-form-wrapper *,
        .rif-contact-form-wrapper *::before,
        .rif-contact-form-wrapper *::after {
            box-sizing: border-box;
        }
        .rif-contact-form-wrapper h2 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-top: 0;
            margin-bottom: 25px;
            font-family: inherit;
        }
        .rif-contact-form-wrapper .form-field {
            margin-bottom: 16px;
        }
        .rif-contact-form-wrapper input[type="text"],
        .rif-contact-form-wrapper input[type="email"],
        .rif-contact-form-wrapper input[type="tel"],
        .rif-contact-form-wrapper textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            font-family: inherit;
            color: #333;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .rif-contact-form-wrapper input[type="text"]::placeholder,
        .rif-contact-form-wrapper input[type="email"]::placeholder,
        .rif-contact-form-wrapper input[type="tel"]::placeholder,
        .rif-contact-form-wrapper textarea::placeholder {
            color: #888;
        }
        .rif-contact-form-wrapper input[type="text"]:focus,
        .rif-contact-form-wrapper input[type="email"]:focus,
        .rif-contact-form-wrapper input[type="tel"]:focus,
        .rif-contact-form-wrapper textarea:focus {
            outline: none;
            border-color: #0073aa; /* WordPress blue, or use #0a6b8a for the teal */
            box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.2);
        }
        
        /* --- NEW ERROR STYLES --- */
        .rif-contact-form-wrapper input.has-error,
        .rif-contact-form-wrapper textarea.has-error {
            border-color: #D8000C; /* Red border for invalid field */
        }
        .rif-contact-form-wrapper input.has-error:focus,
        .rif-contact-form-wrapper textarea.has-error:focus {
             box-shadow: 0 0 0 2px rgba(216, 0, 12, 0.1); /* Faint red glow on focus */
        }
        .rif-field-error {
            color: #D8000C; /* Red error text */
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        /* --- END NEW ERROR STYLES --- */

        .rif-contact-form-wrapper textarea {
            min-height: 120px;
            resize: vertical;
        }
        .rif-contact-form-wrapper button[type="submit"] {
            width: 100%;
            padding: 14px 20px;
            background-color: #0a6b8a; /* Dark Teal from image */
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease;
        }
        .rif-contact-form-wrapper button[type="submit"]:hover {
            background-color: #08556e; /* Slightly darker teal */
        }
        .rif-contact-form-wrapper button[type="submit"]:active {
            transform: scale(0.99);
        }
        
        /* Updated Message Block Styles */
        .rif-form-message {
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 6px;
            font-size: 15px;
            border: 1px solid transparent;
        }
        .rif-form-message.success {
            background-color: #e5f0f4;
            border-color: #a0c8dcff;
            color: #00688F;
        }
        .rif-form-message.error {
            /* This is now for general errors, not field errors */
            background-color: #f8e0e0;
            border-color: #dca0a0;
            color: #8a0a0a;
        }
    </style>
    
    <div class="rif-contact-form-wrapper">
        <h2><?php echo esc_html( $attributes['title'] ); ?></h2>

        <?php if ( ! empty( $form_message ) ) : ?>
            <div class="rif-form-message <?php echo esc_attr( $form_message_type ); ?>">
                <?php echo esc_html( $form_message ); ?>
            </div>
        <?php endif; ?>

        <?php // Don't show form if message was sent successfully
        if ( 'success' !== $form_message_type ) : ?>
            <form id="rif-contact-form" method="POST" action="" novalidate>
                <?php wp_nonce_field( 'rif_contact_form_action', 'rif_nonce' ); ?>
                
                <div class="form-field">
                    <input type="text" id="contact_name" name="contact_name" placeholder="Name *" 
                           class="<?php echo isset( $errors['name'] ) ? 'has-error' : ''; ?>" 
                           value="<?php echo ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset($name) ) ? esc_attr($name) : ''; ?>" required>
                    <?php if ( isset( $errors['name'] ) ) : ?>
                        <span class="rif-field-error"><?php echo esc_html( $errors['name'] ); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-field">
                    <input type="email" id="contact_email" name="contact_email" placeholder="Email *" 
                           class="<?php echo isset( $errors['email'] ) ? 'has-error' : ''; ?>" 
                           value="<?php echo ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset($email) ) ? esc_attr($email) : ''; ?>" required>
                    <?php if ( isset( $errors['email'] ) ) : ?>
                        <span class="rif-field-error"><?php echo esc_html( $errors['email'] ); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-field">
                    <input type="tel" id="contact_phone" name="contact_phone" placeholder="Phone number *" 
                           class="<?php echo isset( $errors['phone'] ) ? 'has-error' : ''; ?>" 
                           value="<?php echo ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset($phone) ) ? esc_attr($phone) : ''; ?>" required pattern="[0-9\+\-\(\)\s]*" title="Please enter a valid phone number.">
                    <?php if ( isset( $errors['phone'] ) ) : ?>
                        <span class="rif-field-error"><?php echo esc_html( $errors['phone'] ); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-field">
                    <textarea id="contact_subject" name="contact_subject" placeholder="Subject *" 
                              class="<?php echo isset( $errors['subject'] ) ? 'has-error' : ''; ?>" 
                              required><?php echo ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset($subject) ) ? esc_textarea($subject) : ''; ?></textarea>
                    <?php if ( isset( $errors['subject'] ) ) : ?>
                        <span class="rif-field-error"><?php echo esc_html( $errors['subject'] ); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-field">
                    <button type="submit" name="rif_contact_submit">Send message</button>
                </div>
            </form>
        <?php endif; ?>
        
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('rif-contact-form');
        if (!form) {
            return;
        }
        
        // Find all fields that have the 'required' attribute
        const requiredFields = form.querySelectorAll('[required]');

        // Function to show an error message
        function showError(field, message) {
            field.classList.add('has-error');
            // Check if an error span already exists from PHP validation
            let errorSpan = field.nextElementSibling;
            if (!errorSpan || !errorSpan.classList.contains('rif-field-error')) {
                // If not, create one
                errorSpan = document.createElement('span');
                errorSpan.classList.add('rif-field-error');
                field.parentNode.insertBefore(errorSpan, field.nextSibling);
            }
            errorSpan.textContent = message;
        }

        // Function to clear an error message
        function clearError(field) {
            field.classList.remove('has-error');
            let errorSpan = field.nextElementSibling;
            if (errorSpan && errorSpan.classList.contains('rif-field-error')) {
                errorSpan.textContent = '';
                // If the error was from PHP, it won't be removed, just cleared.
                // If it was from JS, we can clear it. This is safer.
            }
        }

        // Function to validate a single field
        function validateField(field) {
            const value = field.value.trim();
            
            // 1. Check for 'required'
            if (field.hasAttribute('required') && value === '') {
                showError(field, 'This field is required.');
                return false;
            }

            // 2. Check for 'email' type mismatch
            if (field.type === 'email' && value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                showError(field, 'Please provide a valid email address.');
                return false;
            }
            
            // 3. Check for 'tel' pattern mismatch
            if (field.type === 'tel' && field.hasAttribute('pattern')) {
                // Use the exact pattern from the input
                const pattern = new RegExp(field.getAttribute('pattern'));
                 if (value !== '' && !pattern.test(value)) {
                    showError(field, field.getAttribute('title') || 'Please enter a valid phone number.');
                    return false;
                }
            }

            // If all checks pass
            clearError(field);
            return true;
        }

        // Add 'blur' event listener to each required field
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateField(field);
            });
            
            // Add 'input' event listener to clear error as user types
            field.addEventListener('input', function() {
                // Only clear if it currently has an error
                if (field.classList.contains('has-error')) {
                    validateField(field); // Re-validate, which will clear if it's now valid
                }
            });
        });

        // Add 'submit' event listener to the form
        form.addEventListener('submit', function(event) {
            let isFormValid = true;
            
            // Validate all fields on submit
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                event.preventDefault(); // Stop the form submission
                
                // Update the general message block
                let generalMessage = form.parentNode.querySelector('.rif-form-message.error');
                if (!generalMessage) {
                    // Create it if it doesn't exist
                    generalMessage = document.createElement('div');
                    generalMessage.className = 'rif-form-message error';
                    form.parentNode.insertBefore(generalMessage, form);
                }
                generalMessage.textContent = 'Please correct the errors below and try again.';
            }
        });
    });
    </script>
    <?php
    // Return the buffered content
    return ob_get_clean();
}