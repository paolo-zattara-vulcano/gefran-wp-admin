<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox;

class ExternalSitesMetaBoxView implements ExternalSitesMetaBoxViewInterface
{
    public const META_NAME = 'mlp-external-sites';

    /**
     * @inheritDoc
     */
    public function render(array $externalSites, int $postId): void
    {
        $values = get_post_meta($postId, self::META_NAME, true) ?: [];
        ?>
        <div class="wp-tab-panel">
            <table class="form-table">
                <tbody>
                <?php foreach ($externalSites as $externalSite) :
                    $namePrefix = "multilingualpress[external-sites][{$externalSite->id()}]";
                    $value = array_key_exists($externalSite->id(), $values) ? $values[$externalSite->id()] : '';
                    $urlValue = $value['url'] ?? '';
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="<?= esc_attr($externalSite->id()) ?>">
                                <?= esc_html($externalSite->languageName()) ?>
                            </label>
                        </th>
                        <td>
                            <label for="external-sites-url-<?= esc_attr($externalSite->id()) ?>">
                                <?php  esc_html__('Item Url', 'multilingualpress');?>
                            </label>
                            <input
                                type="text"
                                name="<?= esc_attr("{$namePrefix}[url]")?>"
                                id="external-sites-url-<?= esc_attr($externalSite->id()) ?>"
                                class="large-text"
                                value="<?= esc_attr($urlValue) ?>">
                        </td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
