<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

function my_search_job()
{
  ob_start();

  // Fetch all jobs
  $jobs = new WP_Query([
    'post_type' => 'job_listing',
    'post_status' => 'publish',
    'posts_per_page' => -1,
  ]);
?>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {

      background-color: #f5f7fa;
      color: #333;
      line-height: 1.6;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px;
    }

    /* Tabs */
    .tabs {
      display: flex;
      background: white;
      border-radius: 12px;
      margin-bottom: 20px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .tab {
      flex: 1;
      max-width: 150px;
      padding: 16px 24px;
      border: none;
      background: transparent;
      cursor: pointer;
      font-weight: 500;
      font-size: 16px;
      color: #666;
      transition: all 0.3s ease;
      position: relative;
    }

    .tab.active {
      color: #005a7a;
      background: #f8fafc;
    }

    .tab.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: #2563eb;
    }

    /* Search Container */
    .search-container {
      background: white;
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .search-input-wrapper {
      margin-bottom: 20px;
    }

    .search-input {
      width: 100%;
      padding: 16px 20px;
      font-size: 16px;
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      background: #f8fafc;
      transition: all 0.3s ease;
    }

    .search-input:focus {
      outline: none;
      border-color: #2563eb;
      background: white;
    }

    .search-input::placeholder {
      color: #94a3b8;
    }

    .filters-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
    }

    /* Multi-Select Dropdown Styles */
    .filter-select {
      position: relative;
      min-width: 160px;
      flex: 1;
    }

    .multiselect-wrapper {
      position: relative;
      width: 100%;
    }

    .multiselect-trigger {
      width: 100%;
      height: 48px;
      padding: 0 40px 0 40px;
      font-size: 14px;
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      background: white;
      color: #334155;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.3s ease;
    }

    .multiselect-trigger:focus {
      outline: none;
      border-color: #2563eb;
    }

    .multiselect-trigger.active {
      background: #00688f !important;
      border-color: #00688f !important;
      color: white !important;
    }

    .multiselect-trigger .selection-text {
      flex: 1;
      text-align: left;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .multiselect-trigger .selection-count {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 8px;
    }

    .filter-select svg {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      color: #64748b;
      pointer-events: none;
      z-index: 1;
    }

    .filter-select.active svg {
      color: white !important;
    }

    .multiselect-trigger::after {
      content: '';
      width: 8px;
      height: 8px;
      border: solid #64748b;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
      transition: transform 0.3s ease;
    }

    .multiselect-trigger.active::after {
      border-color: white !important;
      transform: rotate(-135deg);
    }

    .multiselect-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 2px solid #e2e8f0;
      border-top: none;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1000;
      display: none;
      max-height: 300px;
      overflow: hidden;
    }

    .multiselect-dropdown.show {

      display: block;
      max-height: 325px;
    }

    .multiselect-search {
      padding: 12px;
      border-bottom: 1px solid #e2e8f0;
    }

    .multiselect-search input {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      font-size: 14px;
    }

    .multiselect-options {
      max-height: 200px;
      overflow-y: auto;
    }

    .multiselect-option {
      display: flex;
      align-items: center;
      padding: 10px 12px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .multiselect-option:hover {
      background-color: #f8fafc;
    }

    .multiselect-option input[type="checkbox"] {
      margin-right: 10px;
      width: 16px;
      height: 16px;
    }

    .multiselect-option label {
      flex: 1;
      cursor: pointer;
      font-size: 14px;
      color: #334155;
    }

    .multiselect-actions {
      padding: 12px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      gap: 8px;
    }

    .multiselect-btn {
      flex: 1;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .multiselect-btn.reset {
      background: #f1f5f9;
      color: #64748b;
    }

    .multiselect-btn.reset:hover {
      background: #e2e8f0;
    }

    .multiselect-btn.apply {
      background: #00688f;
      color: white;
    }

    .multiselect-btn.apply:hover {
      background: #005a7a;
    }

    .btn {
      padding: 12px 24px;
      font-size: 14px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
      min-width: 100px;
    }

    .search-btn {
      background: #a32441;
      color: white;
    }

    .search-btn:hover {
      background: #c36;
    }

    .reset-btn {
      background: white;
      border: 2px solid #005a7a;
      color: #005a7a;
      font-weight: 600;
    }

    .reset-btn:hover {
      background: #c36;
    }

    /* Main Content */
    .main-content {
      display: grid;
      grid-template-columns: 400px 1fr;
      gap: 20px;
      height: calc(143vh - 200px);
    }

    /* Job List */
    .job-list {
      background: white;
      border-radius: 12px;
      border: 1px solid rgba(0, 0, 0, 0.05);
      /* very light border */
      box-shadow: 0px 0px 2px 0px rgba(0, 0, 0, 0.15);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }


    .list-header {
      padding: 7px 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .job-count {
      font-weight: 600;
      color: #334155;
    }

    .sort-wrapper {
      display: flex;
      align-items: center;
      font-size: 14px;
      color: #64748b;
      width: 64%;
    }

    .bookmark-icon {
      color: #00688F;
      /* stroke + currentColor fill */
    }

    .sort-select {
      border: none;
      background: none;
      color: #231F20;
      font-weight: 400;
      cursor: pointer;
      font-size: 15px;
      border: 1px solid #c5dde6;
    }

    .job-cards {
      flex: 1;
      overflow-y: auto;
      padding: 0px;
    }

    .job-card {
      display: flex;
      gap: 16px;
      padding: 15px;
      margin: 10px;

      /* Border */
      border: 0.5px solid #808184;
      border-radius: 6px;

      /* Cursor & transition */
      cursor: pointer;
      transition: all 0.3s ease;

      /* Layered box-shadows (stacked, comma-separated) */
      box-shadow:
        0px 3.87px 7.74px 0px rgba(0, 0, 0, 0.01),
        0px 15.48px 15.48px 0px rgba(0, 0, 0, 0.01),
        0px 33.54px 20.64px 0px rgba(0, 0, 0, 0.01),
        0px 60.62px 24.51px 0px rgba(0, 0, 0, 0),
        0px 94.16px 25.8px 0px rgba(0, 0, 0, 0);
    }

    .job-card.selected {
      /* Keep existing selected styles */
    }

    .company-logo {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      object-fit: cover;
      flex-shrink: 0;
      border: 1px solid #443d3d;
    }

    .job-info {
      flex: 1;
      min-width: 0;
    }

    .job-info h3 {
      font-size: 16px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0px;
      margin-top: 0px;

    }

    .company-name {
      font-size: 13px;
      color: #64748b;
      margin-bottom: 1px;
      font-weight: 700;
    }

    .job-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 12px;
    }

    .location {
      display: flex;
      align-items: center;
      gap: 4px;
      color: #64748b;
    }

    .posted-time {
      color: #8b1f37;
      font-weight: 500;
    }

    .subscribe-box {
      background: #8b1f37;
      color: white;
      padding: 17px 24px;
      margin-top: 11px;
    }

    .subscribe-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .subscribe-content h4 {
      font-size: 14px;
      font-weight: 500;
    }

    .get-alerts-btn {
      background: white;
      color: #8b1f37;
      padding: 8px 16px;
      font-size: 12px;
      font-weight: 600;
    }

    /* Job Details */
    .job-details {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 30px;
      overflow-y: auto;
    }

    .job-details-header {
      display: flex;
      gap: 20px;
      margin-bottom: 24px;
      align-items: flex-start;
    }

    .company-logo-large {
      width: 70px;
      height: 70px;
      border: 1px solid #443d3d;
      object-fit: cover;
      flex-shrink: 0;
    }

    .job-title-section {
      flex: 1;
    }

    .job-title-section h2 {
      font-size: 24px;
      color: #1e293b;
      margin-bottom: 0px;
      margin-top: 0px;
      font-weight: 700;
    }

    .job-title-section .company-name {
      font-size: 16px;
      color: #369add;
      margin-bottom: 0px;
      font-weight: 700;
    }

    .location-info {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #64748b;
      font-size: 14px;
    }

    .bookmark-btn {
      background: none;
      border: none;
      color: #64748b;
      cursor: pointer;
      padding: 8px;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .bookmark-btn:hover {
      background: #f1f5f9;
      color: #dc2626;
    }

    .job-tags {
      display: flex;
      gap: 12px;
      margin-bottom: 30px;
      align-items: center;
      flex-wrap: wrap;
    }

    .tag {
      padding: 7px 24px;
      border-radius: 5px;
      font-size: 12px;
      font-weight: 700;
    }

    .tag-blue {
      background: rgba(0, 104, 143, 0.1);
      color: #00688f;
    }

    .tag-gray {
      background: #f1f5f9;
      color: #64748b;
    }

    .salary {
      color: #369add;
      font-weight: 700;
      font-size: 18px;
      margin-left: auto;
    }

    .job-content h3 {
      font-size: 18px;
      font-weight: 600;
      color: #1e293b;
      margin: 24px 0 12px 0;
    }

    .job-content ul {
      padding-left: 20px;
      margin-bottom: 20px;
    }

    .job-content li {
      margin-bottom: 8px;
      color: #475569;
    }

    .job-content p {
      color: #475569;
      line-height: 1.7;
    }

    /* Loader styles */
    .job-details {
      position: relative;
      min-height: 200px;
    }

    .loader-wrapper {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      background: rgba(255, 255, 255, 0.8);
      width: 100%;
      height: 100%;
    }

    .spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #0073e6;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    [type=button]:focus,
    [type=button]:hover,
    [type=submit]:focus,
    [type=submit]:hover,
    button:focus,
    button:hover {
      /* background-color: #f1f5f9; */
    }

    .bookmark-btn.loading svg {
      opacity: 0.3;
    }

    .bookmark-btn.loading::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      width: 24px;
      height: 24px;
      margin: -7px 0 0 -7px;
      border: 2px solid #0066FF;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .main-content {
        grid-template-columns: 350px 1fr;
      }
    }

    @media (max-width: 1024px) {
      .main-content {
        grid-template-columns: 1fr;
        height: auto;
      }

      .job-list {
        order: 1;
        height: 400px;
      }

      .job-details {
        order: 2;
      }

      .filters-row {
        flex-direction: column;
        gap: 12px;
      }

      .filter-select {
        min-width: 100%;
      }
    }

    @media (max-width: 768px) {
      .container {
        padding: 16px;
      }

      .search-container {
        padding: 16px;
      }

      .tabs {
        margin-bottom: 16px;
      }

      .tab {
        padding: 12px 16px;
        font-size: 14px;
      }

      .btn {
        width: 100%;
        padding: 14px 20px;
      }

      .main-content {
        gap: 16px;
      }

      .job-details {
        padding: 20px;
      }

      .job-details-header {
        flex-direction: row;
        gap: 16px;
      }

      .job-title-section {
        text-align: left;
      }

      .bookmark-btn {
        align-self: center;
      }

      .multiselect-dropdown {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 400px;
        max-height: 70vh;
        border-radius: 8px;
        border: 2px solid #e2e8f0;

      }
    }

    @media (max-width: 480px) {
      .search-input {
        padding: 14px 16px;
        font-size: 14px;
      }

      .job-card {
        flex-direction: row;
        gap: 12px;
        margin: 8px;
        padding: 12px;
      }

      .company-logo {
        width: 40px;
        height: 40px;
        align-self: flex-start;
        border: 1px solid #443d3d;
      }

      .job-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }

      .subscribe-content {
        flex-direction: column;
        gap: 12px;
        text-align: center;
      }

      .get-alerts-btn {
        width: 100%;
      }

      .job-tags {
        justify-content: center;
      }

      .salary {
        margin-left: 0;
        margin-top: 8px;
      }

      .multiselect-trigger {
        height: 44px;
        padding: 0 36px 0 36px;
        font-size: 13px;
      }

      .filter-select svg {
        width: 14px;
        height: 14px;
        left: 10px;
      }

      .job-title-section h2 {
        font-size: 20px;
        margin-bottom: 5px;

      }

      .job-title-section .company-name {
        font-size: 12px;
        margin-bottom: 5px;
      }

      .subscribe-box {
        padding: 5px 22px;
      }
    }

    /* New Media Query for max-width: 430px */
    @media (max-width: 430px) {
      .container {
        padding: 12px;
      }

      .tabs {
        flex-direction: row;
        align-items: stretch;
      }

      .tab {
        max-width: none;
        padding: 10px 14px;
        font-size: 13px;
      }

      .search-container {
        padding: 12px;
      }

      .search-input {
        padding: 12px 14px;
        font-size: 13px;
      }

      .btn {
        padding: 12px 16px;
        font-size: 13px;
      }

      .job-card {
        padding: 10px;
        margin: 6px;
        gap: 10px;
      }

      .company-logo {
        width: 36px;
        height: 36px;
      }

      .job-info h3 {
        font-size: 14px;
      }

      .company-name {
        font-size: 12px;
      }

      .job-meta {
        font-size: 11px;
      }

      .job-details {
        padding: 16px;
      }

      .job-details-header {
        gap: 12px;
      }

      .company-logo-large {
        width: 60px;
        height: 60px;
      }

      .job-title-section h2 {
        font-size: 15px;

      }

      .job-title-section .company-name {
        font-size: 11px;
      }

      .location-info {
        font-size: 12px;
        gap: 4px;
      }

      .job-tags {
        gap: 8px;
        margin-bottom: 20px;
      }

      .tag {
        padding: 6px 16px;
        font-size: 11px;
      }

      .salary {
        font-size: 16px;
      }

      .job-content h3 {
        font-size: 16px;
        margin: 20px 0 10px 0;
      }

      .job-content p {
        font-size: 14px;
      }

      .job-content ul {
        padding-left: 16px;
      }

      .job-content li {
        font-size: 14px;
      }

      .multiselect-trigger {
        height: 40px;
        padding: 0 32px 0 32px;
        font-size: 12px;
      }

      .filter-select svg {
        width: 12px;
        height: 12px;
        left: 8px;
      }

      .multiselect-dropdown {
        width: 95%;
        max-height: 60vh;
      }

      .multiselect-search input {
        font-size: 12px;
        padding: 6px 10px;
      }

      .multiselect-option {
        padding: 8px 10px;
      }

      .multiselect-option label {
        font-size: 12px;
      }

      .multiselect-btn {
        font-size: 11px;
        padding: 5px 10px;
      }

      .subscribe-box {
        padding: 10px 16px;
      }

      .subscribe-content h4 {
        font-size: 12px;
      }

      .get-alerts-btn {
        padding: 6px 12px;
        font-size: 11px;
      }

      .location-info {
        justify-content: left;
      }
    }

    /* Reset and base styles */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    html {
      font-size: 16px;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI';
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    button {
      font-family: inherit;
      font-size: inherit;
      line-height: inherit;
      margin: 0;
      overflow: visible;
      text-transform: none;
      -webkit-appearance: button;
    }

    button:focus {
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    }

    img {
      max-width: 100%;
      height: auto;
      display: block;
    }

    @media (prefers-reduced-motion: reduce) {

      *,
      *::before,
      *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
      }
    }

    /* *:focus {
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    } */

    html {
      scroll-behavior: smooth;
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

  <div class="container">
    <!-- Tabs -->
    <div class="tabs">
      <button class="tab active">All Jobs</button>
      <button class="tab">Saved Jobs</button>
    </div>

    <!-- Search Container -->
    <div class="search-container">
      <div class="search-input-wrapper">
        <input type="text" placeholder="Search with keyword," name="search_input" class="search-input">
      </div>
      <div class="filters-row">
        <!-- Job Type Multi-Select -->
        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 6h-2V4c0-1.11-.89-2-2-2H8c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zM8 4h8v2H8V4zm12 15H4V8h2v1c0 .55.45 1 1 1s1-.45 1-1V8h8v1c0 .55.45 1 1 1s1-.45 1-1V8h2v11z" />
          </svg>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="job_listing_type">
              <span class="selection-text">All Job Type</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search job types..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                $job_types = get_terms([
                  'taxonomy'   => 'job_listing_type',
                  'hide_empty' => false,
                ]);

                if (!empty($job_types) && !is_wp_error($job_types)) {
                  foreach ($job_types as $type) {
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="job_type_' . esc_attr($type->slug) . '" value="' . esc_attr($type->slug) . '">';
                    echo '<label for="job_type_' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Location Multi-Select -->
        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
          </svg>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="job_location_category">
              <span class="selection-text">All Location</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search locations..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                $terms = get_terms([
                  'taxonomy'   => 'job_location_category',
                  'hide_empty' => false,
                ]);

                if (!empty($terms) && !is_wp_error($terms)) {
                  foreach ($terms as $term) {
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="location_' . esc_attr($term->slug) . '" value="' . esc_attr($term->slug) . '">';
                    echo '<label for="location_' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Multi-Select -->
        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11.99 18.54l-7.37-5.73L3 14.07l9 7 9-7-1.63-1.27-7.38 5.74zM12 16l7.36-5.73L21 9l-9-7-9 7 1.63 1.27L12 16z" />
          </svg>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="job_listing_category">
              <span class="selection-text">All Category</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search categories..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                $job_categories = get_terms([
                  'taxonomy'   => 'job_listing_category',
                  'hide_empty' => false,
                ]);

                if (!empty($job_categories) && !is_wp_error($job_categories)) {
                  foreach ($job_categories as $category) {
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="category_' . esc_attr($category->slug) . '" value="' . esc_attr($category->slug) . '">';
                    echo '<label for="category_' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Company Multi-Select -->
        <div class="filter-select">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z" />
          </svg>
          <?php
          global $wpdb;

          $company_names = $wpdb->get_col("
        SELECT DISTINCT meta_value 
        FROM $wpdb->postmeta pm
        INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_company_name'
        AND p.post_type = 'job_listing'
        AND p.post_status = 'publish'
        ORDER BY meta_value ASC
        ");
          ?>
          <div class="multiselect-wrapper">
            <div class="multiselect-trigger" data-target="company_name">
              <span class="selection-text">All Company</span>
            </div>
            <div class="multiselect-dropdown">
              <div class="multiselect-search">
                <input type="text" placeholder="Search companies..." class="multiselect-search-input">
              </div>
              <div class="multiselect-options">
                <?php
                if (!empty($company_names)) {
                  foreach ($company_names as $company) {
                    $company_id = sanitize_title($company);
                    echo '<div class="multiselect-option">';
                    echo '<input type="checkbox" id="company_' . esc_attr($company_id) . '" value="' . esc_attr($company) . '">';
                    echo '<label for="company_' . esc_attr($company_id) . '">' . esc_html($company) . '</label>';
                    echo '</div>';
                  }
                }
                ?>
              </div>
              <div class="multiselect-actions">
                <button class="multiselect-btn reset">Reset</button>
                <button class="multiselect-btn apply">Apply</button>
              </div>
            </div>
          </div>
        </div>

        <button class="btn search-btn">Find Job</button>
        <button class="btn reset-btn">Reset All</button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Job List -->
      <div class="job-list">
        <div class="list-header">
          <span class="job-count"><?php echo $jobs->found_posts; ?> Jobs</span>
          <div class="sort-wrapper">
            <span style="margin-right:12px;">Sort by</span>
            <select class="sort-select" style="width:75%;">
              <option value="Newest Post">Newest Post</option>
              <option value="Oldest Post">Oldest Post</option>
            </select>
          </div>
        </div>
        <div class="job-cards">
          <?php
          if ($jobs->have_posts()) :
            $count = 0;
            while ($jobs->have_posts()) :
              $jobs->the_post();
              $company_logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail')
                ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';
              $company_name = get_post_meta(get_the_ID(), '_company_name', true);
              $location = get_post_meta(get_the_ID(), '_job_location', true);
              $posted = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';
          ?>
              <div class="job-card <?php echo $count === 0 ? 'selected' : ''; ?>" data-id="<?php echo get_the_ID(); ?>">
                <img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_attr($company_name); ?>" class="company-logo">
                <div class="job-info">
                  <h3><?php the_title(); ?></h3>
                  <p class="company-name"><?php echo esc_html($company_name); ?></p>
                  <div class="job-meta">
                    <div class="location">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                      </svg>
                      <?php echo esc_html($location); ?>
                    </div>
                    <span class="posted-time"><?php echo esc_html($posted); ?></span>
                  </div>
                </div>
              </div>
          <?php
              $count++;
            endwhile;
            wp_reset_postdata();
          else :
            echo '<p style="text-align: center;">No jobs found.</p>';
          endif;
          ?>
        </div>
        <div class="subscribe-box">
          <div class="subscribe-content">
            <h4>Subscribe for Latest Job Updates</h4>
            <button class="btn get-alerts-btn">Get Alerts</button>
          </div>
        </div>
      </div>

      <!-- Job Details -->
      <div class="job-details" id="job-details">
        <div class="loader" style="display:none;text-align:center;padding:40px;">Loading...</div>
        <?php
        // Show first job details by default
        if ($jobs->have_posts()) :
          $jobs->rewind_posts();
          $jobs->the_post();
          echo my_get_single_job_html(get_the_ID());
          wp_reset_postdata();
        endif;
        ?>
      </div>
    </div>
  </div>

  <script>
    jQuery(document).ready(function($) {

      if (window.myMultiselectLoaded) return; // prevent multiple init
      window.myMultiselectLoaded = true;

      // Multi-select functionality
      let selectedFilters = {
        job_listing_type: [],
        job_location_category: [],
        job_listing_category: [],
        company_name: []
      };

      //alert('a');
      // Toggle dropdown
      $('.multiselect-trigger').on('click', function(e) {
        e.stopPropagation();
        const dropdown = $(this).siblings('.multiselect-dropdown');

        // Close other dropdowns
        $('.multiselect-dropdown').not(dropdown).removeClass('show');
        $('.multiselect-trigger').not(this).removeClass('active');

        // Toggle current dropdown
        dropdown.toggleClass('show');
        $(this).toggleClass('active');
      });

      // Close dropdowns when clicking outside
      $(document).on('click', function() {
        $('.multiselect-dropdown').removeClass('show');
        $('.multiselect-trigger').removeClass('active');
      });

      // Prevent dropdown from closing when clicking inside
      $('.multiselect-dropdown').on('click', function(e) {
        e.stopPropagation();
      });

      // Search within dropdown
      $('.multiselect-search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const options = $(this).closest('.multiselect-dropdown').find('.multiselect-option');

        options.each(function() {
          const text = $(this).find('label').text().toLowerCase();
          $(this).toggle(text.includes(searchTerm));
        });
      });

      // Handle checkbox changes
      $('.multiselect-option input[type="checkbox"]').on('change', function() {
        const filterType = $(this).closest('.multiselect-wrapper').find('.multiselect-trigger').data('target');
        const value = $(this).val();

        if ($(this).is(':checked')) {
          if (!selectedFilters[filterType].includes(value)) {
            selectedFilters[filterType].push(value);
          }
        } else {
          selectedFilters[filterType] = selectedFilters[filterType].filter(item => item !== value);
        }

        updateTriggerText(filterType);
      });

      // Reset button in dropdown
      $('.multiselect-btn.reset').on('click', function() {
        const dropdown = $(this).closest('.multiselect-dropdown');
        const filterType = dropdown.siblings('.multiselect-trigger').data('target');

        // Clear checkboxes
        dropdown.find('input[type="checkbox"]').prop('checked', false);

        // Clear search input in this dropdown
        dropdown.find('.multiselect-search-input').val('');

        // Show all options (in case some were hidden by search)
        dropdown.find('.multiselect-option').show();

        // Clear selected filters for this type
        selectedFilters[filterType] = [];

        // Update trigger text and styling
        updateTriggerText(filterType);
      });

      // Apply button in dropdown
      $('.multiselect-btn.apply').on('click', function() {
        const dropdown = $(this).closest('.multiselect-dropdown');
        dropdown.removeClass('show');
        dropdown.siblings('.multiselect-trigger').removeClass('active');
      });

      // Update trigger text based on selections
      function updateTriggerText(filterType) {
        const trigger = $(`.multiselect-trigger[data-target="${filterType}"]`);
        const selections = selectedFilters[filterType];
        const textElement = trigger.find('.selection-text');
        const countElement = trigger.find('.selection-count');

        // Remove existing count badge
        countElement.remove();

        if (selections.length === 0) {
          let defaultText = 'All';
          switch (filterType) {
            case 'job_listing_type':
              defaultText = 'All Job Type';
              break;
            case 'job_location_category':
              defaultText = 'All Location';
              break;
            case 'job_listing_category':
              defaultText = 'All Category';
              break;
            case 'company_name':
              defaultText = 'All Company';
              break;
          }
          textElement.text(defaultText);
          trigger.removeClass('active');
          trigger.closest('.filter-select').removeClass('active');

          // Reset to default styling
          trigger.css({
            'background-color': 'white',
            'border-color': '#e2e8f0',
            'color': '#334155'
          });
        } else {
          if (selections.length === 1) {
            // Show single selection name
            const checkbox = $(`.multiselect-trigger[data-target="${filterType}"]`)
              .siblings('.multiselect-dropdown')
              .find(`input[value="${selections[0]}"]`);
            const label = checkbox.siblings('label').text();
            textElement.text(label);
          } else {
            // Show count for multiple selections
            textElement.text(`${selections.length} selected`);
          }

          // Add count badge
          trigger.append(`<span class="selection-count">${selections.length}</span>`);
          trigger.addClass('active');
          trigger.closest('.filter-select').addClass('active');

          // Force the active styling
          trigger.css({
            'background-color': '#00688f',
            'border-color': '#00688f',
            'color': 'white'
          });
        }
      }

      // Job card click handler (existing functionality)
      $('.job-cards').on('click', '.job-card', function() {
        var jobId = $(this).data('id');

        $('.job-card').removeClass('selected');
        $(this).addClass('selected');

        $('#job-details').html('<div class="loader-wrapper"><div class="spinner"></div></div>');

        $.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: "POST",
          data: {
            action: "get_job_details",
            job_id: jobId,
          },
          success: function(response) {
            $('#job-details').html(response);
          },
          error: function() {
            $('#job-details').html('<p style="text-align:center;">Error loading job details.</p>');
          }
        });
      });

      // Search button handler (modified for multi-select)
      $('.search-btn').on('click', function() {
        let filters = {
          job_listing_type: selectedFilters.job_listing_type,
          job_location_category: selectedFilters.job_location_category,
          job_listing_category: selectedFilters.job_listing_category,
          company_name: selectedFilters.company_name,
          keyword: $('input[name="search_input"]').val(),
          job_sorting: $('.sort-select').val(),
          action: 'filter_jobs'
        };

        $('.job-cards').html('<div style="text-align:center;padding:20px;">Loading...</div>');
        $('#job-details').html('');

        $.post("<?php echo admin_url('admin-ajax.php'); ?>", filters, function(response) {
          $('.job-cards').html(response);
          $('.job-count').html($('.job-cards .job-card').length + ' Jobs');

          let firstCard = $('.job-cards .job-card').first();
          if (firstCard.length) {
            firstCard.trigger('click');
          } else {
            $('#job-details').html(`<div style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 40px 20px;
                    color: #555;
                    font-family: Arial, sans-serif;
                    text-align: center;
                ">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                    </svg>
                    <h3 style="margin: 15px 0 5px; font-size: 20px; color:#333;">No Jobs Found</h3>
                    <p style="font-size: 14px; max-width: 250px; color:#777;">
                        We could not find any jobs matching your filters. Try changing your search criteria.
                    </p>
                </div>`);
          }
        });
      });

      // Reset All button (modified for multi-select)
      $('.reset-btn').on('click', function(e) {
        e.preventDefault();

        // Clear all multi-select filters
        Object.keys(selectedFilters).forEach(filterType => {
          selectedFilters[filterType] = [];

          // Clear checkboxes
          $(`.multiselect-trigger[data-target="${filterType}"]`)
            .siblings('.multiselect-dropdown')
            .find('input[type="checkbox"]')
            .prop('checked', false);

          // Clear search inputs in dropdowns
          $(`.multiselect-trigger[data-target="${filterType}"]`)
            .siblings('.multiselect-dropdown')
            .find('.multiselect-search-input')
            .val('');

          // Show all options (in case some were hidden by search)
          $(`.multiselect-trigger[data-target="${filterType}"]`)
            .siblings('.multiselect-dropdown')
            .find('.multiselect-option')
            .show();

          // Update trigger text and styling
          updateTriggerText(filterType);
        });

        // Clear main search input
        $('input[name="search_input"]').val('');

        // Reset sort dropdown to default
        $('.sort-select').val('Newest Post');

        // Close any open dropdowns
        $('.multiselect-dropdown').removeClass('show');
        $('.multiselect-trigger').removeClass('active');

        // Trigger search to refresh results
        $('.search-btn').trigger('click');
      });

      // Bookmark functionality (existing)
      $(document).on("click", ".bookmark-btn", function(e) {
        e.preventDefault();

        const button = $(this);
        const jobId = button.data("job-id");

        button.addClass("loading");

        $.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: "POST",
          dataType: "json",
          data: {
            action: "toggle_save_job",
            job_id: jobId
          },
          success: function(response) {
            button.removeClass("loading");
            if (response.success) {
              if (response.data.saved) {
                button.addClass("saved");
                button.find("svg").attr("fill", "currentColor");
              } else {
                button.removeClass("saved");
                button.find("svg").attr("fill", "none");
              }
            } else {
              alert(response.data.message);
            }
          },
          error: function() {
            button.removeClass("loading");
            alert("Something went wrong. Please try again.");
          }
        });
      });

      // Sort functionality (existing)
      $('.sort-select').on('change', function() {
        $('.search-btn').trigger('click');
      });
    });
  </script>

<?php
  return ob_get_clean();
}
add_shortcode('search_job', 'my_search_job');

// Helper function to generate job details HTML (unchanged)
function my_get_single_job_html($post_id)
{
  ob_start();
  $company_logo = get_the_post_thumbnail_url($post_id, 'medium') ?: get_stylesheet_directory_uri() . '/images/noimage.jpg';
  $company_name = get_post_meta($post_id, '_company_name', true);
  $location = get_post_meta($post_id, '_job_location', true);
  $salary = get_post_meta($post_id, '_job_salary', true);
  $job_salary_currency   = get_post_meta($post_id, '_job_salary_currency', true);
  $job_salary_unit   = get_post_meta($post_id, '_job_salary_unit', true);

  $job_type_terms = wp_get_post_terms($post_id, 'job_listing_type');
  $job_types = ! empty($job_type_terms) ? wp_list_pluck($job_type_terms, 'name') : array();
?>

  <div class="job-details-header">
    <img src="<?php echo esc_url($company_logo); ?>" alt="<?php echo esc_attr($company_name); ?>" class="company-logo-large">
    <div class="job-title-section">
      <h2><?php echo get_the_title($post_id); ?></h2>
      <p class="company-name"><?php echo esc_html($company_name); ?></p>
      <div class="location-info">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
        </svg>
        <?php echo esc_html($location); ?>
      </div>
    </div>
    <?php
    $user_id = get_current_user_id();
    $saved_jobs = is_user_logged_in() ? get_user_meta($user_id, 'saved_jobs', true) : [];
    $is_saved = is_array($saved_jobs) && in_array($post_id, $saved_jobs);
    ?>
    <button class="bookmark-btn <?php echo $is_saved ? 'saved' : ''; ?>" data-job-id="<?php echo $post_id; ?>">
      <svg
        class="bookmark-icon"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>"
        stroke="currentColor"
        stroke-width="2"
        xmlns="http://www.w3.org/2000/svg">
        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
      </svg>
    </button>
  </div>
  <div class="job-tags">
    <?php foreach ($job_types as $type) : ?>
      <span class="tag tag-blue"><?php echo esc_html($type); ?></span>
    <?php endforeach; ?>
    <?php if ($salary) : ?>
      <span class="salary"><?php echo esc_html($salary); ?> <?php echo $job_salary_currency; ?>/ <?php echo ucfirst(strtolower($job_salary_unit)); ?></span>
    <?php endif; ?>
  </div>
  <div class="job-content">
    <?php echo apply_filters('the_content', get_post_field('post_content', $post_id)); ?>
  </div>
  <div class="job-content">
    <h4 style="font-weight:700;">How to Apply</h4>
    <?php echo get_field('how_to_apply', $post_id); ?>
  </div>

<?php
  return ob_get_clean();
}

// AJAX callback (unchanged)
add_action('wp_ajax_get_job_details', 'my_ajax_get_job_details');
add_action('wp_ajax_nopriv_get_job_details', 'my_ajax_get_job_details');

function my_ajax_get_job_details()
{
  if (!isset($_POST['job_id'])) {
    wp_send_json_error('Missing job_id');
  }
  $job_id = intval($_POST['job_id']);
  echo my_get_single_job_html($job_id);
  wp_die();
}

// Bookmark functionality (unchanged)
add_action('wp_ajax_toggle_save_job', 'toggle_save_job');
add_action('wp_ajax_nopriv_toggle_save_job', 'toggle_save_job');

function toggle_save_job()
{
  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Please log in to save jobs.']);
  }

  $user_id = get_current_user_id();
  $job_id  = intval($_POST['job_id']);

  if (!$job_id || get_post_type($job_id) !== 'job_listing') {
    wp_send_json_error(['message' => 'Invalid job ID']);
  }

  $saved_jobs = get_user_meta($user_id, 'saved_jobs', true);
  if (!is_array($saved_jobs)) {
    $saved_jobs = [];
  }

  if (in_array($job_id, $saved_jobs)) {
    $saved_jobs = array_diff($saved_jobs, [$job_id]);
    update_user_meta($user_id, 'saved_jobs', $saved_jobs);
    wp_send_json_success(['saved' => false, 'message' => 'Job removed from saved list']);
  } else {
    $saved_jobs[] = $job_id;
    $saved_jobs = array_unique($saved_jobs);
    update_user_meta($user_id, 'saved_jobs', $saved_jobs);
    wp_send_json_success(['saved' => true, 'message' => 'Job saved successfully']);
  }
}

// Modified AJAX filter function to handle arrays
add_action('wp_ajax_filter_jobs', 'my_ajax_filter_jobs');
add_action('wp_ajax_nopriv_filter_jobs', 'my_ajax_filter_jobs');

function my_ajax_filter_jobs()
{
  $meta_query = [];
  $tax_query = [];

  // Handle company names (can be array)
  if (!empty($_POST['company_name'])) {
    $company_names = is_array($_POST['company_name']) ? $_POST['company_name'] : [$_POST['company_name']];
    if (count($company_names) > 1) {
      $company_meta_query = ['relation' => 'OR'];
      foreach ($company_names as $company) {
        $company_meta_query[] = [
          'key'   => '_company_name',
          'value' => sanitize_text_field($company),
        ];
      }
      $meta_query[] = $company_meta_query;
    } else {
      $meta_query[] = [
        'key'   => '_company_name',
        'value' => sanitize_text_field($company_names[0]),
      ];
    }
  }

  // Handle taxonomies (can be arrays)
  $taxonomies = ['job_listing_type', 'job_location_category', 'job_listing_category'];

  foreach ($taxonomies as $taxonomy) {
    if (!empty($_POST[$taxonomy])) {
      $terms = is_array($_POST[$taxonomy]) ? $_POST[$taxonomy] : [$_POST[$taxonomy]];
      $terms = array_map('sanitize_text_field', $terms);

      $tax_query[] = [
        'taxonomy' => $taxonomy,
        'field'    => 'slug',
        'terms'    => $terms,
        'operator' => 'IN'
      ];
    }
  }

  // Sorting Logic
  $order = 'DESC';
  if (!empty($_POST['job_sorting']) && $_POST['job_sorting'] === 'Oldest Post') {
    $order = 'ASC';
  }

  $args = [
    'post_type'      => 'job_listing',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    's'              => sanitize_text_field($_POST['keyword']),
    'meta_query'     => $meta_query,
    'tax_query'      => $tax_query,
    'orderby'        => 'date',
    'order'          => $order,
  ];

  $jobs = new WP_Query($args);

  if ($jobs->have_posts()) {
    while ($jobs->have_posts()) {
      $jobs->the_post();
      $company_logo = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: 'https://via.placeholder.com/50';
      $company_name = get_post_meta(get_the_ID(), '_company_name', true);
      $location = get_post_meta(get_the_ID(), '_job_location', true);
      $posted = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago';

      echo '<div class="job-card" data-id="' . get_the_ID() . '">';
      echo '<img src="' . esc_url($company_logo) . '" class="company-logo">';
      echo '<div class="job-info">';
      echo '<h3>' . esc_html(get_the_title()) . '</h3>';
      echo '<p class="company-name">' . esc_html($company_name) . '</p>';
      echo '<div class="job-meta">';
      echo '<div class="location">' . esc_html($location) . '</div>';
      echo '<span class="posted-time">' . esc_html($posted) . '</span>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo '<p style="text-align: center;">No jobs found.</p>';
  }

  wp_reset_postdata();
  wp_die();
}
?>