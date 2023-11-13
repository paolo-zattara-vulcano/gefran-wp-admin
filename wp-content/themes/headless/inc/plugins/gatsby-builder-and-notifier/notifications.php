<?php

// GATSBY ADMIN PAGE
// Add a new menu page under the Gatsby Menu for displaying notifications
add_action('admin_menu', 'register_gatsby_notifications_page');
function register_gatsby_notifications_page() {
    add_menu_page(
        'Gatsby Notifications',
        'Gatsby Notifications',
        'manage_options',
        'gatsby_notifications',
        'display_gatsby_notifications_page'
    );
}

function display_gatsby_notifications_page() {
  ?>
  <div class="wrap">
      <h1>Gatsby Builds Monitor</h1>
      <div id="gatsby-notifications-container"></div>
  </div>
  <?php
}

add_action('admin_post_nopriv_gatsby_build_started', 'gatsby_build_started_callback');
add_action('admin_post_nopriv_gatsby_build_success', 'gatsby_build_success_callback');
add_action('admin_post_nopriv_gatsby_locked', 'gatsby_locked_callback');
add_action('admin_post_nopriv_gatsby_build_fail', 'gatsby_build_fail_callback');

function gatsby_build_started_callback() {
    process_gatsby_notification('notice-started');
}

function gatsby_build_success_callback() {
    process_gatsby_notification('notice-success');
}

function gatsby_locked_callback() {
    process_gatsby_notification('notice-warning');
}

function gatsby_build_fail_callback() {
    process_gatsby_notification('notice-error');
}

function process_gatsby_notification($notice_class) {
    $request = file_get_contents('php://input'); // get data from webhook

    if ($request) {
        // Save the request data in a transient
        $data = array('class' => $notice_class, 'message' => $request);
        set_transient('gatsby_notification', $data, 60 * 30); // transient will expire after 30 minutes
    }
}

add_action('admin_notices', 'gatsby_admin_notice');

function gatsby_admin_notice() {
    // Check if the current page matches the desired notifications page
    if (isset($_GET['page']) && $_GET['page'] === 'gatsby_notifications') {
        // Check if the transient is set
        if ($notification = get_transient('gatsby_notification')) {
            ?>
            <div class="notice <?php echo $notification['class']; ?> is-dismissible">
                <p><?php echo $notification['message']; ?></p>
            </div>
            <?php
            // Delete the transient so it doesn't keep showing up
            delete_transient('gatsby_notification');
        }
    }
}

add_action('admin_footer', 'gatsby_admin_notice_script');
function gatsby_admin_notice_script() {
    ?>
    <script>
        jQuery(document).ready(function ($) {
            console.log('Fet');
            // Check if we are on the gatsby_notifications page
            var currentPage = '<?php echo esc_js(admin_url('admin.php?page=gatsby_notifications')); ?>';

            if (window.location.href === currentPage) {
                // If we are on the gatsby_notifications page, start polling for notifications
                setInterval(function () {
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action: 'check_gatsby_notification'
                        },
                        success: function (response) {
                            if (response) {
                                // If there's a new notification, display it
                                var notification = JSON.parse(response);

                                // Convert the notification.message JSON to an object
                                var messageObj = JSON.parse(notification.message);

                                // console.log('messageObj', messageObj);


                                // Create an HTML template to display the notification details

                                // <p>Deploy Preview URL: <a href="${messageObj.deployPreviewUrl}">${messageObj.deployPreviewUrl}</a></p>
                                // <p>Build Logs URL: <a href="${messageObj.logsUrl}">${messageObj.logsUrl}</a></p>
                                // <p>Workspace Name: ${messageObj.workspaceName}</p>

                                const buildDurationInSeconds = messageObj.deploy_time;
                                const minutes = Math.floor(buildDurationInSeconds / 60);
                                const seconds = buildDurationInSeconds % 60;
                                const buildDurationInMinutesAndSeconds = `${minutes} minutes ${seconds} seconds`;

                                var notificationHTML = `
                                    <div class="notice ${notification.class} is-dismissible">
                                        <h3>Site Name: ${messageObj.name}</h3>
                                        <h5 style="margin-top:0; margin-bottom:0;">${messageObj.title}</h5>
                                        <p style="margin-top:0">
                                          Build Status: ${messageObj.state}<br/>
                                          ${messageObj.error_message ? `Build Error: ${messageObj.error_message}<br/>` : ''}
                                          Build Branch: <strong>${messageObj.branch}</strong><br/>
                                          Build ID: ${messageObj.build_id}<br/>
                                          ${buildDurationInSeconds ? `Build Duration: ${buildDurationInMinutesAndSeconds}<br/>` : ''}
                                          Build Link: <a href="${messageObj.url}" style="display: inline-block" target="_blank">${messageObj.url}</a>
                                        </p>
                                        <button type="button" class="notice-dismiss">
                                          <span class="screen-reader-text">Dismiss this notice.</span>
                                        </button>
                                    </div>
                                `;

                                // Append the notification HTML to the container
                                $('#gatsby-notifications-container').after(notificationHTML);
                            }
                        }
                    });
                }, 5000);
            }
        });
    </script>
    <?php
}



add_action('wp_ajax_check_gatsby_notification', 'check_gatsby_notification');

function check_gatsby_notification() {
    // Check if the transient is set
    if ($notification = get_transient('gatsby_notification')) {
        // If there's a new notification, return it and delete the transient
        echo json_encode($notification);
        delete_transient('gatsby_notification');
    }
    wp_die();
}
