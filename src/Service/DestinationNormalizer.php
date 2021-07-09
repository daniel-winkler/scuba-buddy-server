<?php

namespace App\Service;

use App\Entity\Destination;

class DestinationNormalizer {

    /**
     * Normalize a destination.
     * 
     * @param Destination $destination
     * 
     * @return array|null
     */

    public function destinationNormalizer(Destination $destination): ?array {

        // $shops = [];
        
        // foreach($destination->getShops() as $shop) {
        //     array_push($shops, [
        //         'id' => $shop->getId(),
        //         'name' => $shop->getName()
        //     ]);
        // }

        $data = [
            'id' => $destination->getId(),
            'name' => $destination->getName(),
            // 'shops' => $shops
        ];

        return $data;

    }
}