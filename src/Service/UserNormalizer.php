<?php

namespace App\Service;

use App\Entity\User;
use App\Service\ShopNormalizer;

class UserNormalizer {

    /**
     * Normalize a user.
     * 
     * @param User $user
     * 
     * @return array|null
     */

    public function userNormalizer(User $user, ShopNormalizer $shopNormalizer): ?array {

        if($user->getShop()){
            $shop = $shopNormalizer->shopNormalizer($user->getShop());
        } else {
            $shop = [];
        }

        $data = [
            'id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'shop' => $shop
        ];

        return $data;

    }
}