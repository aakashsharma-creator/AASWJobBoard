<?php


//
// Add custom CSS in WordPress Admin
function my_admin_custom_css() {
    echo '<style>
        .form-field.term-group,
		tr.form-field.term-group-wrap {
			display: none;
		}
		.term-parent-wrap {
            display: none !important;
        }
    </style>';
}
add_action('admin_head', 'my_admin_custom_css');

add_action( 'init', function() {
    // First unregister the taxonomy if already registered
    unregister_taxonomy( 'job_listing_type' );

    // Re-register it without hierarchy
    register_taxonomy( 'job_listing_type', 'job_listing', array(
        'hierarchical'      => false, // 🚫 No parent dropdown
        'labels'            => array(
            'name'          => __( 'Job Types' ),
            'singular_name' => __( 'Job Type' ),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'job-type' ),
    ));
}, 11 );

// Update label/placeholder and values
add_filter( 'submit_job_form_fields', function( $fields ) {
    // Get all terms from job_location taxonomy
    $job_locations = get_terms( array(
        'taxonomy'   => 'job_location_category', // 👈 change if your taxonomy is different
        'hide_empty' => false,
    ) );

    // Initialize options array
    $options = array(
        //'' => __( 'Select a Location', 'job_manager' ),
    );

    if ( ! is_wp_error( $job_locations ) && ! empty( $job_locations ) ) {
        foreach ( $job_locations as $location ) {
            $options[ $location->slug ] = esc_html( $location->name );
        }
    }

    // Update job location field to dropdown
	$fields['job']['job_location']['label']     = 'Job Location <br><small>(You can select multiple job location)</small>';
    $fields['job']['job_location']['type']     = 'term-multiselect'; 
    $fields['job']['job_location']['required'] = true;
    $fields['job']['job_location']['options']  = maybe_serialize($options);
	$fields['job']['job_location']['description']     = '';
	$fields['job']['job_location']['placeholder'] = 'Select Locations';
	$fields['job']['job_location']['taxonomy']    = 'job_location_category';  

    // Update Job Title field
    $fields['job']['job_title']['label']       = 'Job Title / Position <br><small>(Job title describes a single position)</small>';
    $fields['job']['job_title']['placeholder'] = 'Job Title / Position ';
	$fields['job']['job_title']['description']     = '(minimum 10 characters)';
    $fields['job']['job_title']['required']    = true;
	
	$fields['job']['job_type']['label']       =  'Type of Employment <br><small>(You can select multiple type of employment)</small>';
	 
	$fields['job']['job_category']['label'] = 'Job Category <br><small>(You can select multiple job category)</small>';
	$fields['job']['job_category']['placeholder'] = 'Select Category';
	
	$fields['job']['job_salary']['placeholder'] = '20000 - 30000';
	$fields['job']['job_salary']['description'] = 'Annual salary';
	//$fields['job']['job_salary_unit']['description'] = 'This field is optional. Leave it empty to use the default salary unit, if one is defined.';
	
	
	$fields['job']['job_description']['label'] = 'Job Description <br><small>(Describe the job position)</small>';
	
	//$fields['job']['application']['label']       =  'Website URL';
	$fields['job']['application']['required']       =  false;
	
	
     
    return $fields;
}); 



// REmove website URL field
add_filter( 'submit_job_form_fields', 'remove_application_url_from_frontend' );
function remove_application_url_from_frontend( $fields ) {
    unset( $fields['job']['application'] );
    return $fields;
}

add_filter( 'job_manager_job_listing_data_fields', 'remove_application_url_field' );
function remove_application_url_field( $fields ) {
    // Remove the Application URL field
    if ( isset( $fields['_application'] ) ) {
        unset( $fields['_application'] );
    }
    return $fields;
}

// REmove Salary Unit field
add_filter( 'submit_job_form_fields', 'remove_job_salary_unit_field' );
function remove_job_salary_unit_field( $fields ) {
    
    // Check and unset the salary unit field if it exists
    if ( isset( $fields['job']['job_salary_unit'] ) ) {
        unset( $fields['job']['job_salary_unit'] );
    }

    return $fields;
}




// Convert multiselect job_location array to string before saving.
// Fix for job_location array when using multiselect
add_filter( 'job_manager_update_job_data', function( $data, $post_id ) {
    if ( ! empty( $data['_job_location'] ) && is_array( $data['_job_location'] ) ) {
        // Convert array to comma-separated string
        $data['_job_location'] = implode( ', ', $data['_job_location'] );
    }
    return $data;
}, 5, 2 );   


  
//Adding new custom fields

/**
 * Add Salary Range dropdown on both frontend & backend job forms
 */
add_filter( 'submit_job_form_fields', 'custom_frontend_salary_range_field' );
add_filter( 'job_manager_job_listing_data_fields', 'custom_backend_salary_range_field' );

/**
 * Salary options (shared between both)
 */
function custom_get_salary_ranges() {
    return array(
		''  => __( '-Select Salary-', 'job_manager' ),
        '10,000-20,000'   => '10,000 – 20,000',
        '20,000-30,000'   => '20,000 – 30,000',
        '30,000-40,000'   => '30,000 – 40,000',
        '40,000-50,000'   => '40,000 – 50,000',
        '50,000-60,000'   => '50,000 – 60,000',
        '60,000-70,000'   => '60,000 – 70,000',
        '70,000-80,000'   => '70,000 – 80,000',
        '80,000-90,000'   => '80,000 – 90,000',
        '90,000-100,000'  => '90,000 – 100,000',
        'above-100,000'  => 'Above 100,000',
    );
}

/**
 * FRONTEND: Add dropdown to "Post a Job" form
 */
function custom_frontend_salary_range_field( $fields ) {
    $fields['job']['job_salary'] = array(
        'label'       => __( 'Salary Range ', 'wp-job-manager' ), 
        'type'        => 'select',
        'options'     => custom_get_salary_ranges(),
        'required'    => false,
		'description' => 'Select the annual salary range for this job listing.',
        'priority'    => 7,
    );
    return $fields;
}

/**
 * BACKEND: Add dropdown in admin job edit
 */
function custom_backend_salary_range_field( $fields ) {
    $fields['_job_salary'] = array(
        'label'       => __( 'Salary Range', 'wp-job-manager' ),
        'type'        => 'select',
        'options'     => custom_get_salary_ranges(),
        'description' => 'Select the annual salary range for this job listing.',
        'priority'    => 5,
    );
    return $fields;
}

/**
 * Ensure the value from frontend saves to backend meta field `_job_salary`
 */
add_action( 'job_manager_update_job_data', function( $job_id, $values ) {
    if ( ! empty( $values['job']['job_salary'] ) ) {
        update_post_meta( $job_id, '_job_salary', sanitize_text_field( $values['job']['job_salary'] ) );
    }
}, 10, 2 );


// Add Job Expiration Date field to Post a Job form
function add_job_expiration_date_field( $fields ) {

    $fields['job']['_job_expires'] = array(
        'label'       => __( 'Job Expiry Date', 'job_manager' ),
        'type'        => 'date', // WPJM supports: text, textarea, select, multiselect, radio, checkbox, date, file
        'required'    => false,  
        'priority'    => 6,
        'placeholder' => 'YYYY-MM-DD',
        'description' => __( 'Select the date this job listing should expire.', 'job_manager' ),
    );

    return $fields;
}
add_filter( 'submit_job_form_fields', 'add_job_expiration_date_field' );

// Save custom job expiration date
function save_job_expiration_date( $job_id, $values ) {
    if ( ! empty( $values['job']['_job_expires'] ) ) {
        update_post_meta(
            $job_id,
            '_job_expires',
            sanitize_text_field( $values['job']['_job_expires'] )
        );
		 update_post_meta(
            $job_id,
            '__job_expires',
            sanitize_text_field( $values['job']['_job_expires'] )
        );
		
		if ( isset( $_POST['job_expiry_date'] ) && ! empty( $_POST['job_expiry_date'] ) ) {
			$expiry_date = sanitize_text_field( $_POST['job_expiry_date'] );

			// Save as post meta
			update_post_meta( $job_id, '_job_expiry_date', $expiry_date );
		}
    }
}
add_action( 'job_manager_update_job_data', 'save_job_expiration_date', 10, 2 );


add_action( 'job_manager_update_job_data', 'save_custom_job_expiry_date_override', 20, 2 );
function save_custom_job_expiry_date_override( $job_id, $values ) {

    if ( isset( $_POST['job_expiry_date'] ) && ! empty( $_POST['job_expiry_date'] ) ) {
        $selected_date = sanitize_text_field( $_POST['job_expiry_date'] );

        // Convert to standard format (YYYY-MM-DD)
        $formatted_date = date( 'Y-m-d', strtotime( $selected_date ) );

        // Update both meta fields to ensure consistency
        update_post_meta( $job_id, 'job_expiry_date', $formatted_date );
        
		update_post_meta(
            $job_id,
            '__job_expires',
            sanitize_text_field( $formatted_date )
        );
		update_post_meta( $job_id, '_job_expires', $formatted_date );
    }
}    

