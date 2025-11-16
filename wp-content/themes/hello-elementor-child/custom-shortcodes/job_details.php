<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_job_details()
{
?>
  <main id="content" <?php post_class('site-main'); ?>>

    <style>
      @import url("https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css");
      @import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,500,700,600|Poppins:600|Epilogue:var(--body-small-semibold-font-weight)");
    </style>

    <link rel="stylesheet" type="text/css"
      href="<?php echo get_stylesheet_directory_uri(); ?>/custom-shortcodes/css/job_details.css">

    <div class="container">
      <main class="main-content">
        <div class="content-area">
          <div class="page-header">
            <div class="page-info">
              <h2>Review Job</h2>
              <p>Please review all the details of your job post before submitting it for approval. Ensure the information
                is accurate and complete to attract the right candidates.</p>
            </div>
          </div>

          <?php
          while (have_posts()):
            the_post();

            $post_id = get_the_ID();
            log_job_view(get_the_ID());

            $postDetails = get_post($post_id);
            $author_id = $postDetails->post_author;
            $author_email = get_the_author_meta('user_email', $author_id);

            $company_name = get_post_meta($post_id, '_company_name', true);
            $job_location = get_post_meta($post_id, '_job_location', true);
            $job_level = get_post_meta($post_id, 'job_level', true);
            $job_salary = get_post_meta($post_id, '_job_salary', true);
            $job_salary_currency = get_post_meta($post_id, '_job_salary_currency', true);
            $job_salary_unit = get_post_meta($post_id, '_job_salary_unit', true);
            $closing_date = get_post_meta($post_id, '__job_expires', true);
            $how_to_apply = get_post_meta($post_id, 'how_to_apply', true);

            $job_categories = wp_get_post_terms($post_id, 'job_listing_category');
            $job_tags = wp_get_post_terms($post_id, 'job_listing_tag');
            $job_type_terms = wp_get_post_terms($post_id, 'job_listing_type');
            $job_types = !empty($job_type_terms) ? wp_list_pluck($job_type_terms, 'name') : array();
            $job_type_display = !empty($job_types) ? implode(', ', $job_types) : 'N/A';

            $company_logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')
              ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';

            $attachments = get_post_meta($post_id, '_job_attachments', true);
            $attachment_urls = !empty($attachments) ? (is_array($attachments) ? $attachments : array($attachments)) : array();

            $company_data = get_company_info_by_post($post_id);

          ?>

            <div class="job-review-form">
              <div class="form-content">
                <div class="job-header">
                  <div class="job-main-info">
                    <img src="<?php echo $company_data['logo']; ?>" alt="<?php echo esc_attr($company_name);?>" class="company-logo">
                    <div class="job-details">
                      <h3><?php the_title(); ?></h3>
                      <div class="company-name"><?php echo $company_name; ?></div>
                      <div class="location">
                        <img src="https://c.animaapp.com/mfgi6ai0DfvPqJ/img/frame-1321314801.svg" alt="<?php echo esc_attr($job_location); ?>">
                        <span><?php echo $job_location; ?></span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="job-tags">
                  <div class="tag-row">
                    <?php foreach ($job_types as $type): ?>
                      <span class="tag primary"><?php echo esc_html($type); ?></span>
                    <?php endforeach; ?>
                  </div>

                  <div class="salary-info">
                    <div class="salary-label">Salary</div>
                    <div class="salary-amount">
                      <?php
                      // Format the salary display
                      $salary_display = '';

                      if (!empty($job_salary)) {
                        if (strpos($job_salary, 'above-') !== false) {
                          // For values like "above-100,000"
                          $salary_display = 'Above ' . str_replace('above-', '', $job_salary);
                        } else {
                          // For ranges like "10,000-20,000"
                          $salary_display = $job_salary;
                        }
                      }

                      echo esc_html($salary_display);

                      if (!empty($job_salary_currency)) {
                        echo ' ' . esc_html($job_salary_currency);
                      }

                      if (!empty($job_salary_unit)) {
                        echo ' / ' . ucfirst(strtolower($job_salary_unit));
                      }
                      ?>
                    </div>
                  </div>
                </div>

                <hr class="divider">

                <div class="job-content">
                  <div class="job-left">
                    <section class="job-section" style="margin-top:30px;">
                      <h4>Job Description</h4>
                    <?php echo wpautop($postDetails->post_content); ?>
                    </section>

                    <section class="job-section">
                      <h4>How to Apply</h4>
                      <?php echo get_field('how_to_apply', $post_id); ?>
                    </section>

                    <div class="job-link-container">
                      <?php $job_link = get_field('job_link', $post_id); ?>
                      <button class="job-link-button" onclick="toggleJobLinkPopup()">Job Link</button>
                      <div class="job-link-popup" id="job-link-popup">
                        <input type="text" id="job-link" class="job-link-input" value="<?php echo esc_attr($job_link); ?>" readonly>
                        <a href="#" class="copy-link" onclick="copyJobLink(event)">Copy Link</a>
                        <span id="copy-message" class="copy-message">Link Copied to Clipboard!</span>
                      </div>
                    </div>

                    <script>
                      function copyJobLink(event) {
                        event.preventDefault();
                        const input = document.getElementById("job-link");
                        input.select();
                        input.setSelectionRange(0, 99999);
                        navigator.clipboard.writeText(input.value).then(() => {
                          const message = document.getElementById("copy-message");
                          message.style.display = "block";
                          setTimeout(() => {
                            message.style.display = "none";
                          }, 2000);
                        });
                      }

                      function toggleJobLinkPopup() {
                        const popup = document.getElementById("job-link-popup");
                        popup.classList.toggle("show");
                      }
                    </script>
                  </div>

                  <div class="job-sidebar">
                    <div class="categories-section">
                      <h4>Categories</h4>
                      <div class="category-tags">
                        <?php
                        $categories = wp_get_post_terms($post_id, 'job_listing_category');
                        if (!empty($categories)) {
                          $category_display = '';
                          foreach ($categories as $category) {
                            $category_display .= '<span class="category-tag blue">' . esc_html($category->name) . '</span> ';
                          }
                          $category_display = rtrim($category_display, ', ');
                        } else {
                          $category_display = '<span class="category-tag blue">No Category</span>';
                        }
                        echo $category_display;
                        ?>
                      </div>

                      <div class="closing-date">
                        <div class="date-label">Closing Date</div>
                        <div class="date-value">
                          <?php echo date('M d, Y', strtotime($closing_date)); ?>
                        </div>
                      </div>
                    </div>

                    <?php
                    if (!empty($attachment_urls)) {
                      $attachment_urls = json_decode($attachment_urls[0], true);
                    }

                    if (!empty($attachment_urls)) :
                      if (count($attachment_urls) === 1) :
                        $file_url = $attachment_urls[0]; ?>
                        <a href="<?php echo esc_url($file_url); ?>" class="download-button" download>
                          <svg aria-hidden="true" class="fa-download-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <path d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>
                          </svg>
                          Download Attachment
                        </a>
                      <?php
                      else :
                        $zip_url = add_query_arg(
                          ['action' => 'download_job_attachments', 'post_id' => $post_id],
                          admin_url('admin-ajax.php')
                        ); ?>
                        <a href="<?php echo esc_url($zip_url); ?>" class="download-button">
                          <svg aria-hidden="true" class="fa-download-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <path d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path>
                          </svg>
                          Download Attachments (<?php echo count($attachment_urls); ?>)
                        </a>
                    <?php
                      endif;
                    endif;
                    ?>
                  </div>
                </div>
              </div>
            </div>

            <?php $show_company_info = get_post_meta($post_id, '_show_company_info', true);
            if ($show_company_info == 'yes') : ?>
              <div class="toggle-section">
                <div class="description-section">
                  <div class="description-header">
                    <svg aria-hidden="true" class="fa-accordicon-icon e-font-icon-svg e-fas-minus toggle-minus" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">
                      <path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path>
                    </svg>
                    <span class="description-text">Company Details</span>
                    <svg aria-hidden="true" class="fa-accordicon-icon e-font-icon-svg e-fas-angle-right toggle-angle" viewBox="0 0 256 512" xmlns="http://www.w3.org/2000/svg">
                      <path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z"></path>
                    </svg>
                  </div>
                  <div class="description-content">
                    <h4>Company Name: <?php echo get_post_meta($post_id, '_company_profile', true); ?></h4>
                    <p><?php echo get_post_meta($post_id, '__company_description', true); ?></p>
                    <ul>
                      <li>Email: <?php echo get_post_meta($post_id, '__company_email', true); ?></li>
                      <li>Website: <?php echo get_post_meta($post_id, '__company_website', true); ?></li>
                      <li>Company Location: <?php echo get_post_meta($post_id, '__company_location', true); ?></li>
                      <li>Company Contact: <?php echo get_post_meta($post_id, '__company_contact', true); ?></li>
                    </ul>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <script>
              const header = document.querySelector('.description-header');
              header.addEventListener('click', function() {
                const content = document.querySelector('.description-content');
                const minusSvg = document.querySelector('.toggle-minus');
                const angleSvg = document.querySelector('.toggle-angle');

                const isOpen = content.style.display === 'block';
                content.style.display = isOpen ? 'none' : 'block';
                header.classList.toggle('open', !isOpen);

                if (!isOpen) {
                  minusSvg.innerHTML = '<path d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path>';
                } else {
                  minusSvg.innerHTML = '<path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path>';
                }

                if (!isOpen) {
                  angleSvg.innerHTML = '<path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z"></path>';
                  angleSvg.style.transform = 'rotate(0deg)';
                } else {
                  angleSvg.innerHTML = '<path d="M224.3 239l-136-136c-9.4-9.4-24.6-9.4-33.9 0l-22.6 22.6c-9.4 9.4-9.4 24.6 0 33.9l96.4 96.4-96.4 96.4c-9.4 9.4-9.4 24.6 0 33.9l22.6 22.6c9.4 9.4 24.6 9.4 33.9 0l136-136c9.5-9.4 9.5-24.6.1-34z"></path>';
                  angleSvg.style.transform = 'rotate(90deg)';
                }
              });

              header.setAttribute('tabindex', '0');
            </script>
          <?php endwhile; ?>
        </div>
      </main>
    </div>
  </main>
<?php
}

