<?php

namespace App\Service;

use App\Entity\Language;

class LanguageNormalizer {

    /**
     * Normalize a language.
     * 
     * @param Language $language
     * 
     * @return array|null
     */

    public function languageNormalizer(Language $language): ?array {

        $shops = [];
        
        foreach($language->getShops() as $shop) {
            array_push($shops, [
                'id' => $shop->getId(),
                'name' => $shop->getName()
            ]);
        }

        $data = [
            'id' => $language->getId(),
            'name' => $language->getName(),
            'countrycode' => $language->getCountrycode(),
            'shops' => $shops
        ];

        return $data;

    }
}