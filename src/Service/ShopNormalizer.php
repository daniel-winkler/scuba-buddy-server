<?php

namespace App\Service;

use App\Entity\Shop;
use Symfony\Component\HttpFoundation\UrlHelper;

class ShopNormalizer {

    // private $urlConstructor;

    // public function __construct(UrlHelper $urlHelper)
    // {
    //     $this->urlConstructor = $urlHelper;
    // }

    /**
     * Normalize a shop.
     * 
     * @param Shop $shop
     * 
     * @return array|null
     */

    public function shopNormalizer(Shop $shop): ?array {

        $languages = [];
        
        foreach($shop->getLanguages() as $language) {
            array_push($languages, [
                'id' => $language->getId(),
                'name' => $language->getName(),
                'countrycode' => $language->getCountrycode()
            ]);
        }

        if($shop->getCoords()) {
            $coords = [
                'lat' => $shop->getCoords()->getLatitude(),
                'lng' => $shop->getCoords()->getLongitude()
            ];
        } else {
            $coords = [
                'lat' => 0,
                'lng' => 0
            ];
        }

        $destination = [
            'id' => $shop->getDestination()->getId(),
            'name' => $shop->getDestination()->getName()
        ];
        

        $data = [
            'id' => $shop->getId(),
            'name' => $shop->getName(),
            'location' => $shop->getLocation(),
            'destination' => $destination,
            'coords' => $coords,
            'active' => $shop->getActive(),
            'languages' => $languages
        ];

        return $data;

    }
}