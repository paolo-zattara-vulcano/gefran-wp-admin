<?php

abstract class AlgoliaIndex
{
    const products = "Products";
    const applications = 'Applications';
    const case = 'Case_Histories';
    const news = 'NewsEvents';
}

final class Algolia
{
    /**
     * Instance of the main Algolia class
     *
     * @var Algolia $instance
     */
    private static $instance;

    public $client;

    /*
    public $algoliaIndexName;

    public $algoliaApplicationsIndexName;

    public $algoliaCaseIndexName;

    public $algoliaNewsIndexName;
*/
    /**
     * A dummy constructor to ensure Algolia is only setup once.
     * @return  void
     */
    public function __construct()
    {
        // Do nothing.
    }

    /**
     * Returns instance of the main Algolia class
     *
     * @return Algolia
     * @throws Exception
     */
    public static function instance($options)
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Algolia)) {
            self::$instance = new Algolia();
            if (!empty($options)) {
                self::$instance->initialize($options);
            }
        }
        return self::$instance;
    }


    /**
     * Sets up the Algolia plugin.
     *
     * @return  void
     */
    public function initialize($options)
    {
        try {
            /*$this->algoliaIndexName = AlgoliaIndex::products;
            $this->algoliaApplicationsIndexName = AlgoliaIndex::applications;
            $this->algoliaCaseIndexName = AlgoliaIndex::case;
            $this->algoliaNewsIndexName = AlgoliaIndex::news;
            */
            //error_log("BlogId:" . get_current_blog_id() . " initialize appId:" . $options['app_id'] . " apiKey:" . $options['api_key']);
            //$this->client = \Algolia\AlgoliaSearch\SearchClient::create("F2V14JT8I8", "ec1c1c1115c4fa6c43118c053a9c53a5");
            $this->client = \Algolia\AlgoliaSearch\SearchClient::create($options['app_id'], $options['api_key']);
        } catch (\Exception $e) {
            error_log('Error creating algolia index ' . $e->getMessage());
            add_action('admin_init', 'algolia_show_wrong_credentials');
        }
    }

    public function getIndex($locale, string $indexName)
    {
        try {
            return $this->client->initIndex($indexName . '_' . $locale . '_dev');
        } catch (\Exception $e) {
            error_log('Error getting algolia index ' . $e->getMessage());
            add_action('admin_init', 'algolia_show_wrong_credentials');
        }
    }
}

function algolia_show_wrong_credentials()
{
    /**
     * For users with lower capabilities, don't show the notice
     */
    if (!current_user_can('activate_plugins')) {
        return;
    }

    add_action(
        'admin_notices',
        function () {
?>
        <div class="error notice">
            <p><?php esc_html_e('Algolia Plugin: Configure valid AppId and ApiKey', 'algolia-custom-integration'); ?> </p>
        </div>
    <?php
        }
    );
}

function algolia_show_admin_notice()
{
    /**
     * For users with lower capabilities, don't show the notice
     */
    if (!current_user_can('activate_plugins')) {
        return;
    }

    add_action(
        'admin_notices',
        function () {
    ?>
        <div class="error notice">
            <p><?php esc_html_e('Algolia Plugin: AppId and ApiKey must be configured.', 'algolia-custom-integration'); ?> </p>
        </div>
<?php
        }
    );
}

if (!function_exists('algolia_init')) {
    function algolia_init()
    {
        $options = get_option('algolia_custom_integration_plugin_options');
        if (empty($options)) {
            // Show the admin notice
            add_action('admin_init', 'algolia_show_admin_notice');

            // Bail
            return;
        }

        return Algolia::instance($options);
    }
}

add_action(
    'plugins_loaded',
    function () {
        algolia_init();
    }
);
