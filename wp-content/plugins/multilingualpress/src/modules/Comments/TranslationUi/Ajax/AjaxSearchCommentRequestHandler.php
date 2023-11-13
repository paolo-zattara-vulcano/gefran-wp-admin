<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax;

use Inpsyde\MultilingualPress\Framework\Http\RequestHandler;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextFactoryInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use RuntimeException;

use function Inpsyde\MultilingualPress\siteExists;

class AjaxSearchCommentRequestHandler implements RequestHandler
{
    public const ACTION = 'multilingualpress_remote_comment_search';
    public const FILTER_REMOTE_ARGUMENTS = 'multilingualpress.remote_post_search_arguments';

    /**
     * @var CommentsRelationshipContextFactoryInterface
     */
    protected $relationshipContextFactory;


    public function __construct(CommentsRelationshipContextFactoryInterface $relationshipContextFactory)
    {
        $this->relationshipContextFactory = $relationshipContextFactory;
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

        $searchQuery = (string)$request->bodyValue(
            'search',
            INPUT_POST,
            FILTER_SANITIZE_SPECIAL_CHARS,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );

        if (!$searchQuery) {
            wp_send_json_error('Missing data.');
        }

        try {
            $context = $this->createContextFromRequest($request);
            wp_send_json_success($this->findComments($context));
        } catch (RuntimeException $exception) {
            wp_send_json_error($exception->getMessage());
        }
    }

    /**
     * Finds the comment for given context
     *
     * @param CommentsRelationshipContextInterface $context
     * @return array
     */
    protected function findComments(CommentsRelationshipContextInterface $context): array
    {
        $args = [
            'post_id' => $context->remotePostId(),
            'fields' => 'ids',
        ];

        if ($context->hasRemoteComment()) {
            $args['comment__not_in'] = [$context->remoteCommentId()];
        }

        /**
         * Filters the query arguments for the comment search.
         *
         * @param array $args
         */
        $args = (array)apply_filters(self::FILTER_REMOTE_ARGUMENTS, $args);

        switch_to_blog($context->remoteSiteId());
        $comments = get_comments($args);
        restore_current_blog();

        return array_map(
            static function (string $commentId): array {
                return ['id' => (int)$commentId];
            },
            $comments
        );
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
}
