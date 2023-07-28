<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\Integrations\QuickLinks;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelationshipMetaInterface;
use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\OriginalTranslationLanguage;
use Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\ServiceProvider;
use Inpsyde\MultilingualPress\Module\QuickLinks\Model\ViewModel;
use Inpsyde\MultilingualPress\Module\QuickLinks\Settings\TabView;
use Inpsyde\MultilingualPress\Module\QuickLinks\QuickLink;

class QuickLinksIntegration implements Integration
{
    /**
     * @var ViewModel
     */
    protected $originalLanguageViewModel;

    /**
     * @var ContentRelationshipMetaInterface
     */
    protected $contentRelationshipMeta;

    /**
     * @var string
     */
    protected $originalKeyword;

    /**
     * @var bool
     */
    protected $quickLinkSettingOptionValue;

    public function __construct(
        ViewModel $originalLanguageViewModel,
        ContentRelationshipMetaInterface $contentRelationshipMeta,
        string $originalKeyword,
        bool $quickLinkSettingOptionValue
    ) {

        $this->originalLanguageViewModel = $originalLanguageViewModel;
        $this->contentRelationshipMeta = $contentRelationshipMeta;
        $this->originalKeyword = $originalKeyword;
        $this->quickLinkSettingOptionValue = $quickLinkSettingOptionValue;
    }

    /**
     * @inheritDoc
     */
    public function integrate(): void
    {
        add_filter(TabView::FILTER_VIEW_MODELS, function (array $viewModels): array {
            $viewModels[] = $this->originalLanguageViewModel;
            return $viewModels;
        });

        add_filter(
            QuickLink::FILTER_QUICKLINK_LABEL,
            function (string $label, int $siteId): string {
                if (!$this->quickLinkSettingOptionValue) {
                    return $label;
                }

                $originalSiteId = (int)$this->contentRelationshipMeta->relationshipMetaValueByPostId(get_the_ID(), ServiceProvider::MODULE_ID);

                if ($originalSiteId !== $siteId) {
                    return $label;
                }

                return "{$label} <span class='mlp-original-translation-language'>{$this->originalKeyword}</span>";
            },
            10,
            2
        );
    }
}
