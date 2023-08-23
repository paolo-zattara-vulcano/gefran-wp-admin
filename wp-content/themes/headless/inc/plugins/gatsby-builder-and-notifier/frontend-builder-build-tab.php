<?php
// Fetch blogs of the network
$blogs = get_sites();

// Function to get saved webhook values for a specific blog
function get_blog_webhooks($blog_id)
{
  $webhooks_settings = get_option('gatsby_webhooks_settings', array());

    $site_id_for_webhooks = isset($webhooks_settings[$blog_id]['site_id_for_webhooks']) ? $webhooks_settings[$blog_id]['site_id_for_webhooks'] : '';
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
          'x-gatsby-cache' => 'false',
          'x-gatsby-cloud-data-source' => 'gatsby-source-wordpress'
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

        if ($action === 'build') {
            $webhook_url = 'https://webhook.gatsbyjs.com/hooks/data_source/publish/' . $site_id_for_webhooks;
            trigger_gatsby_webhook($webhook_url);
            echo '<p>Blog ID: ' . $blog_id . ', Action: ' . $action . ', Webhook URL: ' . $webhook_url . '</p>';
        } elseif ($action === 'clear_cache') {
            $webhook_url = 'https://webhook.gatsbyjs.com/hooks/builds/trigger/' . $site_id_for_webhooks;
            trigger_gatsby_webhook($webhook_url);
            echo '<p>Blog ID: ' . $blog_id . ', Action: ' . $action . ', Webhook URL: ' . $webhook_url . '</p>';
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
            <input type="radio" name="action_<?php echo $blog->blog_id; ?>" data-blog-id="<?php echo $blog->blog_id; ?>" value="build" <?php echo (get_blog_webhooks($blog->blog_id)['bulk_action'] === 'build' ? 'checked' : ''); ?>>
            Build
        </label>
        <label>
            <input type="radio" name="action_<?php echo $blog->blog_id; ?>" data-blog-id="<?php echo $blog->blog_id; ?>" value="clear_cache" <?php echo (get_blog_webhooks($blog->blog_id)['bulk_action'] === 'clear_cache' ? 'checked' : ''); ?>>
            Clear Cache and Build
        </label>
    <?php endforeach; ?>
    <p>
        <input type="submit" class="button-primary" name="build_frontend" value="Build Frontend">
    </p>
</form>
