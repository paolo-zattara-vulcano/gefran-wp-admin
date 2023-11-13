<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi;

use Inpsyde\MultilingualPress\Framework\Admin\TranslationColumnInterface;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentRelationSaveHelper;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextFactory;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax\AjaxUpdateCommentsRelationshipRequestHandler;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax\AjaxSearchCommentRequestHandler;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxCopyContent;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxField;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxFieldAuthorEmail;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxFieldAuthorName;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxFieldAuthorUrl;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxRelation;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxStatus;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactory;

(static function (Container $container) {

    /**
     * The list of relation tab fields
     *
     * @returns CommentMetaboxField[]
     */
    $container->share(
        'multilingualpress.Comments.MetaboxRelationTabFields',
        static function (Container $container): array {
            $helperFactory = $container->get(MetaboxFieldsHelperFactory::class);
            return [
                new CommentMetaboxRelation('relationship', $helperFactory),
            ];
        }
    );

    /**
     * The list of base tab fields
     *
     * @returns CommentMetaboxField[]
     */
    $container->share(
        'multilingualpress.Comments.MetaboxBaseTabFields',
        static function (Container $container): array {
            $helperFactory = $container->get(MetaboxFieldsHelperFactory::class);
            return [
                new CommentMetaboxFieldAuthorEmail(
                    'remote-author-email',
                    __('Author Email', 'multilingualpress'),
                    $helperFactory
                ),
                new CommentMetaboxFieldAuthorName(
                    'remote-author-name',
                    __('Author Name', 'multilingualpress'),
                    $helperFactory
                ),
                new CommentMetaboxFieldAuthorUrl(
                    'remote-author-url',
                    __('Author Url', 'multilingualpress'),
                    $helperFactory
                ),
                new CommentMetaboxCopyContent(
                    'remote-content-copy',
                    __('Copy source author field values', 'multilingualpress'),
                    $helperFactory
                ),
                new CommentMetaboxStatus('remote-status', __('Status', 'multilingualpress'), $helperFactory),
            ];
        }
    );

    /**
     * The list of all comment metabox fields
     *
     * @returns CommentMetaboxField[]
     */
    $container->share(
        'multilingualpress.Comments.MetaboxFields',
        static function (Container $container): array {
            $relationTabFields = $container->get('multilingualpress.Comments.MetaboxRelationTabFields');
            $baseTabFields = $container->get('multilingualpress.Comments.MetaboxBaseTabFields');
            return array_merge($relationTabFields, $baseTabFields);
        }
    );

    /**
     * The list of comment metabox tabs
     *
     * @returns CommentMetaboxTabInterface[]
     */
    $container->share(
        'multilingualpress.Comments.MetaboxTabs',
        static function (Container $container): array {
            return [
                new CommentMetaboxTab(
                    'tab-relation',
                    _x('Relationship', 'translation comment metabox', 'multilingualpress'),
                    $container->get('multilingualpress.Comments.MetaboxRelationTabFields')
                ),
                new CommentMetaboxTab(
                    'tab-base',
                    _x('Content', 'translation comment metabox', 'multilingualpress'),
                    $container->get('multilingualpress.Comments.MetaboxBaseTabFields')
                ),
            ];
        }
    );

    $container->share(
        AjaxSearchCommentRequestHandler::class,
        static function (Container $container): AjaxSearchCommentRequestHandler {
            return new AjaxSearchCommentRequestHandler($container->get(CommentsRelationshipContextFactory::class));
        }
    );

    $container->share(
        AjaxUpdateCommentsRelationshipRequestHandler::class,
        static function (Container $container): AjaxUpdateCommentsRelationshipRequestHandler {
            return new AjaxUpdateCommentsRelationshipRequestHandler(
                $container->get(CommentsRelationshipContextFactory::class),
                $container->get(ContentRelations::class),
                $container->get('multilingualpress.Comments.MetaboxTabs'),
                $container->get('multilingualpress.Comments.MetaboxFields'),
                $container->get(MetaboxFieldsHelperFactory::class),
                $container->get(CommentRelationSaveHelper::class)
            );
        }
    );

    $container->share(
        CommentsListViewTranslationColumn::class,
        static function (Container $container): TranslationColumnInterface {
            return new CommentsListViewTranslationColumn(
                'translations',
                __('Translations', 'multilingualpress'),
                $container->get(ContentRelations::class)
            );
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