add_action('init', 'handle_job_attachment_download');
function handle_job_attachment_download()
{
  if (isset($_GET['download_job_attachment']) && is_numeric($_GET['download_job_attachment'])) {
    $post_id = intval($_GET['download_job_attachment']);
    $attachments = get_post_meta($post_id, '_job_attachments', true);
    $attachment_urls = !empty($attachments) ? (is_array($attachments) ? $attachments : array($attachments)) : array();

    if (empty($attachment_urls)) {
      wp_die('No attachments found.');
    }

    if (count($attachment_urls) === 1) {
      $file_url = $attachment_urls[0];
      $file_path = str_replace(wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $file_url);

      if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
      } else {
        wp_die('File not found.');
      }
    } else {
      $zip = new ZipArchive();
      $zip_name = 'job_attachments_' . $post_id . '.zip';
      $temp_file = tempnam(sys_get_temp_dir(), 'job_zip_');

      if ($zip->open($temp_file, ZipArchive::CREATE) === TRUE) {
        foreach ($attachment_urls as $file_url) {
          $file_path = str_replace(wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $file_url);
          if (file_exists($file_path)) {
            $zip->addFile($file_path, basename($file_path));
          }
        }
        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($temp_file));
        readfile($temp_file);
        unlink($temp_file);
        exit;
      } else {
        wp_die('Failed to create zip file.');
      }
    }
  }
}

add_shortcode('job_details', 'my_job_details');
?>