//add_action( 'job_manager_update_job_data', 'force_sync_custom_expiry', 999, 2 );
function force_sync_custom_expiry( $job_id, $values ) {

    if ( isset( $_POST['job_expiry_date'] ) && ! empty( $_POST['job_expiry_date'] ) ) {
        $selected_date = sanitize_text_field( $_POST['job_expiry_date'] );
        $formatted_date = date( 'Y-m-d', strtotime( $selected_date ) );

        // Force update (bypass caching)
        delete_post_meta( $job_id, '_job_expires' );
        update_post_meta( $job_id, '_job_expires', $formatted_date );
    }
}  

add_filter( 'submit_job_form_fields', 'load_custom_job_expiry_date_field' );
function load_custom_job_expiry_date_field( $fields ) {
    global $job_id;

    if ( $job_id ) {
        $fields['job']['job_expiry_date']['value'] = get_post_meta( $job_id, 'job_expiry_date', true );
    }

    return $fields;
}

// Add or override the '_job_expires' field in admin listing
add_filter( 'job_manager_job_listing_data_fields', function( $fields ) {
    global $post;

    // Get another meta value (for example, 'job_expiry_date')
    $custom_expiry = '';
    if ( isset( $post->ID ) ) {
        $custom_expiry = get_post_meta( $post->ID, '__job_expires', true );
    }

    // Add or override the '_job_expires' field
    $fields['_job_expires'] = array(
        'label'       => __( 'Job Expiry Date', 'wp-job-manager' ),
        'type'        => 'text',
        'placeholder' => 'YYYY-MM-DD',
        'description' => 'Custom expiry date for this job.',
        'value'       => ! empty( $custom_expiry ) ? $custom_expiry : get_post_meta( $post->ID, '_job_expires', true ),
    );
    return $fields;
});



/**
* Add new custom fields in admin panel for job form
*/

// Add Experience Levels dropdown field to Post a Job form
function add_experience_level_field( $fields ) {

    // Add field under "job" section
    $fields['job']['experience_level'] = array(
        'label'       => __( 'Experience Levels <br><small>(Select the level of experience required for this job)</small>', 'job_manager' ),
        'type'        => 'select',
        'required'    => true,
        'priority'    => 5, // adjust position
        'placeholder' => 'Select Level',
        'options'     => array(
            ''               => 'Select Level',
            'Beginner'       => 'Beginner',
            'Intermediate'   => 'Intermediate',
            'Experienced'    => 'Experienced',
        ),
    );
	
    return $fields;
}
add_filter( 'submit_job_form_fields', 'add_experience_level_field' );

function custom_datepicker_theme() {
    wp_enqueue_style( 'jquery-ui-datepicker-theme', 'https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css' );
}
add_action( 'wp_enqueue_scripts', 'custom_datepicker_theme' );

add_action( 'wp_enqueue_scripts', 'convert_dropdown_to_select2' );
function convert_dropdown_to_select2() {

    // Enqueue Select2 (already included with WordPress)
    wp_enqueue_script( 'select2' );
    wp_enqueue_style( 'select2' );

    // Initialize Select2 on your field
    add_action( 'wp_footer', function() { ?>
        <script>
        jQuery(document).ready(function($) {

            // Run only on Post a Job page
            if ( $('form.job-manager-form').length ) {

                // Target your dropdown — update the name if yours is different
                const field = $('select[name="experience_level"]');
                if (field.length) {
                    field.select2({
                        placeholder: 'Select Level',
                        allowClear: true,
                        width: '100%',
						minimumResultsForSearch: -1
                    });
                }
				
				const currency_field = $('select[name="job_salary_currency"]');
                if (currency_field.length) {
                    currency_field.select2({
                        placeholder: 'Select Currency',
                        allowClear: true,
                        width: '100%',
						minimumResultsForSearch: -1
                    });
                }
				
				const job_salary_field = $('select[name="job_salary"]');
                if (job_salary_field.length) {
                    job_salary_field.select2({
                        placeholder: '-Select Salary-',
                        allowClear: true,
                        width: '100%',
						minimumResultsForSearch: -1
                    });
                }
				/*const salary_unit = $('select[name="job_salary_unit"]');
                if (salary_unit.length) {
                    salary_unit.select2({
                        placeholder: 'Select Unit',
                        allowClear: true,
                        width: '100%',
						minimumResultsForSearch: -1
                    });
                }*/
				
				const company_info = $('select[name="show_company_info"]');
                if (company_info.length) {
                    company_info.select2({
                        placeholder: '-Select-',
                        allowClear: true,
                        width: '100%',
						minimumResultsForSearch: -1
                    });
                }
				
            }
        });
        </script>
    <?php });
}



// Validate Experience Level field
function validate_experience_level_field( $errors, $fields ) {
    if ( empty( $fields['job']['experience_level'] ) ) {
        $errors->add( 'experience_level_error', __( '<strong>Error:</strong> Please select an Experience Level.', 'job_manager' ) );
    }
    return $errors;
}
add_filter( 'submit_job_form_validate_fields', 'validate_experience_level_field', 10, 2 );

/**
 * Add Experience Level dropdown to Post a Job form (frontend)
 */
function add_experience_level_field_frontend( $fields ) {
    $fields['job']['experience_level'] = array(
        'label'       => __( 'Experience Level', 'job_manager' ),
        'type'        => 'select',
        'required'    => true,
        'priority'    => 5,
        'options'     => array(
            ''        => __( 'Select Level', 'job_manager' ),
            'Beginner'   => __( 'Beginner', 'job_manager' ),
            'Intermediate'     => __( 'Intermediate', 'job_manager' ),
            'Experienced'  => __( 'Experienced', 'job_manager' ),
        ),
    );
    return $fields;
}
add_filter( 'submit_job_form_fields', 'add_experience_level_field_frontend' );

/**
 * Save Experience Level from frontend form
 */
function save_experience_level_field_frontend( $job_id, $values ) {
    if ( isset( $values['job']['experience_level'] ) ) {
        update_post_meta( $job_id, '_experience_level', sanitize_text_field( $values['job']['experience_level'] ) );
    }
}
add_action( 'job_manager_update_job_data', 'save_experience_level_field_frontend', 10, 2 );


/**
 * Add Experience Level dropdown in WP Admin Job Listing (backend)
 */
function add_experience_level_admin_field( $fields ) {
    $fields['_experience_level'] = array(
        'label'       => __( 'Experience Level', 'job_manager' ),
        'type'        => 'select',
        'options'     => array(
            ''        => __( 'Select Level', 'job_manager' ),
            'Beginner'   => __( 'Beginner', 'job_manager' ),
            'Intermediate'     => __( 'Intermediate', 'job_manager' ),
            'Experienced'  => __( 'Experienced', 'job_manager' ),
        ),
        'description' => __( 'Select the level of experience required for this job.', 'job_manager' ),
    );
    return $fields;
}
add_filter( 'job_manager_job_listing_data_fields', 'add_experience_level_admin_field' );








// Remove Parent Location dropdown for job_location_category taxonomy
add_action( 'job_location_category_add_form_fields', 'aasw_remove_parent_location_field' );
add_action( 'job_location_category_edit_form_fields', 'aasw_remove_parent_location_field' );

function aasw_remove_parent_location_field() {
    ?>
    <style>
        .term-parent-wrap {
            display: none !important;
        }
    </style>
    <?php
}

// Also remove from quick edit & bulk edit
add_action( 'admin_head-edit-tags.php', function () {
    $screen = get_current_screen();
    if ( isset($screen->taxonomy) && $screen->taxonomy === 'job_location_category' ) {
        echo '<style>.inline-edit-col .term-parent {display:none !important;}</style>';
    }
});


// Change placeholder text for job_listing post type
function change_job_listing_title_placeholder( $title, $post ) {
    if ( $post->post_type == 'job_listing' ) {
        $title = "Job Title / Position"; 
    }
    return $title;
}
add_filter( 'enter_title_here', 'change_job_listing_title_placeholder', 10, 2 );

// Remove all company related fields from Post a Job form
function remove_company_fields_from_job_form( $fields ) {
    unset( $fields['company'] ); // removes the entire "Company Details" section
    return $fields;
}
add_filter( 'submit_job_form_fields', 'remove_company_fields_from_job_form' );



/**
 * Add custom fields to WP Job Manager (frontend + backend).
 */

