<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_locum_profile()
{
  global $wpdb;

  if (!is_user_logged_in()) {
    echo '<p>Session expired. Please log in again to continue editing.</p>';
    return;
  }

  $user_id = get_current_user_id();
  $user_info = get_userdata($user_id);
  $email = $user_info->user_email;

  // ---- FETCH EXISTING DATA ----------------------------------------
  $entry = $wpdb->get_row($wpdb->prepare(
    "SELECT MAX(CASE WHEN m.meta_key = 'email-1' THEN m.meta_value END) AS email,
            MAX(CASE WHEN m.meta_key = 'name-2' THEN m.meta_value END) AS first_name,
            MAX(CASE WHEN m.meta_key = 'name-3' THEN m.meta_value END) AS last_name,
            MAX(CASE WHEN m.meta_key = 'url-1' THEN m.meta_value END) AS linkedin_url,
            MAX(CASE WHEN m.meta_key = 'phone-1' THEN m.meta_value END) AS phone,
            MAX(CASE WHEN m.meta_key = 'address-1' THEN m.meta_value END) AS address,
            MAX(CASE WHEN m.meta_key = 'select-5' THEN m.meta_value END) AS country,
            MAX(CASE WHEN m.meta_key = 'select-4' THEN m.meta_value END) AS state,
            MAX(CASE WHEN m.meta_key = 'textarea-1' THEN m.meta_value END) AS about,
            MAX(CASE WHEN m.meta_key = 'select-3' THEN m.meta_value END) AS qualification,
            MAX(CASE WHEN m.meta_key = 'text-3' THEN m.meta_value END) AS experience,
            MAX(CASE WHEN m.meta_key = 'select-1' THEN m.meta_value END) AS work_type,
            MAX(CASE WHEN m.meta_key = 'text-5' THEN m.meta_value END) AS specialization,
            MAX(CASE WHEN m.meta_key = 'checkbox-1' THEN m.meta_value END) AS preferred_contact,
            MAX(CASE WHEN m.meta_key = 'upload-2' THEN m.meta_value END) AS profile_image,
            MAX(CASE WHEN m.meta_key = 'upload-1' THEN m.meta_value END) AS cv_file,
            MAX(CASE WHEN m.meta_key = 'status' THEN m.meta_value END) AS status,
            MAX(CASE WHEN m.meta_key = 'select-6' THEN m.meta_value END) AS interested_sector
        FROM {$wpdb->prefix}frmt_form_entry_meta m
        WHERE m.entry_id = (
            SELECT entry_id 
            FROM {$wpdb->prefix}frmt_form_entry_meta 
            WHERE meta_key = 'email-1' AND meta_value = %s 
            ORDER BY entry_id DESC 
            LIMIT 1
        )",
    $email
  ), OBJECT);
  // echo "<pre>";
  //   print_r($entry);
  //   echo "</pre>";
  //   exit;

  // --- Fetch Accreditation Fields ---
  $accreditations = [];

  $accreditation_rows = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value 
   FROM {$wpdb->prefix}frmt_form_entry_meta 
   WHERE entry_id = (
       SELECT entry_id 
       FROM {$wpdb->prefix}frmt_form_entry_meta 
       WHERE meta_key = 'email-1' AND meta_value = %s 
       ORDER BY entry_id DESC 
       LIMIT 1
   ) AND meta_key LIKE 'text-4%%'
   ORDER BY meta_key ASC",
    $email
  ));

  if ($accreditation_rows) {
    foreach ($accreditation_rows as $row) {
      $accreditations[] = $row->meta_value;
    }
  }

  if (!$entry) {
    echo '<p>No locum information found for this account.</p>';
    return;
  }

  // ---- PREPARE VALUES ---------------------------------------------
  $first_name = esc_html($entry->first_name ?? '');
  $last_name = esc_html($entry->last_name ?? '');
  $linkedin_url = esc_url($entry->linkedin_url ?? '');
  $phone = esc_html($entry->phone ?? '');
  $about = wp_kses_post($entry->about ?? '');
  $qualification = esc_html($entry->qualification ?? '');
  $experience = esc_html($entry->experience ?? '');
  $work_type = esc_html($entry->work_type ?? '');
  $specialization = esc_html($entry->specialization ?? '');
  $status = esc_html($entry->status ?? '');
  $street = $city = $zip = '';
  $state = esc_html($entry->state ?? '');
  $country = esc_html($entry->country ?? '');
  $profile_image = '';
  $cv_file_url = '';
  $cv_filename = '';

  // Profile image
  if (!empty($entry->profile_image)) {
    $img = maybe_unserialize($entry->profile_image);
    if (!empty($img['file']['file_url'])) {
      $profile_image = esc_url($img['file']['file_url']);
    }
  }

  // CV
  if (!empty($entry->cv_file)) {
    $cv = maybe_unserialize($entry->cv_file);
    if (isset($cv['file']['file_url'][0])) {
      $cv_file_url = esc_url($cv['file']['file_url'][0]);
      $cv_filename = esc_html(basename($cv['file']['file_path'][0] ?? $cv['file']['file_url'][0]));
    }
  }

  // Address
  if (!empty($entry->address)) {
    $addr = maybe_unserialize($entry->address);
    if (is_array($addr)) {
      $street = esc_html($addr['street_address'] ?? '');
      $city = esc_html($addr['city'] ?? '');
      $zip = esc_html($addr['zip'] ?? '');
    }
  }

  // Preferred contact
  $preferred_contact = [];
  if (!empty($entry->preferred_contact)) {
    $preferred_contact = array_map('trim', explode(',', $entry->preferred_contact));
  }

  // States taxonomy
  $states = get_terms([
    'taxonomy' => 'job_location_category',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
  ]);

  $interested_sector = '';
  if (isset($entry->interested_sector)) {
    foreach ($entry->interested_sector as $meta) {
      if ($meta->meta_key === 'select-6') {
        $interested_sector = $meta->meta_value;
        break;
      }
    }
  }

  // ---- ENQUEUE CROPPER --------------------------------------------
  wp_enqueue_style('cropper-css', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css', [], '1.6.1');
  wp_enqueue_script('cropper-js', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js', [], '1.6.1', true);
?>
  <link rel="stylesheet" type="text/css"
    href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/locum_profile.css">

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <style>

  </style>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function() {
      $('.select2-dropdown').each(function() {
        $(this).select2({
          placeholder: $(this).data('placeholder'),
          allowClear: false,
          minimumResultsForSearch: Infinity // hide search box
        });
      });
    });
  </script>
  <script>
    jQuery(document).ready(function($) {

      function refreshRemoveButtons() {
        const groups = $('.accreditation-group');
        groups.find('.remove').show();
        groups.first().find('.remove').hide();
      }

      refreshRemoveButtons();

      // Add new accreditation field
      $('#addAccreditation').on('click', function() {
        $('#accreditationWrapper').append(`
      <div class="accreditation-group">
        <input type="text" name="accreditation[]" placeholder="Enter Accreditation" required>
        <button type="button" class="remove">Remove</button>
      </div>
    `);
        refreshRemoveButtons();
      });

      // Remove accreditation field
      $(document).on('click', '.accreditation-group .remove', function() {
        $(this).closest('.accreditation-group').remove();
        refreshRemoveButtons();
      });

      // ✅ Validate before AJAX submit
      $(document).on('submit', '#locumProfileForm', function(e) {
        let firstVal = $.trim($('.accreditation-group:first input').val());

        if (firstVal === '') {
          e.preventDefault();
          $('#accreditationError').show();
          return false;
        }

        $('#accreditationError').hide();
      });

    });
  </script>

  <!-- ==== MAIN FORM SCRIPT ==== -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('locum-profile-form');
      const submitBtn = document.getElementById('submit-btn');
      const btnText = submitBtn.querySelector('.btn-text');
      const btnLoader = submitBtn.querySelector('.btn-loader');
      const formMessages = document.getElementById('form-messages');
      const fileInputImage = form.querySelector('input[name="upload-2"]');
      const imagePreview = document.getElementById('image-preview');
      const fileInputCV = form.querySelector('input[name="upload-1"]');
      const cvPreview = document.getElementById('cv-preview');

      if (!form || !submitBtn || !btnText || !btnLoader || !formMessages || !fileInputImage || !imagePreview || !fileInputCV || !cvPreview) {
        console.error('Form elements not found:', {
          form,
          submitBtn,
          btnText,
          btnLoader,
          formMessages,
          fileInputImage,
          imagePreview,
          fileInputCV,
          cvPreview
        });
        return;
      }

      // File input change handler for image preview
      fileInputImage.addEventListener('change', function() {
        const file = this.files[0];
        imagePreview.innerHTML = ''; // Clear existing content
        imagePreview.style.backgroundImage = ''; // Clear background image

        if (file) {
          // Validate file type
          if (!file.type.startsWith('image/')) {
            imagePreview.innerHTML = 'Invalid file type';
            imagePreview.style.color = '#dc2626';
            return;
          }

          // Create and display preview
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Profile Image Preview';
            imagePreview.appendChild(img);
          };
          reader.readAsDataURL(file);
        } else {
          // Restore placeholder if no file selected
          imagePreview.innerHTML = 'Profile Picture';
          imagePreview.style.color = '#999';
        }
      });

      // File input change handler for CV preview
      fileInputCV.addEventListener('change', function() {
        const file = this.files[0];
        cvPreview.innerHTML = ''; // Clear existing content

        if (file) {
          // Validate file type
          if (!['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type)) {
            cvPreview.innerHTML = '<span class="cv-icon">📄</span> Invalid file type';
            cvPreview.style.color = '#dc2626';
            return;
          }

          // Display icon
          cvPreview.innerHTML = '<span class="cv-icon">📄</span>';
          cvPreview.style.color = '#333';
          cvPreview.dataset.cvUrl = ''; // Clear URL until uploaded
        } else {
          // Restore placeholder if no file selected
          cvPreview.innerHTML = '<span class="cv-icon">📄</span>';
          cvPreview.style.color = '#999';
          cvPreview.dataset.cvUrl = '<?php echo esc_url($cv_file_url); ?>'; // Restore original URL
        }
      });

      // Click handler for CV preview to open URL
      cvPreview.addEventListener('click', function(e) {
        const cvUrl = this.dataset.cvUrl;
        if (cvUrl) {
          window.open(cvUrl, '_blank');
        }
      });

      const validationRules = [{
          id: 'name-2',
          label: 'First Name',
          required: true,
          pattern: /^[A-Za-z]+$/,
          patternMessage: 'can only contain letters'
        },
        {
          id: 'name-3',
          label: 'Last Name',
          required: true,
          pattern: /^[A-Za-z]+$/,
          patternMessage: 'can only contain letters'
        },
        {
          id: 'phone-1',
          label: 'Mobile Phone',
          required: true,
          pattern: /^[0-9\s\-\(\)\+]+$/,
          patternMessage: 'must contain only numbers, spaces, dashes, parentheses, or plus signs'
        },
        {
          id: 'url-1',
          label: 'Website',
          required: false,
          pattern: /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/,
          patternMessage: 'must be a valid URL'
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
          id: 'select-4',
          label: 'State',
          required: true
        },
        {
          id: 'postcode',
          label: 'Postcode',
          required: true
        },
        {
          id: 'select-5',
          label: 'Country',
          required: true
        },
        {
          id: 'select-3',
          label: 'Qualification',
          required: true
        },
        {
          id: 'text-3',
          label: 'Experience',
          required: true,
          pattern: /^[0-9]+$/,
          patternMessage: 'must be a number'
        },
        {
          id: 'select-1',
          label: 'Work Type',
          required: true
        },
        {
          id: 'text-5',
          label: 'Specialization',
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
        console.log('Showing global message:', {
          message,
          type
        });
        formMessages.innerHTML = `<div class="${type}-message">${message}</div>`;
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
      form.querySelectorAll('input.input-field[type="text"], input.input-field[type="url"], input.input-field[type="number"]').forEach(input => {
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

        // Sync TinyMCE content for about section
        if (typeof tinymce !== 'undefined' && tinymce.get('textarea-1')) {
          tinymce.get('textarea-1').save(); // Ensure the ID matches the wp_editor ID
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

        // Validate CV file type
        if (fileInputCV.files[0]) {
          const fileType = fileInputCV.files[0].type;
          if (!['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(fileType)) {
            showFieldError(fileInputCV, 'CV must be a PDF, DOC, or DOCX file.');
            hasErrors = true;
          }
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
        // Explicitly add TinyMCE content to FormData
        if (typeof tinymce !== 'undefined' && tinymce.get('textarea-1')) {
          formData.set('textarea-1', tinymce.get('textarea-1').getContent());
        }

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
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
              showGlobalMessage('Locum profile updated successfully.', 'success');
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

  <!-- ==== SECTOR TAGS SCRIPT ==== -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const dropdownTrigger = document.getElementById('dropdown-trigger');
      const dropdownMenu = document.getElementById('dropdown-menu');
      const multiselect = document.querySelector('.multiselect-container');
      const selectedTagsContainer = document.getElementById('selected-sectors');
      const searchInput = document.getElementById('search-input');
      const optionsList = document.getElementById('options-list');
      const options = optionsList.querySelectorAll('.option');
      const hiddenInput = document.getElementById('interested-sector-input');
      const placeholder = document.querySelector('.placeholder');
      const clearAll = document.getElementById('clear-all');
      const arrow = document.querySelector('.arrow');

      let selectedValues = [];

      // Toggle dropdown
      dropdownTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleDropdown();
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!multiselect.contains(e.target) && !dropdownMenu.contains(e.target)) {
          closeDropdown();
        }
      });

      // Prevent dropdown from closing when clicking inside
      dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
      });

      // Search functionality
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        options.forEach(option => {
          const text = option.textContent.toLowerCase();
          option.classList.toggle('hidden', !text.includes(searchTerm));
        });
      });

      // Option selection
      options.forEach(option => {
        option.addEventListener('click', function() {
          const value = this.getAttribute('data-value');
          if (selectedValues.includes(value)) return; // ✅ prevent reselect
          addSelection(value);
        });
      });

      // Clear all selections
      clearAll.addEventListener('click', function(e) {
        e.stopPropagation();
        clearAllSelections();
      });

      function toggleDropdown() {
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        multiselect.classList.toggle('open');
        if (dropdownMenu.style.display === 'block') searchInput.focus();
      }

      function closeDropdown() {
        dropdownMenu.style.display = 'none';
        multiselect.classList.remove('open');
        searchInput.value = '';
        options.forEach(option => option.classList.remove('hidden'));
      }

      function addSelection(value) {
        if (!selectedValues.includes(value)) {
          selectedValues.push(value);
          updateUI();
          updateHiddenInput();
        }
      }

      function removeSelection(value) {
        selectedValues = selectedValues.filter(v => v !== value);
        updateUI();
        updateHiddenInput();
      }

      function clearAllSelections() {
        selectedValues = [];
        updateUI();
        updateHiddenInput();
      }

      function updateUI() {
        selectedTagsContainer.innerHTML = '';
        selectedValues.forEach(value => {
          const tag = document.createElement('div');
          tag.className = 'tag';
          tag.innerHTML = `
          ${value}
          <span class="tag-remove" data-value="${value}">×</span>
        `;
          selectedTagsContainer.appendChild(tag);

          tag.querySelector('.tag-remove').addEventListener('click', function(e) {
            e.stopPropagation();
            removeSelection(this.getAttribute('data-value'));
          });
        });

        // ✅ Mark already selected ones in dropdown as disabled
        options.forEach(option => {
          const value = option.getAttribute('data-value');
          if (selectedValues.includes(value)) {
            option.classList.add('selected');
            option.style.pointerEvents = 'none';
            option.style.opacity = '0.6';
          } else {
            option.classList.remove('selected');
            option.style.pointerEvents = 'auto';
            option.style.opacity = '1';
          }
        });

        if (selectedValues.length > 0) {
          placeholder.style.display = 'none';
          clearAll.style.display = 'block';
          dropdownTrigger.classList.add('has-selections');
        } else {
          placeholder.style.display = 'block';
          clearAll.style.display = 'none';
          dropdownTrigger.classList.remove('has-selections');
        }
      }

      function updateHiddenInput() {
        hiddenInput.value = selectedValues.join(',');
      }

      // ✅ Initialize with any pre-selected values (from DB)
      const preSelected = hiddenInput.value;
      if (preSelected) {
        selectedValues = preSelected.split(',').map(v => v.trim()).filter(v => v !== '');
        updateUI();
      }
    });
  </script>

  <!-- ==== CROPPER LOGIC (SQUARE PREVIEW ONLY) ==== -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const preview = document.getElementById('image-preview');
      const fileInput = document.getElementById('profile-upload-input');
      const cropContainer = document.getElementById('crop-container');
      const cropImg = document.getElementById('crop-image');
      const cancelBtn = document.getElementById('crop-cancel');
      const saveBtn = document.getElementById('crop-save');

      let cropper = null;

      // Click preview → open file picker
      preview.addEventListener('click', () => fileInput.click());

      // File selected → open cropper
      fileInput.addEventListener('change', function() {
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
            autoCropArea: 0.85,
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

      // Cancel
      cancelBtn.addEventListener('click', () => {
        cropContainer.style.display = 'none';
        if (cropper) {
          cropper.destroy();
          cropper = null;
        }
        fileInput.value = '';
        // restore original preview
        if ('<?php echo $profile_image; ?>') {
          preview.innerHTML = `<img src="<?php echo esc_url($profile_image); ?>" alt="<?php echo esc_attr($first_name . ' ' . $last_name); ?>">`;
          preview.style.backgroundImage = `url(<?php echo esc_url($profile_image); ?>)`;
        } else {
          preview.innerHTML = `<span class="placeholder-text">Click to upload</span>`;
          preview.style.backgroundImage = '';
        }
      });

      // Save → show ONLY in square preview
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
            preview.innerHTML = `<img src="${url}" alt="Cropped picture">`;
            preview.style.backgroundImage = ''; // rely on <img>

            // replace file input with cropped blob
            const dt = new DataTransfer();
            dt.items.add(new File([blob], 'profile.jpg', {
              type: blob.type
            }));
            fileInput.files = dt.files;

            cropContainer.style.display = 'none';
            if (cropper) {
              cropper.destroy();
              cropper = null;
            }
          }, 'image/jpeg', 0.95);
      });
    });
  </script>
  <script>
    jQuery(document).ready(function($) {
      // Block numbers & symbols in accreditation fields
      $(document).on('input', 'input[name="accreditation[]"]', function() {
        this.value = this.value.replace(/[^A-Za-z\s]/g, '');
      });

      // Also block paste of invalid chars
      $(document).on('paste', 'input[name="accreditation[]"]', function(e) {
        setTimeout(() => {
          this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        }, 0);
      });
    });
  </script>
  <div class="container">
    <div class="card">
      <div id="form-messages"></div>
      <form method="post" enctype="multipart/form-data" id="locum-profile-form">
        <?php wp_nonce_field('locum_profile_update', '_wpnonce'); ?>
        <input type="hidden" name="action" value="update_locum_profile">
        <input type="hidden" name="update_locum_profile" value="1">

        <!-- EMAIL -->
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?php echo esc_attr($email); ?>" readonly class="input-field">
          <span class="field-error"></span>
        </div>

        <!-- NAME ROW -->
        <div class="form-row">
          <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="name-2" value="<?php echo esc_attr($first_name); ?>" required pattern="[A-Za-z]+"
              class="input-field">
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="name-3" value="<?php echo esc_attr($last_name); ?>" required pattern="[A-Za-z]+"
              class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <!-- PHONE & LINKEDIN -->
        <div class="form-row">
          <div class="form-group">
            <label>Mobile Number *</label>
            <input type="text" name="phone-1" value="<?php echo esc_attr($phone); ?>" required pattern="[0-9\s\-\(\)\+]+"
              class="input-field">
            <span class="field-error"></span>
          </div>

          <div class="form-group">
            <label>Linkedin</label>
            <input type="url" name="url-1" value="<?php echo esc_attr($linkedin_url); ?>" class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <!-- ABOUT -->
        <div class="form-group">
          <label>About Me</label>
          <?php
          $editor_settings = [
            'textarea_name' => 'textarea-1',
            'editor_height' => 200,
            'media_buttons' => false,
            'teeny' => true,
            'textarea_rows' => 10,
            'quicktags' => ['buttons' => 'strong,em,link,ul,ol,li,code'],
            'tinymce' => ['toolbar1' => 'bold,italic,underline,link,undo,redo'],
          ];
          wp_editor($about, 'textarea-1', $editor_settings);
          ?>
          <span class="field-error"></span>
        </div>

        <!-- QUALIFICATION & EXPERIENCE -->
        <div class="form-row">
          <div class="form-group">
            <label>Highest Education Qualification *</label>
            <select name="select-3" required class="input-field select2-dropdown" id="educationSelect"
              data-placeholder="-Select Qualification-">

              <option value="Phd" <?php selected($qualification, 'Phd'); ?>>PhD</option>
              <option value="Masters" <?php selected($qualification, 'Masters'); ?>>Masters</option>
              <option value="Diploma" <?php selected($qualification, 'Diploma'); ?>>Diploma</option>
              <option value="Bachelors" <?php selected($qualification, 'Bachelors'); ?>>Bachelors</option>
              <option value="High School" <?php selected($qualification, 'High School'); ?>>High School</option>
            </select>
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Year of Experience *</label>
            <input type="number" name="text-3" value="<?php echo esc_attr($experience); ?>" required min="0"
              class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <!-- AVAILABILITY & SPECIALIZATION -->
        <div class="form-row">
          <div class="form-group">
            <label>Availability *</label>
            <select name="select-1" required class="input-field select2-dropdown">

              <option value="Full Time" <?php selected($work_type, 'Full Time'); ?>>Full Time</option>
              <option value="Part Time" <?php selected($work_type, 'Part Time'); ?>>Part Time</option>
              <option value="Contract" <?php selected($work_type, 'Contract'); ?>>Contract</option>
            </select>
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>Specialization *</label>
            <input type="text" name="text-5" value="<?php echo esc_attr($specialization); ?>" required
              class="input-field">
            <span class="field-error"></span>
          </div>
        </div>

        <div class="form-group accreditation-id">
          <label>Accreditation *</label>
          <div id="accreditationWrapper">
            <?php if (!empty($accreditations)): ?>
              <?php foreach ($accreditations as $i => $acc): ?>
                <div class="accreditation-group">
                  <input type="text" name="accreditation[]" value="<?php echo esc_attr($acc); ?>" placeholder="Enter Accreditation"
                    pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" <?php echo $i === 0 ? 'required' : ''; ?>
                    oninvalid="this.setCustomValidity('Only letters and spaces are allowed')" oninput="this.setCustomValidity('')">
                  <button type="button" class="remove">Remove</button>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="accreditation-group">
                <input type="text" name="accreditation[]" placeholder="Enter Accreditation" required pattern="[A-Za-z\s]+"
                  title="Only letters and spaces allowed" oninvalid="this.setCustomValidity('Only letters and spaces are allowed')"
                  oninput="this.setCustomValidity('')">
                <button type="button" class="remove">Remove</button>
              </div>
            <?php endif; ?>

          </div>
          <button type="button" id="addAccreditation" class="add-btn">+ Add More</button>
          <span class="field-error"></span>
        </div>

        <!-- INTERESTED SECTORS -->
        <div class="form-group">
          <label>Interested Sector <span class="required">*</span></label>
          <div id="sector-wrapper" class="sector-wrapper">
            <div class="multiselect-container">
              <div class="multiselect-inner" id="dropdown-trigger">
                <div id="selected-sectors" class="selected-tags"></div>
                <span class="placeholder">-Select Sector-</span>
                <div class="dropdown-controls">
                  <span class="clear-all" id="clear-all" style="display: none;">×</span>
                  <span class="arrow">▲</span>
                </div>
              </div>
            </div>

            <div class="dropdown-menu" id="dropdown-menu" style="display: none;">
              <div class="search-wrapper">
                <input type="text" id="search-input" class="search-input" placeholder="Search">
              </div>
              <div class="options-list" id="options-list">
                <div class="option" data-value="Academia">Academia</div>
                <div class="option" data-value="Accountant">Accountant</div>
                <div class="option" data-value="Administration">Administration</div>
                <div class="option" data-value="Aged Care">Aged Care</div>
                <div class="option" data-value="Alcohol, tobacco and other drug">Alcohol, tobacco and other drug</div>
                <div class="option" data-value="Assessments">Assessments</div>
                <div class="option" data-value="Audits / accreditations">Audits / accreditations</div>
                <div class="option" data-value="Business Development">Business Development</div>
                <div class="option" data-value="Case Management">Case Management</div>
                <div class="option" data-value="Child">Child</div>
                <div class="option" data-value="Child protection">Child protection</div>
                <div class="option" data-value="Community & Development">Community & Development</div>
                <div class="option" data-value="Consultancy">Consultancy</div>
                <div class="option" data-value="Corrections">Corrections</div>
                <div class="option" data-value="Counselling / Therapy">Counselling / Therapy</div>
                <div class="option" data-value="Culturally and Linguistically Diverse">Culturally and Linguistically
                  Diverse</div>
                <div class="option" data-value="Cyber Security">Cyber Security</div>
                <div class="option" data-value="Defence">Defence</div>
                <div class="option" data-value="Digital Content">Digital Content</div>
                <div class="option" data-value="Disability">Disability</div>
                <div class="option" data-value="Eating disorders">Eating disorders</div>
                <div class="option" data-value="Education">Education</div>
                <div class="option" data-value="Emergency Care">Emergency Care</div>
                <div class="option" data-value="Employee Assistance Provider (EAP)">Employee Assistance Provider (EAP)
                </div>
                <div class="option" data-value="Ethical / Legal / Regulatory Compliance">Ethical / Legal / Regulatory
                  Compliance</div>
                <div class="option" data-value="Event management">Event management</div>
                <div class="option" data-value="Families/Carers">Families/Carers</div>
                <div class="option" data-value="Family violence">Family violence</div>
                <div class="option" data-value="Health">Health</div>
                <div class="option" data-value="Hospital">Hospital</div>
                <div class="option" data-value="Housing">Housing</div>
                <div class="option" data-value="Income Support">Income Support</div>
                <div class="option" data-value="Infants">Infants</div>
                <div class="option" data-value="Management / Leadership">Management / Leadership</div>
                <div class="option" data-value="Marketing / Communications">Marketing / Communications</div>
                <div class="option" data-value="Mental Health">Mental Health</div>
                <div class="option" data-value="Palliative Care / End of Life">Palliative Care / End of Life</div>
                <div class="option" data-value="Policy / Advocacy">Policy / Advocacy</div>
                <div class="option" data-value="Project Management">Project Management</div>
                <div class="option" data-value="Research">Research</div>
                <div class="option" data-value="Sexual assault">Sexual assault</div>
                <div class="option" data-value="Social Worker">Social Worker</div>
                <div class="option" data-value="Supervision">Supervision</div>
                <div class="option" data-value="Training">Training</div>
                <div class="option" data-value="Trauma">Trauma</div>
                <div class="option" data-value="Veterans">Veterans</div>
                <div class="option" data-value="Women/Children">Women/Children</div>
                <div class="option" data-value="Youth">Youth</div>
              </div>
            </div>
          </div>
          <input type="hidden" name="select-6" id="interested-sector-input"
            value="<?php echo esc_attr($entry->interested_sector ?? ''); ?>">
        </div>
        <div class="form-row">
          <div class="form-group" style="flex:1;">
            <label>Country *</label>
            <select name="select-5" required class="input-field select2-dropdown">

              <?php foreach (['Australia'] as $c)
                echo '<option value="' . esc_attr($c) . '" ' . selected($country, $c, false) . '>' . esc_html($c) . '</option>'; ?>
            </select>
            <span class="field-error"></span>
          </div>
          <div class="form-group">
            <label>State/Province *</label>
            <select name="select-4" required class="input-field select2-dropdown">
              <?php
              if (!is_wp_error($states) && !empty($states)) {
                foreach ($states as $term) {
                  $sel = ((string) $entry->state === (string) $term->slug ||
                    (string) $entry->state === (string) $term->name ||
                    (string) $entry->state === (string) $term->term_id);
                  echo '<option value="' . esc_attr($term->name) . '"' . ($sel ? ' selected' : '') . '>' . esc_html($term->name) . '</option>';
                }
              } else {
                echo '<option value="">No states</option>';
              }
              ?>
            </select>
            <span class="field-error"></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>City/Suburb *</label>
            <input type="text" name="suburb" value="<?php echo esc_attr($city); ?>" required class="input-field">
            <span class="field-error"></span>
          </div>

          <div class="form-group">
            <label>Post Code *</label>
            <input type="text" name="postcode" value="<?php echo esc_attr($zip); ?>" required class="input-field">
            <span class="field-error"></span>
          </div>
        </div>
        <!-- ADDRESS -->
        <div class="form-group">
          <label>Street Address *</label>
          <input type="text" name="street_address" value="<?php echo esc_attr($street); ?>" required class="input-field">
          <span class="field-error"></span>
        </div>

        <!-- COUNTRY & STATUS -->
        <div class="form-row" style="gap:20px;">

          <div class="form-group" style="flex:1;">
            <label>Status *</label>
            <select name="status" required class="input-field select2-dropdown">

              <option value="Open" <?php selected($status, 'Open'); ?>>Open</option>
              <option value="Busy" <?php selected($status, 'Busy'); ?>>Busy</option>
              <option value="On Leave" <?php selected($status, 'On Leave'); ?>>On Leave</option>
            </select>
            <span class="field-error"></span>
          </div>
        </div>

        <!-- PREFERRED CONTACT -->
        <div class="form-group">
          <label>Preferred Contact Method *</label>
          <div class="checkbox-group">
            <label><input type="checkbox" name="preferred_contact[]" value="Email" <?php echo in_array('Email', $preferred_contact) ? 'checked' : ''; ?>> Email</label>
            <label><input type="checkbox" name="preferred_contact[]" value="Phone No." <?php echo in_array('Phone No.', $preferred_contact) ? 'checked' : ''; ?>> Mobile No.</label>
            <!-- <label><input type="checkbox" name="preferred_contact[]" value="Mobile No." <?php echo in_array('Mobile No.', $preferred_contact) ? 'checked' : ''; ?>> Mobile No.</label> -->
          </div>
          <span class="field-error"></span>
        </div>

        <!-- ==== SIDE-BY-SIDE: PROFILE + CV ==== -->
        <div class="form-row uploads-row">

          <!-- PROFILE PICTURE (SQUARE) -->
          <div class="form-group">
            <label>Profile Picture</label>

            <div class="image-preview" id="image-preview"
              style="<?php echo $profile_image ? 'background-image:url(' . esc_url($profile_image) . ');' : ''; ?>">
              <?php if ($profile_image): ?>
                <img src="<?php echo esc_url($profile_image); ?>" alt="<?php echo esc_attr($first_name . ' ' . $last_name); ?>">
              <?php else: ?>
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/noimage.jpg" alt="No Image">
              <?php endif; ?>
            </div>

            <div class="file-upload">
              <input type="file" name="upload-2" id="profile-upload-input" accept="image/*" class="input-field">
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

          <?php
          $cv_icon = '<span class="cv-icon">CV</span>'; // default fallback icon

          if (!empty($cv_file_url)) {
            $file_ext = strtolower(pathinfo($cv_file_url, PATHINFO_EXTENSION));

            if ($file_ext === 'pdf') {
              $cv_icon = '<img src="' . esc_url(get_stylesheet_directory_uri() . '/images/pdf-icon.jpg') . '" alt="PDF" style="width:100%;height:auto;">';
            } elseif (in_array($file_ext, ['doc', 'docx'])) {
              $cv_icon = '<img src="' . esc_url(get_stylesheet_directory_uri() . '/images/doc-icon.png') . '" alt="Word" style="width:100%;height:auto;">';
            }
          }
          ?>

          <div class="form-group">
            <label>Update CV</label>
            <div class="file-upload">
              <div class="file-preview" id="cv-preview" data-cv-url="<?php echo esc_url($cv_file_url); ?>">
                <?php echo $cv_icon; ?>
              </div>
              <input type="file" name="upload-1" accept=".pdf,.doc,.docx" class="input-field" id="uploadCV">
              <small>Upload a new CV (PDF, DOC, DOCX).</small>
            </div>
            <span class="field-error"></span>
          </div>

        </div>
        <!-- ==== END SIDE-BY-SIDE ==== -->

        <div class="button-container">
          <button type="submit" class="btn-submit" id="submit-btn">
            <span class="btn-text">Update Profile</span>
            <span class="btn-loader" style="display:none;"><span class="spinner"></span></span>
          </button>
        </div>
      </form>
    </div>
  </div>

