<?php
// Fetch blogs of the network
$blogs = get_sites();

// Get current environment
function get_current_environment() {
    $current_url = home_url( add_query_arg( NULL, NULL ) );

    if (strpos($current_url, 'gefran.kinsta.cloud') !== false) {
        return 'prd';
    } elseif (strpos($current_url, 'gefranstg.kinsta.cloud') !== false) {
        return 'stg';
		} else {
        return '';
    }
}
$current_environment = get_current_environment();


// Function to get saved webhook values for a specific blog
function get_blog_webhooks($blog_id)
{
    $webhooks_settings_prd = get_option('gatsby_webhooks_settings', array());

    $webhooks_settings_stg = [
        1 => ['site_id_for_webhooks' => '65252ea1a345e3089644e931'],
        2 => ['site_id_for_webhooks' => '65252eb4771d2a11bef847a2'],
        3 => ['site_id_for_webhooks' => '65267014c09e632de4d3e274'],
        4 => ['site_id_for_webhooks' => '65250f58f6d81b0083e58048'],
        5 => ['site_id_for_webhooks' => '65252e38d3f9771233a9bb43'],
        6 => ['site_id_for_webhooks' => '65252e6ece4de51189e37abe'],
        7 => ['site_id_for_webhooks' => '65267075a3518a33606655ea'],
    ];

    $site_id_for_webhooks_prd = isset($webhooks_settings_prd[$blog_id]['site_id_for_webhooks']) ? $webhooks_settings_prd[$blog_id]['site_id_for_webhooks'] : '';
    $site_id_for_webhooks_stg = isset($webhooks_settings_stg[$blog_id]['site_id_for_webhooks']) ? $webhooks_settings_stg[$blog_id]['site_id_for_webhooks'] : '';
    $site_id_for_webhooks = get_current_environment() === 'prd' ? $site_id_for_webhooks_prd : $site_id_for_webhooks_stg;

    return array('site_id_for_webhooks' => $site_id_for_webhooks);
}


// Trigger the webhook for a specific blog
function trigger_gatsby_webhook($webhook_url)
{
    if (empty($webhook_url)) {
        echo 'Webhook URL is empty. Unable to trigger the webhook.';
        return;
    }

    $response = wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
          'Content-Type' => 'application/json; charset=utf-8',
        ),
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    } else {
        echo 'Webhook triggered successfully for URL: ' . $webhook_url;
    }
}

// Check if a specific blog is selected for build or clear cache and trigger the webhook accordingly
if (isset($_POST['build_frontend'])) {
    foreach ($blogs as $blog) {
        $blog_id = $blog->blog_id;
        $webhooks = get_blog_webhooks($blog_id);
        $action = isset($_POST['action_' . $blog_id]) ? $_POST['action_' . $blog_id] : '';

        $site_id_for_webhooks = $webhooks['site_id_for_webhooks'];

        // PRODUCTION HOOKS
        if($current_environment === 'prd'){
          if ($action === 'build') {
              $webhook_url = 'https://api.netlify.com/build_hooks/' . $site_id_for_webhooks . '?trigger_title=WP+triggered+PRD';
              trigger_gatsby_webhook($webhook_url);
              echo '<p>Blog ID: ' . $blog_id . ', Action: ' . $action . ', Webhook URL: ' . $webhook_url . '</p>';
          } elseif ($action === 'clear_cache') {
              $webhook_url = 'https://api.netlify.com/build_hooks/' . $site_id_for_webhooks . '?trigger_title=WP+triggered+PRD+CLEAR+CACHE&clear_cache=true';
              trigger_gatsby_webhook($webhook_url);
              echo '<p>Blog ID: ' . $blog_id . ', Action: ' . $action . ', Webhook URL: ' . $webhook_url . '</p>';
          }
        }

        // STAGING HOOKS
        else {
          if ($action === 'build') {
              $webhook_url = 'https://api.netlify.com/build_hooks/' . $site_id_for_webhooks . '?trigger_branch=stg&trigger_title=WP+triggered+STG';
              trigger_gatsby_webhook($webhook_url);
              echo '<p>Blog ID: ' . $blog_id . ', Action: ' . $action . ', Webhook URL: ' . $webhook_url . '</p>';
          } elseif ($action === 'clear_cache') {
              $webhook_url = 'https://api.netlify.com/build_hooks/' . $site_id_for_webhooks . '?trigger_branch=stg&trigger_title=WP+triggered+STG+CLEAR+CACHE&clear_cache=true';
              trigger_gatsby_webhook($webhook_url);
              echo '<p>Blog ID: ' . $blog_id . ', Action: ' . $action . ', Webhook URL: ' . $webhook_url . '</p>';
          }
        }

    }
}
?>

<script>
    function bulkSelect(action) {
        var checkboxes = document.querySelectorAll('[name^="action_"]');
        checkboxes.forEach(function(checkbox) {
            if (checkbox.value === action) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
    }
</script>

<form method="post" style="padding-top: 20px;">
    <label>
        <input type="radio" name="bulk_selection" value="build" onclick="bulkSelect('build')" <?php if (isset($_POST['bulk_selection']) && $_POST['bulk_selection'] === 'build') echo 'checked'; ?>>
        Bulk Select Build
    </label>
    <label>
        <input type="radio" name="bulk_selection" value="clear_cache" onclick="bulkSelect('clear_cache')" <?php if (isset($_POST['bulk_selection']) && $_POST['bulk_selection'] === 'clear_cache') echo 'checked'; ?>>
        Bulk Select Clear Cache and Build
    </label>

    <?php foreach ($blogs as $blog) :

      $webhooks_settings = get_option('gatsby_webhooks_settings', array());
      $site_id_for_webhooks = isset($webhooks_settings[$blog->blog_id]['site_id_for_webhooks']) ? $webhooks_settings[$blog->blog_id]['site_id_for_webhooks'] : '';

      $blog_language = get_blog_option($blog->blog_id, 'WPLANG');
      ?>
      <h2><?= get_blog_option($blog->blog_id, 'blogname') . ' ' . $blog_language ?></h2>
        <label>
            <input type="radio" name="action_<?php echo $blog->blog_id; ?>" data-blog-id="<?php echo $blog->blog_id; ?>" value="build" <?php echo (get_blog_webhooks($blog->blog_id)['bulk_action'] ?? '' === 'build' ? 'checked' : ''); ?>>
            Build
        </label>
        <label>
            <input type="radio" name="action_<?php echo $blog->blog_id; ?>" data-blog-id="<?php echo $blog->blog_id; ?>" value="clear_cache" <?php echo (get_blog_webhooks($blog->blog_id)['bulk_action'] ?? '' === 'clear_cache' ? 'checked' : ''); ?>>
            Clear Cache and Build
        </label>
    <?php endforeach; ?>
    <p>
        <input type="submit" class="button-primary" name="build_frontend" value="Build Frontend">
    </p>
</form>
