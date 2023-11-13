<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax;

use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Entity;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Metabox as FrameworkMetabox;
use Inpsyde\MultilingualPress\Framework\Http\RequestHandler;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentRelationSaveHelperInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContext;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextFactoryInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\CommentMetabox;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\CommentMetaboxTabInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxField;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactoryInterface;
use RuntimeException;

use function Inpsyde\MultilingualPress\siteExists;
use function Inpsyde\MultilingualPress\siteNameWithLanguage;

class AjaxUpdateCommentsRelationshipRequestHandler implements RequestHandler
{
    public const ACTION = 'multilingualpress_update_comment_relationship';

    protected const AVAILABLE_TASKS = ['new', 'existing', 'remove'];

    /**
     * @var CommentsRelationshipContextFactoryInterface
     */
    protected $relationshipContextFactory;

    /**
     * @var ContentRelations
     */
    protected $contentRelations;

    /**
     * @var CommentMetaboxTabInterface[]
     */
    protected $metaboxTabs;

    /**
     * @var CommentMetaboxField[]
     */
    protected $metaboxFields;

    /**
     * @var MetaboxFieldsHelperFactoryInterface
     */
    protected $metaboxFieldsHelperFactory;

    /**
     * @var CommentRelationSaveHelperInterface
     */
    protected $commentRelationSaveHelper;

    public function __construct(
        CommentsRelationshipContextFactoryInterface $relationshipContextFactory,
        ContentRelations $contentRelations,
        array $metaboxTabs,
        array $metaboxFields,
        MetaboxFieldsHelperFactoryInterface $metaboxFieldsHelperFactory,
        CommentRelationSaveHelperInterface $commentRelationSaveHelper
    ) {

        $this->relationshipContextFactory = $relationshipContextFactory;
        $this->contentRelations = $contentRelations;
        $this->metaboxTabs = $metaboxTabs;
        $this->metaboxFields = $metaboxFields;
        $this->metaboxFieldsHelperFactory = $metaboxFieldsHelperFactory;
        $this->commentRelationSaveHelper = $commentRelationSaveHelper;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequest $request)
    {
        if (!wp_doing_ajax()) {
            return;
        }

        if (!doing_action('wp_ajax_' . self::ACTION)) {
            wp_send_json_error('Invalid action.');
        }

        $task = (string)$request->bodyValue('task', INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!in_array($task, self::AVAILABLE_TASKS, true)) {
            wp_send_json_error('Invalid task.');
        }

        try {
            $context = $this->createContextFromRequest($request);
            switch ($task) {
                case 'existing':
                    $this->commentRelationSaveHelper->relateComments($context);
                    break;
                case 'remove':
                    $this->commentRelationSaveHelper->disconnectComments($context);
                    break;
                default:
                    break;
            }

            $context = $this->configureContext($context);
            $remoteSiteId = $context->remoteSiteId();

            $title = sprintf(
            /* translators: %s is site name including language */
                __('Translation for "%s"', 'multilingualpress'),
                siteNameWithLanguage($remoteSiteId)
            );

            $commentMetabox = new CommentMetabox(
                $title,
                $context,
                $this->metaboxTabs,
                $this->metaboxFields,
                $this->metaboxFieldsHelperFactory->createMetaboxFieldsHelper($remoteSiteId),
                $this->commentRelationSaveHelper
            );

            $entity = new Entity($context->sourceComment());
            $info = $commentMetabox->createInfo(FrameworkMetabox::SHOW, $entity);

            switch_to_blog($remoteSiteId);
            ob_start();
            $commentMetabox->view($entity)->render($info);
            $rendered = ob_get_clean();
            restore_current_blog();

            wp_send_json_success($rendered);
        } catch (RuntimeException $exception) {
            wp_send_json_error($exception->getMessage());
        }
    }

    /**
     * Creates the relationship context from given request.
     *
     * @param ServerRequest $request
     * @return CommentsRelationshipContextInterface
     * @throws RuntimeException if problem creating.
     */
    protected function createContextFromRequest(ServerRequest $request): CommentsRelationshipContextInterface
    {
        $sourceSiteId = (int)$request->bodyValue(
            'source_site_id',
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        $sourceCommentId = (int)$request->bodyValue(
            'source_comment_id',
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        $remoteSiteId = (int)$request->bodyValue(
            'remote_site_id',
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        $remoteCommentId = (int)$request->bodyValue(
            'remote_comment_id',
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        if (
            !$sourceSiteId
            || !$sourceCommentId
            || !$remoteSiteId
            || !siteExists($sourceSiteId)
            || !siteExists($remoteSiteId)
        ) {
            throw new RuntimeException('Invalid context.');
        }

        return $this->relationshipContextFactory->createCommentsRelationshipContext(
            $sourceSiteId,
            $remoteSiteId,
            $sourceCommentId,
            $remoteCommentId
        );
    }

    /**
     * Configures the given context.
     *
     * @param CommentsRelationshipContextInterface $context
     * @return CommentsRelationshipContextInterface
     * @throws NonexistentTable
     */
    protected function configureContext(CommentsRelationshipContextInterface $context): CommentsRelationshipContextInterface
    {
        $remoteCommentId = $this->contentRelations->contentIdForSite(
            $context->sourceSiteId(),
            $context->sourceCommentId(),
            ContentRelations::CONTENT_TYPE_COMMENT,
            $context->remoteSiteId()
        );

        return CommentsRelationshipContext::fromExistingAndData(
            $context,
            [CommentsRelationshipContext::REMOTE_COMMENT_ID => $remoteCommentId]
        );
    }
}