// ======================
// 1. Add fields to frontend form
// ======================
add_filter( 'submit_job_form_fields', 'custom_job_manager_fields' );
function custom_job_manager_fields( $fields ) {
    // Apply Link field
    $fields['job']['apply_link'] = array(
        'label'       => __( 'Apply Link <br><small>(Add the link to your company website or job portal where candidates can apply directly.)</small>', 'job_manager' ),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'www.example.com/application',
        'priority'    => 14, 
    );

    // How to Apply Direction field
    $fields['job']['how_to_apply'] = array(
        'label'       => __( 'How to Apply Direction', 'job_manager' ),
        'type'        => 'wp-editor', // gives text area with editor
        'required'    => false,
        'placeholder' => 'Write the description here',
        'priority'    => 13,
		'description' => __( 'Provide clear instructions for applicants on how to apply for this job.', 'job_manager' )
    );

    return $fields;
}

// ======================
// 2. Save fields
// ======================
add_action( 'job_manager_update_job_data', 'custom_job_manager_save_fields', 10, 2 );
function custom_job_manager_save_fields( $job_id, $values ) {
    if ( ! empty( $values['job']['apply_link'] ) ) {
        update_post_meta( $job_id, '_apply_link', sanitize_text_field( $values['job']['apply_link'] ) );
		update_post_meta( $job_id, 'job_link', sanitize_text_field( $values['job']['apply_link'] ) );
    }
    if ( ! empty( $values['job']['how_to_apply'] ) ) {
        update_post_meta( $job_id, '_how_to_apply', wp_kses_post( $values['job']['how_to_apply'] ) );
		update_post_meta( $job_id, 'how_to_apply', wp_kses_post( $values['job']['how_to_apply'] ) );
    }
}

// ======================
// 3. Show fields in backend job listing form
// ======================
add_filter( 'job_manager_job_listing_data_fields', 'custom_job_manager_admin_fields' );
function custom_job_manager_admin_fields( $fields ) {
    $fields['_apply_link'] = array(
        'label'       => __( 'Apply Link', 'job_manager' ),
        'type'        => 'text',
        'placeholder' => 'www.google.com',
        'description' => 'Add the link to your company’s website or job portal where candidates can apply directly.',
        'priority'    => 14,
    );

    $fields['_how_to_apply'] = array(
        'label'       => __( 'How to Apply Direction', 'job_manager' ),
        'type'        => 'wp-editor',
        'placeholder' => 'Write the description here',
        'description' => 'Provide clear instructions for applicants on how to apply for this job.',
        'priority'    => 13,
    );

    return $fields;
}



// ======================
// 4. Display fields on job listing (frontend)
// ======================
/*add_action( 'single_job_listing_meta_after', 'custom_job_manager_display_fields' );
function custom_job_manager_display_fields() {
    global $post;

    $apply_link   = get_post_meta( $post->ID, '_apply_link', true );
    $how_to_apply = get_post_meta( $post->ID, '_how_to_apply', true );

    if ( $apply_link ) {
        echo '<p><strong>Apply Link:</strong> <a href="' . esc_url( $apply_link ) . '" target="_blank">' . esc_html( $apply_link ) . '</a></p>';
    }

    if ( $how_to_apply ) {
        echo '<div><strong>How to Apply:</strong><br>' . wpautop( wp_kses_post( $how_to_apply ) ) . '</div>';
    }
}
*/

// 1. Add field to frontend Post a Job form
add_filter( 'submit_job_form_fields', function( $fields ) {
    $fields['job']['job_location_custom'] = array(
        'label'       => __( 'Work Location', 'job_manager' ),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'Enter work location', 
        'priority'    => 3,
		'description' => '(Enter your exact work location.)',
    );
    return $fields;
});
 
// 2. Save field value to post meta _job_location
add_action( 'job_manager_update_job_data', function( $job_id, $values ) {
    if ( ! empty( $values['job']['job_location_custom'] ) ) {
        update_post_meta( $job_id, '_job_location', sanitize_text_field( $values['job']['job_location_custom'] ) );
    }
}, 10, 2 );

// Update backend label for _job_location field
add_filter( 'job_manager_job_listing_data_fields', function( $fields ) {
    if ( isset( $fields['_job_location'] ) ) {
        $fields['_job_location']['label'] = __( 'Work Location', 'job_manager' );
    }
    return $fields;
});


// Populate saved locations array on edit post job form
add_filter( 'submit_job_form_fields_get_job_data', function( $fields, $job ) {
    if ( ! $job || ! is_object( $job ) || ! isset( $job->ID ) ) {
        return $fields;
    }

    if ( isset( $fields['job']['job_location'] ) ) {
        $terms = wp_get_object_terms( $job->ID, 'job_location_category', array( 'fields' => 'ids' ) );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $fields['job']['job_location']['value'] = $terms;
        }
    }

    return $fields;
}, 10, 2 );   

////

/**
 * Add "Upload Attachment" field (multi-file) to WP Job Manager
 */

// 1. Add field to frontend form
add_filter( 'submit_job_form_fields', function( $fields ) {
    $fields['job']['job_attachments'] = array(
        'label'       => __( 'Upload Attachment', 'job_manager' ),
        'type'        => 'file',
        'required'    => false,
        'priority'    => 20,
        'ajax'        => true,
        'multiple'    => true, // enable multiple uploads
        'allowed_mime_types' => array(
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        ),
    );
    return $fields;
});

// 2. Save uploaded files to post meta as JSON
add_action( 'job_manager_update_job_data', function( $job_id, $values ) {
    if ( ! empty( $values['job']['job_attachments'] ) ) {
        // Store as JSON for easy retrieval
        update_post_meta( $job_id, '_job_attachments', wp_json_encode( $values['job']['job_attachments'] ) );
    } else {
        delete_post_meta( $job_id, '_job_attachments' );
    }
}, 10, 2 );

// 3. Pre-populate when editing job from frontend
add_filter( 'submit_job_form_fields_get_job_data', function( $fields, $job ) {
    $saved = get_post_meta( $job->ID, '_job_attachments', true );
    if ( $saved ) {
        $attachments = json_decode( $saved, true );
        if ( ! empty( $attachments ) ) {
            $fields['job']['job_attachments']['value'] = $attachments;
        }
    }
    return $fields;
}, 10, 2 );

// 4. Show in backend Job Listing editor
add_filter( 'job_manager_job_listing_data_fields', function( $fields ) {
    $saved = get_post_meta( get_the_ID(), '_job_attachments', true );
    $files = array();

    if ( ! empty( $saved ) ) {
        if ( is_array( $saved ) ) {
            // Already an array — use it directly
            $files = $saved;
        } elseif ( is_string( $saved ) ) {
            // Decode JSON string
            $decoded = json_decode( $saved, true );
            if ( is_array( $decoded ) ) {
                $files = $decoded;
            }
        }
    }

    $fields['_job_attachments'] = array(
        'label'       => __( 'Upload Attachment', 'job_manager' ),
        'type'        => 'file',
        'multiple'    => true,
        'ajax'        => true,
        'value'       => $files,
        'allowed_mime_types' => array(
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        ),
        'description' => __( 'Upload one or more attachments (PDF, DOC, DOCX).', 'job_manager' ),
        'priority'    => 20,
    );

    return $fields;
});


/**
 * Include Employer users in Author dropdown for Job Listings.
 */
add_filter( 'wp_dropdown_users', 'include_employers_in_author_dropdown', 10, 2 );
function include_employers_in_author_dropdown( $output, $r = array() ) {
    global $post;

    // Only modify for 'job_listing' post type in admin edit/add screens
    if ( isset( $post->post_type ) && 'job_listing' === $post->post_type && is_admin() ) {

        // Get all users with role 'employer'
        $employers = get_users( array(
            'role'    => 'employer',
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ) );

        if ( ! empty( $employers ) ) {
            // Ensure $r has expected keys to prevent warnings
            $defaults = array(
                'name'     => 'post_author_override',
                'id'       => 'post_author_override',
                'class'    => '',
                'selected' => isset( $post->post_author ) ? $post->post_author : 0,
            );
            $r = wp_parse_args( $r, $defaults );

            $output = '<select name="' . esc_attr( $r['name'] ) . '" id="' . esc_attr( $r['id'] ) . '" class="' . esc_attr( $r['class'] ) . '">';

            // Get default authors (admins/editors)
            $existing_users = get_users( array(
                'who'     => 'authors',
                'orderby' => 'display_name',
                'order'   => 'ASC',
            ) );

            // Merge and remove duplicates
            $combined_users = array_merge( $existing_users, $employers );
            $unique_users   = array();
            foreach ( $combined_users as $user ) {
                $unique_users[ $user->ID ] = $user;
            }

            foreach ( $unique_users as $user ) {
                $output .= sprintf(
                    '<option value="%d"%s>%s (%s)</option>',
                    esc_attr( $user->ID ),
                    selected( $user->ID, $r['selected'], false ),
                    esc_html( $user->display_name ),
                    esc_html( $user->roles[0] )
                );
            }
            $output .= '</select>';
        }
    }
    return $output;
}




