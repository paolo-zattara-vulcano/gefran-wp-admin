<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage;

use Inpsyde\MultilingualPress\Framework\Admin\Metabox\PostMetaboxRendererInterface;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelationshipMetaInterface;

/**
 * @psalm-type relatedSites = array{id: int, name: string}
 */
class MetaboxRenderer implements PostMetaboxRendererInterface
{
    /**
     * @var array<int, string> The list of related sites.
     * @psalm-var array<relatedSites>
     */
    protected $relatedSites;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $relationshipMetaName;

    /**
     * @var ContentRelationshipMetaInterface
     */
    protected $contentRelationshipMeta;

    public function __construct(
        array $relatedSites,
        string $label,
        string $relationshipMetaName,
        ContentRelationshipMetaInterface $contentRelationshipMeta
    ) {

        $this->relatedSites = $relatedSites;
        $this->label = $label;
        $this->relationshipMetaName = $relationshipMetaName;
        $this->contentRelationshipMeta = $contentRelationshipMeta;
    }

    /**
     * @inheritDoc
     */
    public function render(int $postId): void
    {
        $moduleId = ServiceProvider::MODULE_ID;
        $name = "{$this->relationshipMetaName}[$moduleId]";
        $value = (int)$this->contentRelationshipMeta->relationshipMetaValueByPostId($postId, $moduleId) ?: get_current_blog_id();
        ?>
        <label for="<?= esc_attr($moduleId);?>" class="css-1v57ksj">
            <?= esc_html($this->label);?>
        </label>
        <select id="<?= esc_attr($moduleId);?>" name="<?= esc_attr($name);?>">
            <?php foreach ($this->relatedSites as $relatedSite) :
                $siteId = $relatedSite['id'] ?? 0;
                ?>
                <option value="<?= esc_attr($siteId);?>" <?php selected($siteId, $value);?>">
                    <?= esc_html($relatedSite['name'] ?? '');?>
                </option>
            <?php endforeach;?>
        </select>
        <?php
    }
}
