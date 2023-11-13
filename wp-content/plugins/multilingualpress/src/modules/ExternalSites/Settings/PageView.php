<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Settings;

use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;

use function Inpsyde\MultilingualPress\printNonceField;

class PageView implements SettingsPageView
{
    /**
     * @var Nonce
     */
    protected $nonce;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TableFormView
     */
    protected $table;

    public function __construct(Nonce $nonce, Request $request, TableFormView $table)
    {
        $this->nonce = $nonce;
        $this->request = $request;
        $this->table = $table;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()) ?></h1>
            <?php settings_errors() ?>
            <?php $this->renderForm() ?>
        </div>
        <?php
    }

    /**
     * Renders the form.
     *
     * @return void
     */
    protected function renderForm(): void
    {
        ?>
        <form class="mlp-external-sites-form"
              action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
              method="post"
        >
            <?php $this->table->render() ?>
            <?php printNonceField($this->nonce) ?>
            <input type="hidden"
                   name="action"
                   value="<?php echo esc_attr(RequestHandler::ACTION) ?>"
            />
            <?php submit_button(__('Save External Sites', 'multilingualpress')) ?>
        </form>
        <?php
    }
}
