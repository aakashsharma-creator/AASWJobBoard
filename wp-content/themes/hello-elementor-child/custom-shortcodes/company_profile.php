<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_company_profile()
{
  global $wpdb;

  // Check if user is logged in
  if (!is_user_logged_in()) {
    echo '<p>Please log in to edit your profile.</p>';
    return;
  }

  $user_id = get_current_user_id();
  $user_info = get_userdata($user_id);
  $email = $user_info->user_email;

  // Fetch existing data
  $entry = $wpdb->get_row($wpdb->prepare(
    "SELECT MAX(CASE WHEN m.meta_key = 'email-1' THEN m.meta_value END) AS email, 
            MAX(CASE WHEN m.meta_key = 'name-1' THEN m.meta_value END) AS company_name, 
            MAX(CASE WHEN m.meta_key = 'name-2' THEN m.meta_value END) AS first_name, 
            MAX(CASE WHEN m.meta_key = 'name-3' THEN m.meta_value END) AS last_name, 
            MAX(CASE WHEN m.meta_key = 'url-1' THEN m.meta_value END) AS website_url, 
            MAX(CASE WHEN m.meta_key = 'address-1' THEN m.meta_value END) AS address,
            MAX(CASE WHEN m.meta_key = 'phone-1' THEN m.meta_value END) AS phone, 
            MAX(CASE WHEN m.meta_key = 'phone-2' THEN m.meta_value END) AS office, 
            MAX(CASE WHEN m.meta_key = 'textarea-1' THEN m.meta_value END) AS about_company, 
            MAX(CASE WHEN m.meta_key = 'checkbox-1' THEN m.meta_value END) AS prefered_contact, 
            MAX(CASE WHEN m.meta_key = 'select-1' THEN m.meta_value END) AS sector,
            MAX(CASE WHEN m.meta_key = 'select-2' THEN m.meta_value END) AS types,
            MAX(CASE WHEN m.meta_key = 'select-3' THEN m.meta_value END) AS country,
            MAX(CASE WHEN m.meta_key = 'select-4' THEN m.meta_value END) AS state,
            MAX(CASE WHEN m.meta_key = 'upload-1' THEN m.meta_value END) AS company_logo
    FROM {$wpdb->prefix}frmt_form_entry_meta m 
    WHERE m.entry_id = (SELECT entry_id FROM {$wpdb->prefix}frmt_form_entry_meta WHERE meta_key = 'email-1' AND meta_value = %s ORDER BY entry_id DESC LIMIT 1)",
    $email
  ), OBJECT);

  if (!$entry) {
    echo '<p>No company information found for this account.</p>';
    return;
  }

  // Prepare field values safely
  $company_name = esc_html($entry->company_name ?? '');
  $first_name = esc_html($entry->first_name ?? '');
  $last_name = esc_html($entry->last_name ?? '');
  $website_url = esc_url($entry->website_url ?? '');
  $phone = esc_html($entry->phone ?? '');
  $office = esc_html($entry->office ?? '');
  $about_company = wp_kses_post($entry->about_company ?? '');
  $sector = esc_html($entry->sector ?? '');
  $types = esc_html($entry->types ?? '');
  $address_raw = $entry->address ?? '';
  $street = $city = $zip = '';
  $state = esc_html($entry->state ?? '');
  $country = esc_html($entry->country ?? '');
  $company_logo = '';
  if (!empty($entry->company_logo)) {
    $logo_data = maybe_unserialize($entry->company_logo);
    if (!empty($logo_data['file']['file_url'])) {
      $company_logo = esc_url($logo_data['file']['file_url']);
    }
  }

  if (!empty($address_raw)) {
    $address_data = maybe_unserialize($address_raw);
    if (is_array($address_data)) {
      $street = esc_html($address_data['street_address'] ?? '');
      $city = esc_html($address_data['city'] ?? '');
      $zip = esc_html($address_data['zip'] ?? '');
    }
  }

  $preferred_contact = [];
  if (!empty($entry->prefered_contact)) {
    $preferred_contact = array_map('trim', explode(',', $entry->prefered_contact));
  }

  // ---- ENQUEUE CROPPER ------
  wp_enqueue_style('cropper-css', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css', [], '1.6.1');
  wp_enqueue_script('cropper-js', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js', [], '1.6.1', true);

?>
  <link rel="stylesheet" type="text/css"
    href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/company_profile.css">

  <div class="container">
    <!-- Card -->
    <div class="card">
      <div id="form-messages"></div>
      <form method="post" enctype="multipart/form-data" id="company-profile-form">
        <?php wp_nonce_field('company_profile_update', '_wpnonce'); ?>
        <input type="hidden" name="action" value="update_company_profile">
        <input type="hidden" name="update_company_profile" value="1">

        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?php echo esc_attr($email); ?>" placeholder="your.email@example.com"
            readonly class="input-field">
          <span class="field-error"></span>
        </div>

        <div class="form-group">
          <label>Organization Name *</label>
          <input type="text" name="company_name" value="<?php echo esc_attr($company_name); ?>"
            placeholder="Enter your company name" required pattern="[A-Za-z\s]+"
            title="Only letters and spaces are allowed" class="input-field">
          <span class="field-error"></span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" placeholder="First name"
              required pattern="[A-Za-z]+" title="Only letters are allowed" class="input-field">
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" placeholder="Last name"
              required pattern="[A-Za-z]+" title="Only letters are allowed" class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Mobile Phone *</label>
            <input type="text" name="mobile_phone" value="<?php echo esc_attr($phone); ?>" placeholder="+61 400 000 000"
              required pattern="[0-9\s\-\(\)\+]+"
              title="Only numbers, spaces, dashes, parentheses, or plus signs are allowed" class="input-field">
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Other Phone</label>
            <input type="text" name="office_number" value="<?php echo esc_attr($office); ?>"
              placeholder="+61 (02) 0000 0000" pattern="[0-9\s\-\(\)\+]+"
              title="Only numbers, spaces, dashes, parentheses, or plus signs are allowed" class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <div class="form-group">
          <label>About Company</label>
          <?php
          $editor_settings = [
            'textarea_name' => 'about_company',
            'editor_height' => 200,
            'media_buttons' => false,
            'teeny' => true,
            'textarea_rows' => 10,
            'quicktags' => ['buttons' => 'strong,em,link,ul,ol,li,code'],
            'tinymce' => [
              'toolbar1' => 'bold,italic,underline,link,undo,redo',
              'toolbar2' => '',
            ],
          ];
          wp_editor($about_company, 'about_company', $editor_settings);
          ?>
          <span class="field-error"></span>
        </div>

        <div class="form-group">
          <label>Street Address *</label>
          <input type="text" name="street_address" value="<?php echo esc_attr($street); ?>" placeholder="123 Main Street"
            required class="input-field">
          <span class="field-error"></span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Suburb *</label>
            <input type="text" name="suburb" value="<?php echo esc_attr($city); ?>" placeholder="Suburb" required
              class="input-field">
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>State *</label>
            <select name="state" required class="input-field">
              <!-- <option value="">- Select State -</option> -->
              <?php
              $terms = get_terms([
                'taxonomy' => 'job_location_category',
                'hide_empty' => false,
              ]);

              if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                  echo '<option value="' . esc_attr($term->name) . '" ' . selected($state, $term->name, false) . '>' . esc_html($term->name) . '</option>';
                }
              }
              ?>
            </select>
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Postcode *</label>
            <input type="text" name="postcode" value="<?php echo esc_attr($zip); ?>" placeholder="0000" required
              class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Sector *</label>
            <select name="select-1" required class="input-field">
              <!-- <option value="">- Select -</option> -->
              <option value="Federal Government" <?php selected($sector, 'Federal Government'); ?>>Federal Government
              </option>
              <option value="Government" <?php selected($sector, 'Government'); ?>>Government</option>
              <option value="Non-Government" <?php selected($sector, 'Non-Government'); ?>>Non-Government</option>
              <option value="Private-Practice" <?php selected($sector, 'Private-Practice'); ?>>Private Practice</option>
              <option value="Community" <?php selected($sector, 'Community'); ?>>Community</option>
            </select>
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Country *</label>
            <select name="country" required class="input-field">
              <!-- <option value="">- Select Country -</option> -->
              <?php
              $countries = ['Australia'];
              foreach ($countries as $c) {
                echo '<option value="' . esc_attr($c) . '" ' . selected($country, $c, false) . '>' . esc_html($c) . '</option>';
              }
              ?>
            </select>
            <span class="field-error"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Account Type *</label>
            <select name="select-2" required class="input-field">
              <option value="">- Select -</option>
              <option value="Business" <?php selected($types, 'Business'); ?>>Business</option>
              <option value="Not For Profit" <?php selected($types, 'Not For Profit'); ?>>Not For Profit</option>
            </select>
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Preferred Contact Method *</label>
            <div class="checkbox-group">
              <label>
                <input type="checkbox" name="preferred_contact[]" value="email" <?php echo in_array('email', $preferred_contact) ? 'checked' : ''; ?>>
                Email
              </label>
              <label>
                <input type="checkbox" name="preferred_contact[]" value="phoneno" <?php echo in_array('phoneno', $preferred_contact) ? 'checked' : ''; ?>>
                Mobile Phone
              </label>
              <label>
                <input type="checkbox" name="preferred_contact[]" value="mobileno" <?php echo in_array('mobileno', $preferred_contact) ? 'checked' : ''; ?>>
                Home Phone
              </label>
            </div>
            <span class="field-error"></span>
          </div>
        </div>

        <!-- ==== COMPANY LOGO (CROPPER) ==== -->
        <div class="form-row uploads-row">
          <div class="form-group">
            <label>Company Logo</label>

            <div class="image-preview" id="logo-preview"
              style="<?php echo $company_logo ? 'background-image:url(' . esc_url($company_logo) . ');' : ''; ?>">
              <?php if ($company_logo): ?>
                <img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_attr($company_name); ?>">
              <?php else: ?>
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/noimage.jpg" alt="No Image">
              <?php endif; ?>
            </div>

            <div class="file-upload">
              <input type="file" name="upload-1" id="logo-upload-input" accept="image/*" class="input-field">
              <small>Profile picture must be in JPG, JPEG or PNG format.</small>
            </div>

            <!-- CROP POPUP -->
            <div id="crop-container" class="crop-container" style="display:none;">
              <div class="crop-overlay"></div>
              <div class="crop-box">
                <img id="crop-image" src="" alt="Crop">
                <div class="crop-controls">
                  <button type="button" id="crop-cancel" class="crop-btn">Cancel</button>
                  <button type="button" id="crop-save" class="crop-btn crop-save">Save</button>
                </div>
              </div>
            </div>
            <span class="field-error"></span>
          </div>
        </div>

        <div class="button-container">
          <button type="submit" class="btn-submit" id="submit-btn">
            <span class="btn-text">Update Profile</span>
            <span class="btn-loader" style="display: none;"><span class="spinner"></span></span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('company-profile-form');
      const submitBtn = document.getElementById('submit-btn');
      const btnText = submitBtn.querySelector('.btn-text');
      const btnLoader = submitBtn.querySelector('.btn-loader');
      const formMessages = document.getElementById('form-messages');

      const logoPreview = document.getElementById('logo-preview');
      const logoInput = document.getElementById('logo-upload-input');
      const cropContainer = document.getElementById('crop-container');
      const cropImg = document.getElementById('crop-image');
      const cancelBtn = document.getElementById('crop-cancel');
      const saveBtn = document.getElementById('crop-save');
      let cropper = null;

      if (!form || !submitBtn || !btnText || !btnLoader || !formMessages || !logoInput || !logoPreview) {
        console.error('Form elements not found:', {
          form,
          submitBtn,
          btnText,
          btnLoader,
          formMessages,
          logoInput,
          logoPreview
        });
        return;
      }

      // click preview → open picker
      logoPreview.addEventListener('click', () => logoInput.click());

      logoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
          alert('Please select an image.');
          this.value = '';
          return;
        }
        const reader = new FileReader();
        reader.onload = e => {
          cropImg.src = e.target.result;
          cropContainer.style.display = 'flex';
          if (cropper) cropper.destroy();
          cropper = new Cropper(cropImg, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            responsive: true
          });
        };
        reader.readAsDataURL(file);
      });

      cancelBtn.addEventListener('click', () => {
        cropContainer.style.display = 'none';
        if (cropper) {
          cropper.destroy();
          cropper = null;
        }
        logoInput.value = '';
        // restore original preview
        <?php if ($company_logo): ?>
          logoPreview.innerHTML = `<img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_js($company_name); ?>">`;
          logoPreview.style.backgroundImage = `url(<?php echo esc_url($company_logo); ?>)`;
        <?php else: ?>
          logoPreview.innerHTML = `<span class="placeholder-text">Click to upload</span>`;
          logoPreview.style.backgroundImage = '';
        <?php endif; ?>
      });

      saveBtn.addEventListener('click', () => {
        if (!cropper) return;
        cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
          })
          .toBlob(blob => {
            const url = URL.createObjectURL(blob);
            logoPreview.innerHTML = `<img src="${url}" alt="<?php echo esc_js($company_name); ?>">`;
            logoPreview.style.backgroundImage = '';

            // replace file input with cropped blob
            const dt = new DataTransfer();
            dt.items.add(new File([blob], 'logo.jpg', {
              type: blob.type
            }));
            logoInput.files = dt.files;

            cropContainer.style.display = 'none';
            if (cropper) {
              cropper.destroy();
              cropper = null;
            }
          }, 'image/jpeg', 0.95);
      });

      const validationRules = [{
          id: 'company_name',
          label: 'Organization Name',
          required: true,
          pattern: /^[A-Za-z\s]+$/,
          patternMessage: 'can only contain letters and spaces'
        },
        {
          id: 'first_name',
          label: 'First Name',
          required: true,
          pattern: /^[A-Za-z]+$/,
          patternMessage: 'can only contain letters'
        },
        {
          id: 'last_name',
          label: 'Last Name',
          required: true,
          pattern: /^[A-Za-z]+$/,
          patternMessage: 'can only contain letters'
        },
        {
          id: 'mobile_phone',
          label: 'Mobile Phone',
          required: true,
          pattern: /^[0-9\s\-\(\)\+]+$/,
          patternMessage: 'must contain only numbers, spaces, dashes, parentheses, or plus signs'
        },
        {
          id: 'office_number',
          label: 'Other Phone',
          required: false,
          pattern: /^[0-9\s\-\(\)\+]+$/,
          patternMessage: 'must contain only numbers, spaces, dashes, parentheses, or plus signs'
        },
        {
          id: 'street_address',
          label: 'Street Address',
          required: true
        },
        {
          id: 'suburb',
          label: 'Suburb',
          required: true
        },
        {
          id: 'state',
          label: 'State',
          required: true
        },
        {
          id: 'postcode',
          label: 'Postcode',
          required: true
        },
        {
          id: 'select-1',
          label: 'Sector',
          required: true
        },
        {
          id: 'country',
          label: 'Country',
          required: true
        },
        {
          id: 'select-2',
          label: 'Account Type',
          required: true
        }
      ];

      function clearFieldError(input) {
        const formGroup = input.closest('.form-group');
        if (formGroup) {
          formGroup.classList.remove('has-error');
          const errorSpan = formGroup.querySelector('.field-error');
          if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
          }
        }
      }

      function showFieldError(input, message) {
        const formGroup = input.closest('.form-group');
        if (formGroup) {
          formGroup.classList.add('has-error');
          const errorSpan = formGroup.querySelector('.field-error');
          if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.style.display = 'block';
          }
        }
      }

      function validateField(input, rule) {
        clearFieldError(input);
        const value = input.value.trim();

        if (rule.required && !value) {
          showFieldError(input, `${rule.label} is required.`);
          return false;
        }

        if (value && rule.pattern && !rule.pattern.test(value)) {
          showFieldError(input, `${rule.label} ${rule.patternMessage}.`);
          return false;
        }

        return true;
      }

      function validatePreferredContact() {
        const checkboxGroup = form.querySelector('.checkbox-group').parentNode;
        const errorSpan = checkboxGroup.querySelector('.field-error');
        const formGroup = checkboxGroup.closest('.form-group');
        const preferredContact = form.querySelectorAll('input[name="preferred_contact[]"]:checked');

        if (preferredContact.length === 0) {
          if (formGroup) {
            formGroup.classList.add('has-error');
            if (errorSpan) {
              errorSpan.textContent = 'Preferred Contact Method is required.';
              errorSpan.style.display = 'block';
            }
          }
          return false;
        } else {
          if (formGroup) {
            formGroup.classList.remove('has-error');
            if (errorSpan) {
              errorSpan.textContent = '';
              errorSpan.style.display = 'none';
            }
          }
          return true;
        }
      }

      function showGlobalMessage(message, type) {
        formMessages.innerHTML = `
    <div class="${type}-message" style="background-color: #00688f;color:white;">
      ${message}
    </div>
  `;
        formMessages.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        if (type === 'success') {
          setTimeout(() => {
            formMessages.innerHTML = '';
          }, 5000);
        }
      }

      function clearAllErrors() {
        form.querySelectorAll('.form-group').forEach(group => {
          group.classList.remove('has-error');
          const errorSpan = group.querySelector('.field-error');
          if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
          }
        });
        formMessages.innerHTML = '';
      }

      // Real-time validation for text inputs and textareas
      form.querySelectorAll('input.input-field[type="text"], textarea.input-field').forEach(input => {
        input.addEventListener('input', () => {
          const rule = validationRules.find(r => r.id === input.name);
          if (rule) {
            validateField(input, rule);
          }
        });
        input.addEventListener('blur', () => {
          const rule = validationRules.find(r => r.id === input.name);
          if (rule) {
            validateField(input, rule);
          }
        });
      });

      // Real-time validation for select fields
      form.querySelectorAll('select.input-field').forEach(select => {
        select.addEventListener('change', () => {
          const rule = validationRules.find(r => r.id === select.name);
          if (rule) {
            validateField(select, rule);
          }
        });
        select.addEventListener('blur', () => {
          const rule = validationRules.find(r => r.id === select.name);
          if (rule) {
            validateField(select, rule);
          }
        });
      });

      // Real-time validation for preferred contact checkboxes
      form.querySelectorAll('input[name="preferred_contact[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', validatePreferredContact);
      });

      form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearAllErrors();

        // Sync TinyMCE content for about_company
        if (typeof tinymce !== 'undefined' && tinymce.get('about_company')) {
          tinymce.get('about_company').save();
        }

        let hasErrors = false;

        // Validate all fields
        validationRules.forEach(rule => {
          const input = form.querySelector(`[name="${rule.id}"]`);
          if (input && !validateField(input, rule)) {
            hasErrors = true;
          }
        });

        // Validate preferred contact
        if (!validatePreferredContact()) {
          hasErrors = true;
        }

        if (hasErrors) {
          console.log('Validation failed - errors shown per field');
          return;
        }

        // Show loader
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-flex';

        const formData = new FormData(form);
        if (typeof tinymce !== 'undefined' && tinymce.get('about_company')) {
          formData.set('about_company', tinymce.get('about_company').getContent());
        }

        fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';

            if (data.success) {
              showGlobalMessage('Profile updated successfully!', 'success');
              setTimeout(() => {
                window.location.reload();
              }, 2000);
            } else {
              const errorMessage = data.data?.message || 'Something went wrong.';
              showGlobalMessage(errorMessage, 'error');
              console.log('Server-side error:', errorMessage);
            }
          })
          .catch(err => {
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            showGlobalMessage(`Error submitting form: ${err.message}`, 'error');
            console.error('Fetch error:', err);
          });
      });
    });
  </script>