<?php
}

// AJAX handler for form submission
function update_locum_profile_callback()
{
  global $wpdb;

  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Session expired. Please log in again to continue editing']);
  }

  if (!check_ajax_referer('locum_profile_update', '_wpnonce', false)) {
    wp_send_json_error(['message' => 'Security check failed.']);
  }

  $user_id = get_current_user_id();
  $user_info = get_userdata($user_id);
  $email = $user_info->user_email;
  $errors = [];

  // Required fields
  $required_fields = [
    'name-2' => 'First Name',
    'name-3' => 'Last Name',
    'phone-1' => 'Mobile Phone',
    'street_address' => 'Street Address',
    'suburb' => 'Suburb',
    'select-4' => 'State',
    'postcode' => 'Postcode',
    'select-5' => 'Country',
    'select-3' => 'Qualification',
    'text-3' => 'Experience',
    'select-1' => 'Work Type',
    'text-5' => 'Specialization',
    'status' => 'status'
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
  if (!empty($_POST['name-2']) && !preg_match('/^[A-Za-z]+$/', $_POST['name-2'])) {
    $errors[] = 'First Name can only contain letters.';
  }
  if (!empty($_POST['name-3']) && !preg_match('/^[A-Za-z]+$/', $_POST['name-3'])) {
    $errors[] = 'Last Name can only contain letters.';
  }

  // Validate phone number
  if (!empty($_POST['phone-1']) && !preg_match('/^[0-9\s\-\(\)\+]+$/', $_POST['phone-1'])) {
    $errors[] = 'Mobile Phone must contain only numbers, spaces, dashes, parentheses, or plus signs.';
  }

  // Validate website URL
  if (!empty($_POST['url-1']) && !preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $_POST['url-1'])) {
    $errors[] = 'Website must be a valid URL.';
  }

  // Validate experience
  if (!empty($_POST['text-3']) && !preg_match('/^[0-9]+$/', $_POST['text-3'])) {
    $errors[] = 'Experience must be a number.';
  }

  // Validate CV file type
  if (!empty($_FILES['upload-1']['name'])) {
    $file_type = $_FILES['upload-1']['type'];
    if (!in_array($file_type, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
      $errors[] = 'CV must be a PDF, DOC, or DOCX file.';
    }
  }
  if (
    empty($_POST['accreditation']) ||
    !is_array($_POST['accreditation']) ||
    empty(trim($_POST['accreditation'][0]))
  ) {
    $errors[] = 'First Accreditation is required.';
  }

  if (!empty($errors)) {
    wp_send_json_error(['message' => implode('<br>', $errors)]);
  }

  // Sanitize inputs
  $first_name = sanitize_text_field($_POST['name-2']);
  $last_name = sanitize_text_field($_POST['name-3']);
  $phone = sanitize_text_field($_POST['phone-1']);
  $linkedin_url = sanitize_text_field($_POST['url-1'] ?? '');
  $about = wp_kses_post($_POST['textarea-1'] ?? '');
  $qualification = sanitize_text_field($_POST['select-3']);
  $experience = sanitize_text_field($_POST['text-3']);
  $work_type = sanitize_text_field($_POST['select-1']);
  $specialization = sanitize_text_field($_POST['text-5']);
  $street_address = sanitize_text_field($_POST['street_address']);
  $suburb = sanitize_text_field($_POST['suburb']);
  $state = sanitize_text_field($_POST['select-4']);
  $postcode = sanitize_text_field($_POST['postcode']);
  $country = sanitize_text_field($_POST['select-5']);
  $preferred_contact = !empty($_POST['preferred_contact']) ? implode(',', array_map('sanitize_text_field', (array) $_POST['preferred_contact'])) : '';
  $status = sanitize_text_field($_POST['status']);
  // Handle file upload for profile image
  $profile_image = '';
  if (!empty($_FILES['upload-2']['name'])) {
    $uploaded = media_handle_upload('upload-2', 0);
    if (!is_wp_error($uploaded)) {
      $profile_image = ['file' => ['file_url' => wp_get_attachment_url($uploaded)]];
      $profile_image = maybe_serialize($profile_image);
    } else {
      wp_send_json_error(['message' => 'Error uploading profile image: ' . $uploaded->get_error_message()]);
    }
  }

  $accreditations = [];
  if (!empty($_POST['accreditation']) && is_array($_POST['accreditation'])) {
    $accreditations = array_filter(array_map('sanitize_text_field', $_POST['accreditation']));
  }

  // Handle file upload for CV
  $cv_file = '';
  if (!empty($_FILES['upload-1']['name'])) {
    $uploaded = media_handle_upload('upload-1', 0);
    if (!is_wp_error($uploaded)) {
      $cv_file = [
        'file' => [
          'success' => true,
          'file_url' => [wp_get_attachment_url($uploaded)],
          'file_path' => [get_attached_file($uploaded)]
        ]
      ];
      $cv_file = maybe_serialize($cv_file);
    } else {
      wp_send_json_error(['message' => 'Error uploading CV: ' . $uploaded->get_error_message()]);
    }
  }

  // Update the database
  $entry_id = $wpdb->get_var($wpdb->prepare(
    "SELECT entry_id FROM {$wpdb->prefix}frmt_form_entry_meta WHERE meta_key = 'email-1' AND meta_value = %s ORDER BY entry_id DESC LIMIT 1",
    $email
  ));

  if ($entry_id) {
    $meta_updates = [
      'name-2' => $first_name,
      'name-3' => $last_name,
      'phone-1' => $phone,
      'url-1' => $linkedin_url,
      'textarea-1' => $about,
      'select-3' => $qualification,
      'text-3' => $experience,
      'select-1' => $work_type,
      'text-5' => $specialization,
      'status' => $status,
      'address-1' => maybe_serialize([
        'street_address' => $street_address,
        'city' => $suburb,
        'zip' => $postcode
      ]),
      'select-4' => $state,
      'select-5' => $country,
      'checkbox-1' => $preferred_contact
    ];

    if (isset($_POST['select-6'])) {
      $interested_sector = sanitize_text_field($_POST['select-6']);
      $meta_updates['select-6'] = $interested_sector;
    }


    if ($profile_image) {
      $meta_updates['upload-2'] = $profile_image;
    }
    if ($cv_file) {
      $meta_updates['upload-1'] = $cv_file;
    }

    foreach ($meta_updates as $meta_key => $meta_value) {
      $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_id FROM {$wpdb->prefix}frmt_form_entry_meta WHERE entry_id = %d AND meta_key = %s",
        $entry_id,
        $meta_key
      ));

      if ($existing) {
        $wpdb->update(
          "{$wpdb->prefix}frmt_form_entry_meta",
          ['meta_value' => $meta_value],
          ['entry_id' => $entry_id, 'meta_key' => $meta_key],
          ['%s'],
          ['%d', '%s']
        );
      } else {
        $wpdb->insert(
          "{$wpdb->prefix}frmt_form_entry_meta",
          [
            'entry_id' => $entry_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'date_created' => current_time('mysql'),
            'date_updated' => '0000-00-00 00:00:00'
          ],
          ['%d', '%s', '%s', '%s', '%s']
        );
      }
    }

    if (!empty($accreditations)) {
      $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}frmt_form_entry_meta WHERE entry_id = %d AND meta_key LIKE 'text-4%%'",
        $entry_id
      ));

      $count = 0;
      foreach ($accreditations as $acc) {
        $meta_key = ($count === 0) ? 'text-4' : 'text-4-' . ($count + 1);
        $wpdb->insert(
          "{$wpdb->prefix}frmt_form_entry_meta",
          [
            'entry_id' => $entry_id,
            'meta_key' => $meta_key,
            'meta_value' => $acc,
            'date_created' => current_time('mysql'),
            'date_updated' => '0000-00-00 00:00:00'
          ],
          ['%d', '%s', '%s', '%s', '%s']
        );
        $count++;
      }
    }


    wp_send_json_success(['message' => 'Locum profile updated successfully.']);
  } else {
    wp_send_json_error(['message' => 'No existing profile found to update.']);
  }
}

add_action('wp_ajax_update_locum_profile', 'update_locum_profile_callback');
add_shortcode('locum_profile', 'my_locum_profile');
?>