// 5. Optional: Display attachments on single job listing
/*
add_action( 'single_job_listing_meta_after', function() {
    global $post;
    $saved = get_post_meta( $post->ID, '_job_attachments', true );
    $files = ! empty( $saved ) ? json_decode( $saved, true ) : array();

    if ( ! empty( $files ) ) {
        echo '<div class="job-attachments"><strong>Attachments:</strong><ul>';
        foreach ( $files as $file_url ) {
            echo '<li><a href="' . esc_url( $file_url ) . '" target="_blank">' . basename( $file_url ) . '</a></li>';
        }
        echo '</ul></div>';
    }
});
*/

// Add "Company Profile" field to the job submission form
add_filter( 'submit_job_form_fields', function( $fields ) {
    global $wpdb;

     // Get current user
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $user_email   = $current_user->user_email;
		
		//echo $company_name;
		$company_name = $wpdb->get_var( $wpdb->prepare("
			SELECT name.meta_value
			FROM {$wpdb->prefix}frmt_form_entry AS e
			INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS email
				ON e.entry_id = email.entry_id
			INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS name
				ON e.entry_id = name.entry_id
			WHERE email.meta_key = %s
			  AND email.meta_value = %s
			  AND name.meta_key = %s
			ORDER BY e.date_created DESC
			LIMIT 1
		", 'email-1', $user_email, 'name-1' ) );
		
    } else {
        $company_name = '';
    }

    // Add field to job form
    $fields['job']['company_profile'] = array(
        'label'    => __( 'Company Profile', 'job_manager' ),
        'type'     => 'text',
        'required' => false,
        'priority' => 26,
		'value'  => $company_name,  
        //'default'  => $company_name, // Pre-fill with company name
    );


    return $fields;
});

// Save "Company Profile" selection
add_action( 'job_manager_update_job_data', function( $job_id, $values ) {
    if ( isset( $values['job']['company_profile'] ) ) {
        update_post_meta( $job_id, '_company_profile', sanitize_text_field( $values['job']['company_profile'] ) );
		update_post_meta( $job_id, '_company_name', sanitize_text_field( $values['job']['company_profile'] ) );		
    }
}, 10, 2);


// Show "Company Profile" in Job Listing editor
add_filter( 'job_manager_job_listing_data_fields', function( $fields ) {
    global $wpdb;

     // Get current user
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $user_email   = $current_user->user_email;

        // Fetch company name (name-1) where email-1 matches current user email
        $meta_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT *
             FROM {$wpdb->prefix}frmt_form_entry_meta AS email
             INNER JOIN {$wpdb->prefix}frmt_form_entry_meta AS name
                ON email.entry_id = name.entry_id
             WHERE email.meta_key = %s
               AND email.meta_value = %s
               AND name.meta_key = %s",
            'email-1',
            $user_email,
            'name-1'
        ));
		
		$company_name = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value 
			 FROM {$wpdb->prefix}frmt_form_entry_meta 
			 WHERE meta_id = %d",
			$meta_id
		));
		
    } else {
        $company_name = '';
    }

    $fields['_company_profile'] = array(
        'label'    => __( 'Company Profile', 'job_manager' ),
        'type'     => 'text',
        'value'    => $company_name,
        'priority' => 26,
    );

    return $fields;
});


// Company extra fields
add_filter( 'submit_job_form_fields', function( $fields ) {
    
    // Other hidden fields
    $fields['job']['_company_website'] = [
        'label'       => __( 'Company Website', 'job_manager' ),
        'type'        => 'text',
        'priority'    => 27,
        'value'       => '',
		'required'    => false,
        'class'       => 'company-extra',
    ];
    $fields['job']['_company_location'] = [
        'label'       => __( 'Company Location', 'job_manager' ),
        'type'        => 'text',
        'priority'    => 28,
        'value'       => '',
		'required'    => false,
        'class'       => 'company-extra',
    ];
    $fields['job']['_company_contact'] = [
        'label'       => __( 'Company Contact', 'job_manager' ),
        'type'        => 'text',
        'priority'    => 29,
        'value'       => '',
		'required'    => false,
        'class'       => 'company-extra',
    ];
    $fields['job']['_company_email'] = [
        'label'       => __( 'Company Email', 'job_manager' ),
        'type'        => 'text',
        'priority'    => 30,
        'value'       => '',
		'required'    => false,
        'class'       => 'company-extra',
    ];
	$fields['job']['_company_description'] = [
        'label'       => __( 'Company Description', 'job_manager' ),
        'type'        => 'textarea',
        'priority'    => 31,
		'required'    => false,
        'class'       => 'company-extra',
    ];
	
    // Pass company data to JS
    wp_enqueue_script( 'company-profile-js', get_stylesheet_directory_uri() . '/js/company-profile.js', [ 'jquery' ], '1.0', true );
    wp_localize_script( 'company-profile-js', 'companyAjax', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'company_profile_nonce' ),
    ] );

    return $fields;
});