<?php
}

// AJAX handler for form submission
function update_company_profile_callback()
{
  global $wpdb;

  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Please log in to update your profile.']);
  }

  if (!check_ajax_referer('company_profile_update', '_wpnonce', false)) {
    wp_send_json_error(['message' => 'Security check failed.']);
  }

  $user_id = get_current_user_id();
  $user_info = get_userdata($user_id);
  $email = $user_info->user_email;
  $errors = [];

  // Required fields
  $required_fields = [
    'company_name' => 'Organization Name',
    'first_name' => 'First Name',
    'last_name' => 'Last Name',
    'mobile_phone' => 'Mobile Phone',
    'street_address' => 'Street Address',
    'suburb' => 'Suburb',
    'state' => 'State',
    'postcode' => 'Postcode',
    'select-1' => 'Sector',
    'country' => 'Country',
    'select-2' => 'Account Type'
  ];

  // Validate required fields
  foreach ($required_fields as $field => $label) {
    if (empty($_POST[$field]) && !isset($_FILES[$field])) {
      $errors[] = "$label is required.";
    }
  }

  // Validate preferred contact
  if (empty($_POST['preferred_contact'])) {
    $errors[] = 'Preferred Contact Method is required.';
  }

  // Validate name fields (letters only)
  if (!empty($_POST['company_name']) && !preg_match('/^[a-zA-Z\s]+$/', $_POST['company_name'])) {
    $errors[] = 'Organization Name can only contain letters and spaces.';
  }
  if (!empty($_POST['first_name']) && !preg_match('/^[a-zA-Z]+$/', $_POST['first_name'])) {
    $errors[] = 'First Name can only contain letters.';
  }
  if (!empty($_POST['last_name']) && !preg_match('/^[a-zA-Z]+$/', $_POST['last_name'])) {
    $errors[] = 'Last Name can only contain letters.';
  }

  // Validate phone numbers
  if (!empty($_POST['mobile_phone']) && !preg_match('/^[0-9\s\-\(\)\+]+$/', $_POST['mobile_phone'])) {
    $errors[] = 'Mobile Phone must contain only numbers, spaces, dashes, parentheses, or plus signs.';
  }
  if (!empty($_POST['office_number']) && !preg_match('/^[0-9\s\-\(\)\+]+$/', $_POST['office_number'])) {
    $errors[] = 'Other Phone must contain only numbers, spaces, dashes, parentheses, or plus signs.';
  }

  if (!empty($errors)) {
    wp_send_json_error(['message' => implode('<br>', $errors)]);
  }

  // Sanitize inputs
  $company_name = sanitize_text_field($_POST['company_name']);
  $first_name = sanitize_text_field($_POST['first_name']);
  $last_name = sanitize_text_field($_POST['last_name']);
  $mobile_phone = sanitize_text_field($_POST['mobile_phone']);
  $office_number = sanitize_text_field($_POST['office_number'] ?? '');
  $about_company = wp_kses_post($_POST['about_company'] ?? '');
  $street_address = sanitize_text_field($_POST['street_address']);
  $suburb = sanitize_text_field($_POST['suburb']);
  $state = sanitize_text_field($_POST['state']);
  $postcode = sanitize_text_field($_POST['postcode']);
  $sector = sanitize_text_field($_POST['select-1']);
  $country = sanitize_text_field($_POST['country']);
  $account_type = sanitize_text_field($_POST['select-2']);
  $preferred_contact = !empty($_POST['preferred_contact']) ? implode(',', array_map('sanitize_text_field', (array) $_POST['preferred_contact'])) : '';

  // Log sanitized inputs for debugging
  error_log('Sanitized inputs: ' . print_r([
    'about_company' => $about_company,
    'state' => $state,
    'country' => $country
  ], true));

  // Handle file upload for company logo
  $company_logo = '';
  if (!empty($_FILES['upload-1']['name'])) {
    $uploaded = media_handle_upload('upload-1', 0);
    if (!is_wp_error($uploaded)) {
      $company_logo = ['file' => ['file_url' => wp_get_attachment_url($uploaded)]];
      $company_logo = maybe_serialize($company_logo);
    } else {
      wp_send_json_error(['message' => 'Error uploading company logo: ' . $uploaded->get_error_message()]);
    }
  }

  // Fetch entry_id
  $entry_id = $wpdb->get_var($wpdb->prepare(
    "SELECT entry_id FROM {$wpdb->prefix}frmt_form_entry_meta WHERE meta_key = 'email-1' AND meta_value = %s ORDER BY entry_id DESC LIMIT 1",
    $email
  ));

  if (!$entry_id) {
    wp_send_json_error(['message' => 'No existing profile found to update.']);
  }

  // Update or insert meta values
  $meta_updates = [
    'name-1' => $company_name,
    'name-2' => $first_name,
    'name-3' => $last_name,
    'phone-1' => $mobile_phone,
    'phone-2' => $office_number,
    'textarea-1' => $about_company,
    'address-1' => maybe_serialize([
      'street_address' => $street_address,
      'city' => $suburb,
      'zip' => $postcode
    ]),
    'select-1' => $sector,
    'select-2' => $account_type,
    'select-3' => $country,
    'select-4' => $state,
    'checkbox-1' => $preferred_contact
  ];

  if ($company_logo) {
    $meta_updates['upload-1'] = $company_logo;
  }

  foreach ($meta_updates as $meta_key => $meta_value) {
    // Check if meta_key exists for this entry_id
    $exists = $wpdb->get_var($wpdb->prepare(
      "SELECT COUNT(*) FROM {$wpdb->prefix}frmt_form_entry_meta WHERE entry_id = %d AND meta_key = %s",
      $entry_id,
      $meta_key
    ));

    if ($exists) {
      // Update existing meta
      $updated = $wpdb->update(
        "{$wpdb->prefix}frmt_form_entry_meta",
        ['meta_value' => $meta_value],
        ['entry_id' => $entry_id, 'meta_key' => $meta_key],
        ['%s'],
        ['%d', '%s']
      );
      error_log("Updating $meta_key for entry_id $entry_id: " . ($updated !== false ? 'Success' : 'Failed'));
    } else {
      // Insert new meta
      $inserted = $wpdb->insert(
        "{$wpdb->prefix}frmt_form_entry_meta",
        [
          'entry_id' => $entry_id,
          'meta_key' => $meta_key,
          'meta_value' => $meta_value
        ],
        ['%d', '%s', '%s']
      );
      error_log("Inserting $meta_key for entry_id $entry_id: " . ($inserted !== false ? 'Success' : 'Failed'));
    }
  }

  wp_send_json_success(['message' => 'Profile updated successfully!']);
}

add_action('wp_ajax_update_company_profile', 'update_company_profile_callback');
add_shortcode('company_profile', 'my_company_profile');
?>