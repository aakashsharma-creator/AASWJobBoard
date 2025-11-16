<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_change_password()
{
    $current_user = wp_get_current_user();
    $errors = [];
    $success_message = '';

    if (!is_user_logged_in()) {
        return '<p style="color:red; text-align:center;">You must be logged in to change your password.</p>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['my_change_password_nonce'])) {
        if (!wp_verify_nonce($_POST['my_change_password_nonce'], 'my_change_password_action')) {
            $errors['general'] = 'Security check failed. Please try again.';
        } else {
            $current_password = sanitize_text_field($_POST['current_password'] ?? '');
            $new_password     = sanitize_text_field($_POST['new_password'] ?? '');
            $confirm_password = sanitize_text_field($_POST['confirm_password'] ?? '');

            // Validation
            if (empty($current_password)) {
                $errors['current_password'] = 'Please enter your current password.';
            } elseif (!wp_check_password($current_password, $current_user->user_pass, $current_user->ID)) {
                $errors['current_password'] = 'Current password is incorrect.';
            }

            if (empty($new_password)) {
                $errors['new_password'] = 'Please enter a new password.';
            } elseif (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
                $errors['new_password'] = 'Password must be at least 8 characters long, include uppercase, lowercase, number, and special character.';
            } elseif ($new_password === $current_password) {
                $errors['new_password'] = 'New password cannot be the same as your current password.';
            }

            if (empty($confirm_password)) {
                $errors['confirm_password'] = 'Please confirm your new password.';
            } elseif ($new_password !== $confirm_password) {
                $errors['confirm_password'] = 'New password and confirm password do not match.';
            }

            if (empty($errors)) {
                wp_set_password($new_password, $current_user->ID);
                wp_set_current_user($current_user->ID);
                wp_set_auth_cookie($current_user->ID);
                do_action('wp_login', $current_user->user_login, $current_user);

                $success_message = 'Password updated successfully.';
            }
        }
    }

    ob_start();
?>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f8fbff;
            padding: 20px;
        }

        .container {
            max-width: 100%;
            padding: 0px 20px;
            margin: 40px auto;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 24px;
            padding: 28px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            font-size: 15px;
            border: 1px solid #e5e7eb;
            background-color: #f5fbf9;
            border-radius: 6px;
            outline: none;
            transition: all 0.2s;
            position: relative;
            display: flex;
            align-items: center;
        }

        input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
            background-color: #fff;
        }

        .password-group {
            position: relative;
			height:78px;
        }

        .eye-icon {
            position: absolute;
            right: 10px;
            top: 63%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .button-container {
            text-align: right;
        }

        .btn-submit {
            background-color: #a0283b;
            border: none;
            color: #fff;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background-color: #881f31;
        }

        .btn-submit:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
            }

            .card {
                padding: 20px;
            }

            .btn-submit {
                width: 100%;
            }
        }

        .dashboard-container {
            max-width: 100% !important;
        }

        /* Inline error + success messages */
        .error-message {
            color: red;
            font-size: 13px;
            margin-top: 6px;
            text-align: left;
			position:absolute;
        }

        .success-message {
            color: #0a6c8c;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>

    <div class="container">
        <div class="card">
            <?php if ($success_message) : ?>
                <p class="success-message"><?php echo esc_html($success_message); ?></p>
            <?php endif; ?>

            <?php if (!empty($errors['general'])) : ?>
                <p style="color:red; text-align:center;"><?php echo esc_html($errors['general']); ?></p>
            <?php endif; ?>

            <form id="change-password-form" method="post" novalidate>
                <?php wp_nonce_field('my_change_password_action', 'my_change_password_nonce'); ?>

                <div class="password-group">
                    <label for="currentPassword">Current Password *</label>
                    <input type="password" id="currentPassword" name="current_password">
                    <span class="eye-icon" onclick="togglePassword('currentPassword', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 
                            0-5-2.24-5-5s2.24-5 5-5 
                            5 2.24 5 5-2.24 5-5 
                            5zm0-8c-1.66 0-3 1.34-3 
                            3s1.34 3 3 3 3-1.34 
                            3-3-1.34-3-3-3z" />
                        </svg>
                    </span>
                   
                </div>
				 <?php if (!empty($errors['current_password'])) : ?>
                        <p class="error-message"><?php echo esc_html($errors['current_password']); ?></p>
                    <?php endif; ?>

                <div class="password-group">
                    <label for="newPassword">New Password *</label>
                    <input type="password" id="newPassword" name="new_password">
                    <span class="eye-icon" onclick="togglePassword('newPassword', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 
                            1 12c1.73 4.39 6 7.5 
                            11 7.5s9.27-3.11 
                            11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 
                            17c-2.76 0-5-2.24-5-5s2.24-5 
                            5-5 5 2.24 5 5-2.24 
                            5-5 5zm0-8c-1.66 0-3 
                            1.34-3 3s1.34 3 3 3 
                            3-1.34 3-3-1.34-3-3-3z" />
                        </svg>
                    </span>
                   
                </div>
				 <?php if (!empty($errors['new_password'])) : ?>
                        <p class="error-message"><?php echo esc_html($errors['new_password']); ?></p>
                    <?php endif; ?>

                <div class="password-group">
                    <label for="confirmPassword">Confirm Password *</label>
                    <input type="password" id="confirmPassword" name="confirm_password">
                    <span class="eye-icon" onclick="togglePassword('confirmPassword', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 
                            1 12c1.73 4.39 6 7.5 
                            11 7.5s9.27-3.11 
                            11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 
                            17c-2.76 0-5-2.24-5-5s2.24-5 
                            5-5 5 2.24 5 5-2.24 
                            5-5 5zm0-8c-1.66 0-3 
                            1.34-3 3s1.34 3 3 3 
                            3-1.34 3-3-1.34-3-3-3z" />
                        </svg>
                    </span>
                   
                </div>
				 <?php if (!empty($errors['confirm_password'])) : ?>
                        <p class="error-message"><?php echo esc_html($errors['confirm_password']); ?></p>
                    <?php endif; ?>

                <div class="button-container">
                    <button type="submit" class="btn-submit" id="submitButton">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(id, icon) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }

        document.getElementById('change-password-form').addEventListener('submit', function(e) {
            let valid = true;
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            const current = document.getElementById('currentPassword');
            const newPass = document.getElementById('newPassword');
            const confirm = document.getElementById('confirmPassword');

            if (current.value.trim() === '') {
                showError(current, 'Please enter your current password.');
                valid = false;
            }

            if (newPass.value.trim() === '') {
                showError(newPass, 'Please enter a new password.');
                valid = false;
            } else if (newPass.value.length < 8 || !/[A-Z]/.test(newPass.value) || !/[0-9]/.test(newPass.value)) {
                showError(newPass, 'Password must be at least 8 characters long, include uppercase, lowercase, number, and special character.');
                valid = false;
            }

            if (confirm.value.trim() === '') {
                showError(confirm, 'Please confirm your new password.');
                valid = false;
            } else if (newPass.value !== confirm.value) {
                showError(confirm, 'Passwords do not match.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });

        function showError(input, message) {
            const error = document.createElement('p');
            error.className = 'error-message';
            error.textContent = message;
            input.insertAdjacentElement('afterend', error);
        }
    </script>
<?php
    return ob_get_clean();
}

add_shortcode('change_password', 'my_change_password');
?>