// Get company data on post a job form
function get_company_data() {
    check_ajax_referer( 'company_profile_nonce', 'security' );

    global $wpdb;
	$full_address = '';
	
    $company_name = sanitize_text_field( $_POST['company_name'] );

    // First get the entry_id where meta_key = 'name-1' and meta_value = selected company
    $entry_id = $wpdb->get_var( $wpdb->prepare("
        SELECT entry_id 
        FROM {$wpdb->prefix}frmt_form_entry_meta
        WHERE meta_key = 'name-1'
          AND meta_value = %s
        LIMIT 1
    ", $company_name ) );


    if ( ! $entry_id ) {
        wp_send_json_error( [ 'message' => 'Company not found.' ] );
    }

    // Now fetch details for this entry_id
    $results = $wpdb->get_results( $wpdb->prepare("
        SELECT meta_key, meta_value
        FROM {$wpdb->prefix}frmt_form_entry_meta
        WHERE entry_id = %d
          AND meta_key IN ('url-1','address-1','phone-1','email-1','textarea-1')
    ", $entry_id ), OBJECT_K );
	
	$raw_address = $results['address-1']->meta_value;
	$address = maybe_unserialize( $raw_address );
	
	$full_address .= ''.$address["street_address"].', '.$address["city"].', '.$address["state"].', '.$address["zip"].', '.$address["country"].'';
	
    $response = [
        'website'  => isset($results['url-1']) ? $results['url-1']->meta_value : '',
        'address' => isset($results['address-1']) ? $full_address : '',
        'contact'  => isset($results['phone-1']) ? $results['phone-1']->meta_value : '',
        'email'    => isset($results['email-1']) ? $results['email-1']->meta_value : '',
		'description'    => isset($results['textarea-1']) ? $results['textarea-1']->meta_value : '',
    ];
	
	//print_r($response);die;

    wp_send_json_success( $response );
}
add_action( 'wp_ajax_get_company_data', 'get_company_data' );
add_action( 'wp_ajax_nopriv_get_company_data', 'get_company_data' );

  

// 1. Add to frontend Post a Job form
add_filter( 'submit_job_form_fields', function( $fields ) {
    $fields['job']['show_company_info'] = [
        'label'       => __( 'Do you want to display your company details on the job post?', 'job_manager' ),
        'type'        => 'select',
        'required'    => true,
        'priority'    => 25,
		'description' => '(If yes, following details be added to the job post)',
        'options'     => [
            ''  => __( '-Select-', 'job_manager' ),
            'yes' => __( 'Yes', 'job_manager' ),
            'no'  => __( 'No', 'job_manager' ),
        ],
    ];
    return $fields;
});

// 2. Save submitted value to post meta
add_action( 'job_manager_update_job_data', function( $job_id, $values ) {
    if ( isset( $values['job']['show_company_info'] ) ) {
        update_post_meta( $job_id, '_show_company_info', sanitize_text_field( $values['job']['show_company_info'] ) );
    }
}, 10, 2 );

// 3. Add to backend Job Listing form
add_filter( 'job_manager_job_listing_data_fields', function( $fields ) {
    $saved = get_post_meta( get_the_ID(), '_show_company_info', true );

    $fields['_show_company_info'] = [
        'label'       => __( 'Do you want to show these company information on dashboard/job detail page?', 'job_manager' ),
        'type'        => 'select',
		'required'    => false,
        'options'     => [
            '' => __( '-Select-', 'job_manager' ),
			'yes' => __( 'Yes', 'job_manager' ),
            'no'  => __( 'No', 'job_manager' ),
        ],
        'value'       => $saved,
        'priority'    => 25,
    ];

    return $fields;
});


/**
* remove unwanted fields form back-end
*/
add_filter( 'job_manager_job_listing_data_fields', function( $fields ) {
    // Unset the fields you don't want in backend editor
    unset( $fields['_company_website'] );
	unset( $fields['application'] );
    unset( $fields['_company_twitter'] );
    unset( $fields['_apply_link'] );
    unset( $fields['_company_tagline'] );
    unset( $fields['_company_video'] );
	unset( $fields['_job_salary_unit'] );

    return $fields;
}, 20 );


// validations

add_action( 'wp_footer', 'custom_job_form_pre_submit_validation', 9999 );
function custom_job_form_pre_submit_validation() {
    if ( is_page( 'post-a-job' ) ) : // Change if your slug differs
    ?>
	
    <script>  
		function initAutocomplete() {
			const input = document.getElementById("job_location_custom");
			if (!input) return;

			const autocomplete = new google.maps.places.Autocomplete(input, {
				componentRestrictions: { country: "au" }, // only for Australia
				fields: ["address_components", "geometry", "formatted_address"],
				types: ["address"],
			});

			autocomplete.addListener("place_changed", () => {
				const place = autocomplete.getPlace();
				if (!place.address_components) return;

				const components = {};
				place.address_components.forEach(c => {
					const type = c.types[0];
					components[type] = c.long_name;
				});

				// Fill individual fields
				/*document.getElementById("street").value = 
					(components.street_number ? components.street_number + " " : "") + (components.route || "");
				document.getElementById("city").value = components.locality || "";
				document.getElementById("state").value = components.administrative_area_level_1 || "";
				document.getElementById("postcode").value = components.postal_code || "";

				// Get Latitude & Longitude
				if (place.geometry && place.geometry.location) {
					document.getElementById("latitude").value = place.geometry.location.lat();
					document.getElementById("longitude").value = place.geometry.location.lng();
					console.log("Lat:", place.geometry.location.lat(), "Lng:", place.geometry.location.lng());
				}*/     
			});
		}

		// Ensure callback initializes autocomplete
		if (typeof google !== "undefined" && google.maps && google.maps.places) {
			initAutocomplete();
		} else {
			window.initAutocomplete = initAutocomplete;
		}
		
		
    document.addEventListener('DOMContentLoaded', function() {
        const submitBtn = document.querySelector('input[name="submit_job"]');
        const form = document.querySelector('form.job-manager-form');

        if (!submitBtn || !form) return;

        submitBtn.addEventListener('click', function(e) {
            // Remove previous errors
            document.querySelectorAll('.custom-url-error').forEach(el => el.remove());
            document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

            let valid = true;

            // Validation helper
            function validateUrlField(name, label, required = false) {
				const input = form.querySelector(`input[name="${name}"]`);
				if (!input) return true;

				const val = input.value.trim();
				// Updated regex: allows optional http/https and optional www
				const pattern = /^(https?:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/[\w\-._~:/?#[\]@!$&'()*+,;=]*)?$/i;

				if (required || val !== '') {
					if (!pattern.test(val)) {
						showError(input, `Please enter a valid URL (e.g. https://example.com or example.com)`);
						valid = false;
					}
				}
			}
            function showError(input, message) {
                input.classList.add('error');
				input.focus();
                const error = document.createElement('div');
                error.className = 'custom-url-error';
                error.textContent = message;
                input.insertAdjacentElement('afterend', error);
            }

            // Validate both fields
            //validateUrlField('application', 'Application URL', true);
            validateUrlField('apply_link', 'Apply Link', false);
			
            if (!valid) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });
    });
    </script>

    <style>
        input.error {
            border-color: red !important;
        }
        .custom-url-error {
            color: red;
            font-size: 13px;
            margin-top: 4px;
        }
    </style>
    <?php
    endif;
}

add_action( 'wp_footer', 'custom_validate_job_salary_field_js' );
function custom_validate_job_salary_field_js() {
    if ( is_page( 'post-a-job' ) ) : // Change slug if needed
    ?>
   <script>
   
   (function($){
        // select2 init if available
        $(document).ready(function(){
			
			const $salaryField = $('input[name="job_salary"]');

			if (!$salaryField.length) return;

			// Helper to show error
			function showSalaryError($input, message) {
				removeSalaryError($input); // remove previous errors
				$input.addClass('salary-error-field');
				$input.focus();
				const $errorMsg = $('<div class="custom-salary-error"></div>')
					.text(message)
					.css({
						color: 'red',
						'margin-top': '5px'
					});
				$input.after($errorMsg);
			}

			// Helper to remove error
			function removeSalaryError($input) {
				$input.removeClass('salary-error-field');
				$input.next('.custom-salary-error').remove();
			}

			// Validation logic
			function validateSalary($input) {
				const salaryValue = $.trim($input.val());
				let salaryValid = true;

				// Patterns
				const validPattern = /^[0-9]+(?:[ ]?(?:-|\/|per|to)[ ]?[0-9a-zA-Z]*)?$/i;
				const unsafePattern = /[<>{}\[\];'"`~!@#$%^&*_=+\\|:?]/;
				const consecutiveInvalid = /( {2,}|--|\/\/|\.\.)/;

				// Skip empty (optional)
				if (salaryValue !== '') {
					if (
						!validPattern.test(salaryValue) ||
						unsafePattern.test(salaryValue) ||
						consecutiveInvalid.test(salaryValue)
					) {
						salaryValid = false;
						showSalaryError(
							$input,
							'Invalid salary format. Allowed examples: "1000 - 2000", "1000/2000", "1000 per year".'
						);
					} else {
						removeSalaryError($input);
					}
				} else {
					removeSalaryError($input);
				}

				return salaryValid;
			}

			// Validate on input and blur
			$salaryField.on('input blur', function() {
				validateSalary($(this));
			});

			// Also validate on form submit
			$('form.job-manager-form').on('submit', function(e) {
				const $input = $salaryField;
				const isValid = validateSalary($input);

				if (!isValid) {
					e.preventDefault();
					e.stopImmediatePropagation();
					return false;
				}
			});
		});
	});
	
	document.addEventListener('DOMContentLoaded', function() {
		const submitBtn_salary = document.querySelector('input[name="submit_job"]');
		const form_salary = document.querySelector('form.job-manager-form');

		if (!submitBtn_salary || !form_salary) return;

		submitBtn_salary.addEventListener('click', function(e) {
			// Remove previous errors
			document.querySelectorAll('.custom-salary-error').forEach(el => el.remove());
			document.querySelectorAll('.salary-error-field').forEach(el => el.classList.remove('salary-error-field'));

			let salaryValid = true;

			const salaryField = form_salary.querySelector('input[name="job_salary"]');
			if (salaryField) {
				const salaryValue = salaryField.value.trim();

				if (salaryValue !== '') {
					// Main validation pattern
					const validPattern = /^[0-9]+(?:[ ]?(?:-|\/|per|to)[ ]?[0-9a-zA-Z]*)?$/i;

					// Reject special characters or scripts
					const unsafePattern = /[<>{}\[\];'"`~!@#$%^&*_=+\\|:?]/;

					// Reject multiple spaces, dashes, or dots
					const consecutiveInvalid = /( {2,}|--|\/\/|\.\.)/;

					if (
						!validPattern.test(salaryValue) ||
						unsafePattern.test(salaryValue) ||
						consecutiveInvalid.test(salaryValue)
					) {
						salaryValid = false;
						showSalaryError(
							salaryField,
							'Invalid salary format. Allowed examples: "1000 - 2000", "1000/2000", "1000 per year".'
						);
					}
				}
			}

			if (!salaryValid) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}

			// Helper to show error
			function showSalaryError(input, message) {
				input.classList.add('salary-error-field');
				input.focus();
				const errorMsg = document.createElement('div');
				errorMsg.className = 'custom-salary-error';
				errorMsg.textContent = message;
				errorMsg.style.color = 'red';
				errorMsg.style.marginTop = '5px';
				input.insertAdjacentElement('afterend', errorMsg);
			}
		});
	});   
	</script>


    <style>
        input.salary-error-field {
            border-color: red !important;
        }
        .custom-salary-error {
            color: red;
            font-size: 13px;
            margin-top: 4px;
        }
    </style>
    <?php
    endif;
}
  
 
// Job Expiry Date
add_action( 'wp_footer', 'custom_disable_job_expiry_input' );
function custom_disable_job_expiry_input() {
    if ( is_page( 'post-a-job' ) ) { // Change page slug if needed
	
		// Get current logged-in user ID
		$current_user_id = get_current_user_id();
		$packages = wc_paid_listings_get_user_packages( $current_user_id );

		if ( empty( $packages ) ) {
			$expiry_date_formatted = '';
			$is_expired = false;
		} else {
			$i = 0;
			foreach ( $packages as $package ) {
				// Get the WooCommerce order related to this package
				$order = wc_get_order( $package->order_id );

				if ( $order && ! empty( $package->package_duration ) && $package->package_duration > 0 ) {
					$order_date = $order->get_date_created(); // WC_DateTime object

					// Expiry date
					$expiry_date = clone $order_date;
					$expiry_date->modify( '+' . $package->package_duration . ' days' );
					$expiry_date_formatted = $expiry_date->format('M d, Y');
					
				} else {
					$expiry_date_formatted = '';
				}
				$i++;
			}
		} 
		?>
		<script>    
		
		//var maxDate = new Date('<?php echo esc_js( $expiry_date_formatted ); ?>');
		var maxDate2 = new Date('<?php echo esc_js( date('Y-m-d', strtotime($expiry_date_formatted)) ); ?>');
		//console.log(maxDate);
		console.log(maxDate2);
		
		jQuery(function($) {
		  $("#_job_expires").datepicker({
			dateFormat: "yy-mm-dd",  
			minDate: 0,           
			maxDate: maxDate2 ,
			onSelect: function(dateText, inst){
			  // dateText will be in yy-mm-dd as per dateFormat
			  $("#job_expiry_date").val(dateText);
			  console.log('dateText: '+dateText);
			}
		  }); 
		});  
		
		
		document.addEventListener('DOMContentLoaded', function() {
			// Replace 'job_expiry_date' with your actual input name or ID
			const expiryInput = document.querySelector('input[name="_job_expires"]');

			if (expiryInput) {
				// Disable typing
				expiryInput.addEventListener('keydown', function(e) {
					e.preventDefault();
				});

				// Disable pasting
				expiryInput.addEventListener('paste', function(e) {
					e.preventDefault();
				});

				// Optional: prevent drag/drop
				expiryInput.addEventListener('drop', function(e) {
					e.preventDefault();
				});

				// Optional: make readonly (still allows date picker)
				//expiryInput.setAttribute('readonly', 'readonly');  
			}
		}); 
		</script>
    <?php
	}
}

// update salary unit field

add_filter( 'submit_job_form_fields', 'custom_replace_salary_currency_field' );
function custom_replace_salary_currency_field( $fields ) {

    // Check if the salary currency field exists
    if ( isset( $fields['job']['job_salary_currency'] ) ) {

        // Replace with a select dropdown
        $fields['job']['job_salary_currency'] = array(
            'label'       => __( 'Salary Currency', 'wp-job-manager' ),
            'type'        => 'select',
            'required'    => false,
            'options'     => array(
                ''       => __( '-Select Currency-', 'wp-job-manager' ),
                'USD'    => 'USD - US Dollar',
                'EUR'    => 'EUR - Euro',
                'GBP'    => 'GBP - British Pound',
                'AUD'    => 'AUD - Australian Dollar',
                'CAD'    => 'CAD - Canadian Dollar',
                // Add more currencies as needed
            ),
            'priority'    => 8, // Adjust position if needed
			'default'     => 'AUD', // Set AUD as the default selected
        );
    }

    return $fields;
}  


/*******************************************/
/**
 * JS handler for Renewal button
 */
add_action( 'wp_footer', 'custom_renew_package_button_script' );
function custom_renew_package_button_script() {
    if ( ! is_user_logged_in() ) return;
    ?>
    <script>
	jQuery(document).ready(function($) {

		// Close success popup
		$(document).on('click', '#close_popup', function() {
			$('.elementor-popup-modal').removeClass('elementor-active').hide();
			$('body').removeClass('elementor-popup-modal-open');
			location.reload();
		});

		let activePackageId = null;
		let $activeBtn = null;

		// Open confirmation popup on Renew button click
		$(document).on('click', '.renew-package-btn', function(e) {
			e.preventDefault();

			const $btn = $(this);
			const packageId = $btn.data('package-id');
			const btnId = $btn.attr('id');
			const limit_remain = $('#limit_remains').text().trim();
			console.log(limit_remain);
			
			if (btnId === 'invalid') return false;

			activePackageId = packageId;
			$activeBtn = $btn;

			// Open Elementor Popup safely
			if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
				elementorProFrontend.modules.popup.showPopup({ id: 3605 });
			}
			$('#renew_popups span#renew_job_remainings').text(limit_remain);
		});

		// "No" click (close popup)
		$(document).on('click', '#renew_no', function(e) {
			e.preventDefault();
			if ($activeBtn) $activeBtn.prop('disabled', false).text('Renew Package');
			$('.elementor-popup-modal').removeClass('elementor-active').hide();
			$('body').removeClass('elementor-popup-modal-open');
		});

		// "Yes" click (only one handler globally)
		$(document).on('click', '#renew_yes', function(e) {
			e.preventDefault();
			if (!activePackageId || !$activeBtn) return;

			const $yesBtn = $(this);
			$yesBtn.prop('disabled', true).text('Renewing...');
			$activeBtn.prop('disabled', true).text('Renewing...');

			$('#loader').show();
			$('.elementor-popup-modal').removeClass('elementor-active').hide();
			$('body').removeClass('elementor-popup-modal-open');

			$.ajax({
				url: '<?php echo admin_url("admin-ajax.php"); ?>',
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'renew_wc_paid_listing_package',
					package_id: activePackageId
				},
				success: function(response) {
					console.log(response);
					$('#loader').fadeOut('slow');
					$('body').css('overflow', 'unset');

					if (response.success) {
						if (typeof elementorProFrontend !== "undefined" && elementorProFrontend.modules.popup) {
							elementorProFrontend.modules.popup.showPopup({ id: 3618 });
						}
					} else {
						$activeBtn.prop('disabled', false).text('Renew Package');
						$yesBtn.prop('disabled', false).text('Yes');
					}
				},
				error: function() {
					alert('Something went wrong. Please try again.');
					$activeBtn.prop('disabled', false).text('Renew Package');
					$yesBtn.prop('disabled', false).text('Yes');
					$('#loader').fadeOut('slow');
					$('body').css('overflow', 'unset');
				}
			});
		});
	});
	</script>
    <?php
}


add_action( 'wp_ajax_renew_wc_paid_listing_package', 'renew_wc_paid_listing_package_callback' );
add_action( 'wp_ajax_nopriv_renew_wc_paid_listing_package', 'renew_wc_paid_listing_package_callback' );
function renew_wc_paid_listing_package_callback() {
    // Helpful for debugging — remove or comment out on production.
    if ( defined('WP_DEBUG') && WP_DEBUG ) {
        @error_reporting( E_ALL );
        @ini_set( 'display_errors', 1 );
    }

    // Security: ensure user is logged in
    if ( ! is_user_logged_in() ) {
        wp_send_json( [ 'success' => false, 'message' => 'You must be logged in.' ] );
    }

    $user_id    = get_current_user_id();
    $package_id = isset( $_POST['package_id'] ) ? absint( $_POST['package_id'] ) : 0;

    if ( ! $package_id ) {
        wp_send_json( [ 'success' => false, 'message' => 'Invalid package ID.' ] );
    }

    // get package object
    $package = wc_paid_listings_get_package( $package_id );

    if ( ! $package ) {
        wp_send_json( [ 'success' => false, 'message' => 'Package not found.' ] );
    }

    // Determine the product ID correctly depending on object type.
    // Many versions return an object that has get_product_id(); older or other classes may store ->product_id
    $product_id = $package_id;

    if ( ! $product_id ) {
        // log for debugging
        error_log( 'renew_wc_paid_listing_package: Could not determine product_id from package. package_id=' . $package_id );
        wp_send_json( [ 'success' => false, 'message' => 'No product linked to this package.' ] );
    }

    // Get Job Duration from product meta (fallback to 30 days)
    $job_duration = get_post_meta( $product_id, '_job_listing_duration', true );
    if ( empty( $job_duration ) ) {
        $job_duration = 30;
    }

    // Get saved Stripe tokens for the user
    // Note: the first parameter is user ID; the second is the gateway string - ensure the token type is correct for your setup
    $tokens = WC_Payment_Tokens::get_customer_tokens( $user_id, 'stripe' );

    if ( empty( $tokens ) || ! is_array( $tokens ) ) {
        wp_send_json( [ 'success' => false, 'message' => 'No saved payment method found. Please renew manually.' ] );
    }

    // Use the first token (or implement logic to choose default)
    $token = reset( $tokens );
    if ( ! is_object( $token ) || ! method_exists( $token, 'get_token' ) ) {
        error_log( 'renew_wc_paid_listing_package: invalid token object for user ' . $user_id );
        wp_send_json( [ 'success' => false, 'message' => 'Saved payment method invalid. Please re-add your card.' ] );
    }

    // Get the Stripe gateway instance
    $gateways = WC()->payment_gateways->payment_gateways();
	//print_r($gateways);
	$gateway  = isset($gateways['stripe']) ? $gateways['stripe'] : false;

    if ( ! $gateway ) {
        error_log( 'renew_wc_paid_listing_package: stripe gateway not found' );
        wp_send_json( [ 'success' => false, 'message' => 'Stripe gateway not available.' ] );
    }

    // Create a new order for the user 
    try {
        $order = wc_create_order( array( 'customer_id' => $user_id ) );
        if ( ! is_a( $order, 'WC_Order' ) ) {
            error_log( 'renew_wc_paid_listing_package: wc_create_order did not return WC_Order' );
            wp_send_json( [ 'success' => false, 'message' => 'Unable to create order.' ] );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json( [ 'success' => false, 'message' => 'Product not found.' ] );
        }


        // add product & totals
        $order->add_product( $product, 1 );
        $order->set_currency( get_woocommerce_currency() );
        $order->calculate_totals();

        // set payment method to the gateway ID string (not the object)
        if ( property_exists( $gateway, 'id' ) ) {
            $gateway_id = $gateway->id;
        } elseif ( isset( $gateway->id ) ) {
            $gateway_id = $gateway->id;
        } else {
            // fallback: known id
            $gateway_id = 'stripe';
        }

        $order->set_payment_method( $gateway_id );
        
		// Get user data
		$user_meta = get_user_meta( $user_id );

		// Billing details
		$order->set_billing_first_name( $user_meta['billing_first_name'][0] ?? '' );
		$order->set_billing_last_name( $user_meta['billing_last_name'][0] ?? '' );
		$order->set_billing_company( $user_meta['billing_company'][0] ?? '' );
		$order->set_billing_address_1( $user_meta['billing_address_1'][0] ?? '' );
		$order->set_billing_address_2( $user_meta['billing_address_2'][0] ?? '' );
		$order->set_billing_city( $user_meta['billing_city'][0] ?? '' );
		$order->set_billing_state( $user_meta['billing_state'][0] ?? '' );
		$order->set_billing_postcode( $user_meta['billing_postcode'][0] ?? '' );
		$order->set_billing_country( $user_meta['billing_country'][0] ?? '' );
		$order->set_billing_phone( $user_meta['billing_phone'][0] ?? '' );
		$order->set_billing_email( $user_meta['billing_email'][0] ?? '' );

		// Shipping details
		$order->set_shipping_first_name( $user_meta['shipping_first_name'][0] ?? '' );
		$order->set_shipping_last_name( $user_meta['shipping_last_name'][0] ?? '' );
		$order->set_shipping_company( $user_meta['shipping_company'][0] ?? '' );
		$order->set_shipping_address_1( $user_meta['shipping_address_1'][0] ?? '' );
		$order->set_shipping_address_2( $user_meta['shipping_address_2'][0] ?? '' );
		$order->set_shipping_city( $user_meta['shipping_city'][0] ?? '' );
		$order->set_shipping_state( $user_meta['shipping_state'][0] ?? '' );
		$order->set_shipping_postcode( $user_meta['shipping_postcode'][0] ?? '' );
		$order->set_shipping_country( $user_meta['shipping_country'][0] ?? '' );

		$order->save();

    } catch ( Exception $e ) {
        error_log( 'renew_wc_paid_listing_package: order creation error: ' . $e->getMessage() );
        wp_send_json( [ 'success' => false, 'message' => 'Failed to create order.' ] );
    }

    // PROCESS PAYMENT
    try {
		$token_id = $token->get_id();
		$token_value = $token->get_token();

		// Common token POST keys
		$_POST['payment_method'] = $gateway->id;
		$_POST['wc-' . $gateway->id . '-payment-token'] = $token_id;
		$_POST['payment_token'] = $token_value; // for alt Stripe plugins

		$result = $gateway->process_payment( $order->get_id() );

		if ( is_array( $result ) && isset( $result['result'] ) && $result['result'] === 'success' ) {
			$new_expiry = date('Y-m-d H:i:s', strtotime("+{$job_duration} days"));
			if ( method_exists($package, 'set_expiry') ) {
				$package->set_expiry($new_expiry);
				if ( method_exists($package, 'save') ) $package->save();
			} else {
				update_post_meta($package_id, '_package_expiry_date', $new_expiry);
			}
			
			//New order id
			$new_package_id = $order->get_id(); 
			
			// After payment success, create new package for user
			global $wpdb;
			$table = $wpdb->prefix . 'wcpl_user_packages';

			// 1. Check table exists before querying
			$table_exists = $wpdb->get_var( $wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$table
			) );
			
			// 2. Get user packages safely
			$packages2 = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC",
					$user_id
				)
			);
			
			// 3. Loop through and delete old ones
			foreach ( $packages2 as $packag ) {
				if ( intval( $packag->order_id ) === intval( $new_package_id ) ) {
					continue; // Skip the new one
				}

				// Delete the old package record
				$wpdb->delete(
					$table,
					array( 'order_id' => intval( $packag->order_id ) ),
					array( '%d' )
				);

				// Cancel related WooCommerce order if found
				if ( ! empty( $packag->order_id ) ) {
					$order = wc_get_order( intval( $packag->order_id ) );
					if ( $order ) {
						$order->update_status( 'cancelled', 'Old package cancelled after renewal.' );
					}
				} 
			}
			
			wp_send_json_success([
				'message' => sprintf('Package renewed successfully'),
				'order_id' => $new_package_id,
			]);
		} else {
			error_log( 'Stripe charge failed. Gateway returned: ' . print_r($result, true) );
			wp_send_json_error(['message' => 'Payment failed. See logs for details.']);
		}

	} catch ( Exception $e ) {
		error_log( 'Payment exception: ' . $e->getMessage() );
		wp_send_json_error(['message' => 'Payment error: ' . $e->getMessage()]);
	}

    wp_die();
}


// make published job
add_filter( 'job_manager_get_job_listing_post_status', function( $status, $job ) {
    return 'publish'; // auto-publish
}, 10, 2 ); 


// Saving lat/long into Database on post a job 
/*add_action( 'save_post_job_listing', function( $post_id, $post, $update ) {

    // Ensure this only runs when the post is published or pending
    if ( $post->post_type !== 'job_listing' ) {
        return;
    }

    // Avoid infinite loops
    remove_action( 'save_post_job_listing', __FUNCTION__, 20 );

    // Get city and taxonomy-based state
    $city = get_post_meta( $post_id, '_job_location', true );

    $categories = wp_get_post_terms( $post_id, 'job_location_category', [ 'fields' => 'names' ] );
    $state = ! empty( $categories ) && ! is_wp_error( $categories ) ? implode( ', ', $categories ) : '';

    if ( empty( $city ) && empty( $state ) ) {
        return;
    }

    // Prepare query for API
    $query = urlencode( trim( $city . ', ' . $state ) );
    $url   = "https://nominatim.openstreetmap.org/search?format=json&q={$query}";
	//$url   = "https://nominatim.openstreetmap.org/search?format=json&q={$query}";

    // Fetch from API
    $response = wp_remote_get( $url, [
        'headers' => [
            'User-Agent' => 'YourSiteName/1.0 (' . home_url() . ')'
        ],
        'timeout' => 20,
    ]);

    if ( is_wp_error( $response ) ) {
        return;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( ! empty( $data[0]['lat'] ) && ! empty( $data[0]['lon'] ) ) {
        update_post_meta( $post_id, '_job_latitude', sanitize_text_field( $data[0]['lat'] ) );
        update_post_meta( $post_id, '_job_longitude', sanitize_text_field( $data[0]['lon'] ) );
    }

    // Re-add action
    add_action( 'save_post_job_listing', __FUNCTION__, 20, 3 );

}, 20, 3 );
*/

add_filter( 'woocommerce_save_to_account_checked', '__return_true' );  

// Enqueue Select2 and initialize it on page load on woocommerce checkout
add_action('wp_footer', function() { 

	if(is_page( array( 2222 ) )){
	?>
		<script>
		document.addEventListener("DOMContentLoaded", function () {
				const interval = setInterval(function () {
				const job_location_custom = document.querySelector('input[name="job_location_custom"]');

				if (job_location_custom) { 
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

								// Allow letters, numbers, spaces, commas, dashes, and slashes
								const validPattern = /^[A-Za-z0-9\s,\/-]*$/;

								if (!validPattern.test(value)) {
									error = 'Only letters, numbers, spaces, commas, dashes, and slashes are allowed.';
									// Remove invalid characters, but keep valid ones
									value = value.replace(/[^A-Za-z0-9\s,\/-]/g, '');
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
					setupValidation(job_location_custom, 50); // work location  
				}
			}, 500);
		});
		</script>
		
		<script>
		document.addEventListener("DOMContentLoaded", function () {
			const interval = setInterval(function () {
				const job_title = document.querySelector('input[name="job_title"]');

				if (job_title) { 
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

								// Allow only alphabets and spaces, with a minimum length of 10 characters
								const validPattern = /^[A-Za-z\s]{10,}$/;

								if (!validPattern.test(value)) {
									error = 'Only letters and spaces are allowed, and minimum 10 characters required.';
									// Remove invalid characters (anything not a letter or space)
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
					setupValidation(job_title, 100); // work location  
				}
			}, 500);
		});
		</script>
		
	<?php 
	}
});
        

/**
* cancel package function
*/ 
function enqueue_cancel_package_script() {
    wp_enqueue_script( 'cancel-package-ajax', get_stylesheet_directory_uri() . '/js/cancel-package.js', ['jquery'], '1.0', true );
    wp_localize_script( 'cancel-package-ajax', 'cancelPackageAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cancel_package_nonce')
    ]);
}
add_action( 'wp_enqueue_scripts', 'enqueue_cancel_package_script' );


function ajax_cancel_user_package() {
    check_ajax_referer('cancel_package_nonce', 'nonce');

    if ( ! is_user_logged_in() ) {
        wp_send_json_error('You must be logged in.');
    }

    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
    if ( $package_id <= 0 ) {
        wp_send_json_error('Invalid package id.');
    }

    $current_user = get_current_user_id();
    global $wpdb;

    // table name (adjust if your prefix or plugin uses different)
    $table = $wpdb->prefix . 'wcpl_user_packages';

    // 1) Verify ownership in the plugin table (reliable)
    $row_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table WHERE product_id  = %d AND user_id = %d",
        $package_id,
        $current_user
    ) );

    if ( ! $row_id ) {
        wp_send_json_error( 'Invalid package or you do not own this package.' );
    }

    // 2) Optional: mark package as cancelled (post meta) for extra trace
    update_post_meta( $package_id, '_is_cancelled', 'yes' );

    // 3) Get order_id from table (if present)
    $order_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT order_id FROM $table WHERE product_id  = %d AND user_id = %d",
        $package_id,
        $current_user
    ) );

    // 4) Remove the package row from the table
    $deleted = $wpdb->delete(
        $table,
        [
            'id' => $row_id, // use the specific row primary key if available
        ],
        ['%d']
    );

    if ( $deleted === false ) {
        wp_send_json_error( 'Failed to remove package from table.' );
    }

    // 5) If order exists, verify order belongs to current user before deleting
    if ( $order_id ) {
        $order = wc_get_order( intval( $order_id ) );
        if ( $order ) {
            $order_user_id = (int) $order->get_user_id();
            if ( $order_user_id === $current_user ) {
                // Permanently delete the order - be cautious
                //wp_delete_post( intval($order_id), true );
				$order->update_status( 'cancelled', 'Order cancelled by user request.' ); 
            } else {
                // Do not delete someone else's order; optionally log or notify
                wp_send_json_error( 'Order does not belong to you. Order not deleted.' );
            }
        }
    }  

    wp_send_json_success( 'SUCCESS: Package has been cancelled and removed successfully.' );
}
add_action('wp_ajax_cancel_user_package', 'ajax_cancel_user_package');



