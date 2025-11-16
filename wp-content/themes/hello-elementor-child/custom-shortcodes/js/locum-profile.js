$(document).ready(function () {
  $(".select2-dropdown").each(function () {
    $(this).select2({
      placeholder: $(this).data("placeholder"),
      allowClear: false,
      minimumResultsForSearch: Infinity, // hide search box
    });
  });
});
////////////////////////////// MAIN FORM SCRIPT 
 document.addEventListener('DOMContentLoaded', function () {
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
      fileInputImage.addEventListener('change', function () {
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
          reader.onload = function (e) {
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
      fileInputCV.addEventListener('change', function () {
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
      cvPreview.addEventListener('click', function (e) {
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

      form.addEventListener('submit', function (e) {
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


   /////////////////////////////////////////////////////////////SECTOR TAGS SCRIPT
        document.addEventListener('DOMContentLoaded', function () {
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
      dropdownTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleDropdown();
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', function (e) {
        if (!multiselect.contains(e.target) && !dropdownMenu.contains(e.target)) {
          closeDropdown();
        }
      });

      // Prevent dropdown from closing when clicking inside
      dropdownMenu.addEventListener('click', function (e) {
        e.stopPropagation();
      });

      // Search functionality
      searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        options.forEach(option => {
          const text = option.textContent.toLowerCase();
          option.classList.toggle('hidden', !text.includes(searchTerm));
        });
      });

      // Option selection
      options.forEach(option => {
        option.addEventListener('click', function () {
          const value = this.getAttribute('data-value');
          if (selectedValues.includes(value)) return; // ✅ prevent reselect
          addSelection(value);
        });
      });

      // Clear all selections
      clearAll.addEventListener('click', function (e) {
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

          tag.querySelector('.tag-remove').addEventListener('click', function (e) {
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


    //////////////////////////////////////////////CROPPER LOGIC (SQUARE PREVIEW ONLY) ///////////////////////////////////////
        document.addEventListener('DOMContentLoaded', function () {
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
      fileInput.addEventListener('change', function () {
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
          preview.innerHTML = `<img src="<?php echo esc_url($profile_image); ?>" alt="Current picture">`;
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