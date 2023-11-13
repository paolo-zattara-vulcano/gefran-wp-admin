<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi;

use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Action;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Info;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\View;
use Inpsyde\MultilingualPress\Framework\Entity;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Metabox as MetaboxInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentRelationSaveHelperInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxField;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;

class CommentMetabox implements MetaboxInterface
{
    public const RELATIONSHIP_TYPE = 'comment';
    public const ID_PREFIX = 'multilingualpress_comment_translation_metabox_';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var CommentsRelationshipContextInterface
     */
    protected $relationshipContext;

    /**
     * @var CommentMetaboxTabInterface[]
     */
    protected $metaboxTabs;

    /**
     * @var CommentMetaboxField[]
     */
    protected $metaboxFields;

    /**
     * @var MetaboxFieldsHelperInterface
     */
    protected $metaboxFieldsHelper;

    /**
     * @var CommentRelationSaveHelperInterface
     */
    protected $commentRelationSaveHelper;

    public function __construct(
        string $title,
        CommentsRelationshipContextInterface $relationshipContext,
        array $metaboxTabs,
        array $metaboxFields,
        MetaboxFieldsHelperInterface $metaboxFieldsHelper,
        CommentRelationSaveHelperInterface $commentRelationSaveHelper
    ) {

        $this->title = $title;
        $this->relationshipContext = $relationshipContext;
        $this->metaboxTabs = $metaboxTabs;
        $this->metaboxFields = $metaboxFields;
        $this->metaboxFieldsHelper = $metaboxFieldsHelper;
        $this->commentRelationSaveHelper = $commentRelationSaveHelper;
    }

    /**
     * @inheritDoc
     */
    public function siteId(): int
    {
        return $this->relationshipContext->remoteSiteId();
    }

    /**
     * @inheritDoc
     */
    public function isValid(Entity $entity): bool
    {
        return current_user_can('moderate_comments')
            && current_user_can_for_blog($this->siteId(), 'moderate_comments');
    }

    /**
     * @inheritdoc
     */
    public function createInfo(string $showOrSave, Entity $entity): Info
    {
        return new Info($this->title, self::ID_PREFIX . $this->siteId(), 'normal');
    }

    /**
     * @inheritdoc
     */
    public function view(Entity $entity): View
    {
        return new CommentMetaboxView($this->metaboxTabs, $this->metaboxFieldsHelper, $this->relationshipContext);
    }

    /**
     * @inheritdoc
     */
    public function action(Entity $entity): Action
    {
        return new MetaboxAction(
            $this->metaboxFields,
            $this->metaboxFieldsHelper,
            $this->relationshipContext,
            $this->commentRelationSaveHelper
        );
    }
}