add_filter( 'wc_paid_listings_user_package_is_valid', function( $valid, $package ) {
    if ( get_post_meta( $package->id, '_is_cancelled', true ) === 'yes' ) {
        return false;
    }
    return $valid;
}, 10, 2 );  


// delete old package and assigned new package
add_action( 'woocommerce_order_status_completed', 'wcpl_delete_old_package_and_order', 20, 1 );
function wcpl_delete_old_package_and_order( $order_id ) {
    global $wpdb;

    // Get the WooCommerce order
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $user_id = $order->get_user_id();
    if ( ! $user_id ) return;

    // Get product IDs from this order
    $new_product_ids = [];
    foreach ( $order->get_items() as $item ) {
        $new_product_ids[] = $item->get_product_id();
    }

    if ( empty( $new_product_ids ) ) return;

    // Fetch all user packages
    $packages = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = %d", $user_id)
    );

    if ( empty( $packages ) ) return;

    foreach ( $packages as $package ) {
		
        // Skip the package if it belongs to the newly purchased order
        if ( intval( $package->order_id ) === intval( $order_id ) ) {
            continue;
        }

        // Cancel old WooCommerce order (if exists)
        if ( ! empty( $package->order_id ) ) {
            $old_order = wc_get_order( intval( $package->order_id ) );
            if ( $old_order && $old_order->get_status() !== 'cancelled' ) {
                $old_order->update_status( 'cancelled', 'Auto-cancelled due to new package purchase.' );
            }
        }

        // Delete the user package record
        $wpdb->delete(
            "{$wpdb->prefix}wcpl_user_packages",
            [ 'id' => $package->id ],
            [ '%d' ]
        );
    }

    // Optional: Add a note to new order
    $order->add_order_note( 'Previous packages cancelled and removed automatically.' );
}


