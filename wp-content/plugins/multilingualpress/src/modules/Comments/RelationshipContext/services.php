<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Service\Container;

(static function (Container $container) {
    $container->share(
        CommentsRelationshipContextFactory::class,
        static function (): CommentsRelationshipContextFactoryInterface {
            return new CommentsRelationshipContextFactory();
        }
    );

    $container->share(
        CommentRelationSaveHelper::class,
        static function (Container $container): CommentRelationSaveHelperInterface {
            return new CommentRelationSaveHelper($container->get(ContentRelations::class));
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
