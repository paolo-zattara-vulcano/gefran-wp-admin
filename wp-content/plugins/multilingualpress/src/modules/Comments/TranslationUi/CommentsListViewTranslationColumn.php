<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi;

use Inpsyde\MultilingualPress\Framework\Admin\TranslationColumnInterface;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;

use function Inpsyde\MultilingualPress\siteLanguageTag;

class CommentsListViewTranslationColumn implements TranslationColumnInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var ContentRelations
     */
    protected $contentRelations;

    public function __construct(string $name, string $title, ContentRelations $contentRelations)
    {
        $this->name = $name;
        $this->title = $title;
        $this->contentRelations = $contentRelations;
    }

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function value(int $id): string
    {
        $sourceCommentId = $id;
        $translations = [];
        $sourceSiteId = get_current_blog_id();

        $relations = $this->contentRelations->relations($sourceSiteId, $sourceCommentId, 'comment');
        unset($relations[$sourceSiteId]);

        if (!$relations) {
            return '';
        }

        foreach ($relations as $remoteSiteId => $remoteCommentId) {
            switch_to_blog($remoteSiteId);

            $siteLanguageTag = siteLanguageTag($remoteSiteId);

            if (!$siteLanguageTag) {
                return '';
            }

            $translations[] = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url(get_edit_comment_link($remoteCommentId)),
                $siteLanguageTag
            );

            restore_current_blog();
        }

        return implode('<span class="mlp-table-list-relations-divide"></span>', $translations);
    }
}
