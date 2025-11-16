<?php
if ( ! defined('ABSPATH') ) exit;
$users = get_users(['role' => 'candidate']);
?>
<div class="wrap udp-admin-page">
    <h1 class="udp-page-title">Locum List</h1>

    <div class="udp-card">
        <div class="udp-card-header">
            <h2>All Locums</h2>
            <p class="udp-subtitle">Below is a list of all users with the role <strong>Locum</strong> (Candidate).</p>
        </div>

        <div class="udp-table-wrapper">
            <table class="udp-table display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $key => $user): ?>
                    <tr>
                        <td><?php echo esc_html($key + 1); ?></td>
                        <td><?php echo esc_html($user->user_login); ?></td>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html(date('d M Y', strtotime($user->user_registered))); ?></td>
                        <td>
                            <button class="button button-primary udp-view-user" data-user="<?php echo esc_attr($user->ID); ?>">
                                View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="udp-modal" class="udp-modal">
        <div class="udp-modal-inner">
            <span class="udp-close">&times;</span>
            <div id="udp-modal-content"></div>
        </div>
    </div>
</div>