// Testing function only
//add_action('woocommerce_order_status_completed', 'wcpl_delete_old_package_and_orders', 20, 1);
function wcpl_delete_old_package_and_orders($order_id) {  
    global $wpdb;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    // Get all product IDs from this order
    $new_product_ids = [];
    foreach ($order->get_items() as $item) {
        $new_product_ids[] = $item->get_product_id();
    }

    if (empty($new_product_ids)) return;

    // Detect the category of the new package (first product assumed primary)
    $main_product_id = $new_product_ids[0];
    $product_terms = wp_get_post_terms($main_product_id, 'product_cat', ['fields' => 'slugs']);
    $is_rooms_for_hire = in_array('rooms_for_hire', $product_terms, true);

    // Get all user packages
    $packages = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = %d", $user_id)
    );

    if (empty($packages)) return;

    foreach ($packages as $package) {

        // Skip the package if it's from this order
        if (intval($package->order_id) === intval($order_id)) continue;

        // Fetch the product for this user package
        $old_product_id = intval($package->product_id);
        $old_terms = wp_get_post_terms($old_product_id, 'product_cat', ['fields' => 'slugs']);
        $old_is_rooms_for_hire = in_array('rooms_for_hire', $old_terms, true);

        // Skip deletion if category differs
        if ($is_rooms_for_hire !== $old_is_rooms_for_hire) {
            continue; // keep packages from other categories
        }

        // Cancel old WooCommerce order
        if (!empty($package->order_id)) {
            $old_order = wc_get_order(intval($package->order_id));
            if ($old_order && $old_order->get_status() !== 'cancelled') {
                $old_order->update_status('cancelled', 'Auto-cancelled due to new package purchase.');
            }
        }

        // Delete the package record from wp_wcpl_user_packages
       /* $wpdb->delete(
            "{$wpdb->prefix}wcpl_user_packages",
            ['id' => $package->id],
            ['%d']
        );*/
    }

    $order->add_order_note('Previous packages of same category cancelled and removed automatically.');
}


