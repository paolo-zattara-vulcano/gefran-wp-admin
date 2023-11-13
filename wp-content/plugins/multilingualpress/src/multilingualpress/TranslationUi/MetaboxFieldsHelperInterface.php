<?php

namespace Inpsyde\MultilingualPress\TranslationUi;

use Inpsyde\MultilingualPress\Framework\Http\Request;

interface MetaboxFieldsHelperInterface
{
    /**
     * Create the field ID from field key.
     *
     * @param string $fieldKey The field key.
     * @return string The field id.
     */
    public function fieldId(string $fieldKey): string;

    /**
     * Create the field name from field key.
     *
     * @param string $fieldKey The field key.
     * @return string The field name.
     */
    public function fieldName(string $fieldKey): string;

    /**
     * Get the value of a given field key from a given request.
     *
     * @param Request $request
     * @param string $fieldKey The field key.
     * @param null $default The default value.
     * @return mixed The request value.
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function fieldRequestValue(Request $request, string $fieldKey, $default = null);
}
