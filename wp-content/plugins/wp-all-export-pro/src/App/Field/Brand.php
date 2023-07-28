<?php

namespace Wpae\App\Field;


class Brand extends Field
{
    const SECTION = 'uniqueIdentifiers';

    public function getValue($snippetData)
    {
        $uniqueIdentifiersData = $this->feed->getSectionFeedData(self::SECTION);

        $value = $this->replaceSnippetsInValue($uniqueIdentifiersData['brand'], $snippetData);

        return $value;
    }

    public function getFieldName()
    {
        return 'brand';
    }
}