// add order action buttons 
add_filter( 'woocommerce_my_account_my_orders_actions', 'add_invoice_button_to_orders', 10, 2 );
function add_invoice_button_to_orders( $actions, $order ) {
    if ( $order->has_status( array( 'completed', 'processing' ) ) ) {
        $actions['invoice'] = array(
            'url'  => wp_nonce_url( add_query_arg( 'invoice', $order->get_id(), wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ) ), 'invoice' ),
            'name' => __( 'Invoice', 'woocommerce' ),
        );
    }
    return $actions;
}

// restrict or notice for user for you can't submit the job now 
add_action( 'job_manager_job_submission_form_start', 'wcpl_show_package_limit_notice' );
function wcpl_show_package_limit_notice() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) return;

    $packages = wc_paid_listings_get_user_packages( $user_id );

    $has_available_package = false;
    if ( ! empty( $packages ) ) {
        foreach ( $packages as $package ) {
            if ( $package->package_limit == 0 || $package->package_count < $package->package_limit ) {
                $has_available_package = true;
                break;
            }
        }
    }

    if ( ! $has_available_package ) {
        echo '<div class="job-manager-error">You have consumed all your job listings. Please <a href="' . esc_url( site_url() ) . '">purchase a new package</a> to continue posting jobs.</div>';
        remove_action( 'job_manager_job_submission_form', 'job_manager_job_submission_form' ); // prevent form rendering
    }
}
