<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Query\Data\Filter\Module;

use XCart\Bus\Core\Annotations\DataSourceFilter;
use XCart\Bus\Domain\Module;
use XCart\Bus\Query\Data\Filter\AModifier;

/**
 * @DataSourceFilter(name="language")
 */
class Language extends AModifier
{
    /**
     * @return mixed
     */
    public function current()
    {
        /** @var Module $item */
        $item = $this->getInnerIterator()->current();

        $translation = $this->getTranslation((array) $item->translations, $this->data);
        unset($item->translations);

        if ($translation) {
            $item->merge(array_filter($translation));
        }

        return $item;
    }

    /**
     * @param array  $translations
     * @param string $language
     *
     * @return array|mixed
     */
    private function getTranslation(array $translations, $language)
    {
        foreach ($translations as $translation) {
            if ($translation['code'] === $language) {
                unset($translation['code']);

                return $translation;
            }
        }

        return [];
    }
}
