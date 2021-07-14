<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CoordsRepository;
use App\Repository\DestinationRepository;
use App\Repository\LanguageRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Service\ShopNormalizer;
use App\Service\UserNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(UserPasswordHasherInterface $hasher, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true); // recibimos el JSON en formato string, lo decodificamos, lo recogemos y con 'true' nos devuelve un array asociativo

        $user = new User();

        $plainPassword = $data['newPassword'];
        $hashedPassword = $hasher->hashPassword($user, $plainPassword);

        $user->setUsername($data['newUsername']);
        $user->setEmail($data['newEmail']);
        $user->setPassword($hashedPassword);
        $user->setActive(true);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED); 
    }

    /**
     * @Route("/api/dashboard", name="dashboard", methods={"GET"})
     */
    public function dashboard(UserNormalizer $userNormalizer, ShopNormalizer $shopNormalizer): Response
    {
        $userData = $this->getUser();
        $user = $userNormalizer->userNormalizer($userData, $shopNormalizer); // pasamos el normalizador de shop para que el normalizador de users compruebe que tiene uno asociado

        return $this->json($user);
        
    }

    /**
     * @Route("/api/update", name="update_shop", methods={"PUT"})
     */
    public function postShop(
        Request $request,
        LanguageRepository $languageRepository,
        DestinationRepository $destinationRepository,
        ShopNormalizer $shopNormalizer,
        ShopRepository $shopRepository,
        CoordsRepository $coordsRepository,
        EntityManagerInterface $entityManager
        ): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$this->getUser()->getShop()){
            return $this->json([
                'message' => "Shop not found."
            ],
                Response::HTTP_NOT_FOUND)
            ;
        }

        $shopID = $this->getUser()->getShop()->getID();
        $shop = $shopRepository->find($shopID);
        
        $shop->setName($data['shopname']);
        $shop->setLocation($data['shoplocation']);
        $shop->setDestination($destinationRepository->find($data['destination']['id']));

        foreach($shop->getLanguages() as $lang){
            $language = $languageRepository->find($lang->getID());
            $shop->removeLanguage($language);
        }
        
        foreach($data['badges'] as $badge){
            $language = $languageRepository->find($badge['id']);
            $shop->addLanguage($language);
        }

        $coordsID = $this->getUser()->getShop()->getCoords()->getId();
        $coords = $coordsRepository->find($coordsID);

        $coords->setLatitude($data['coords']['lat']);
        $coords->setLongitude($data['coords']['lng']);

        $shop->setCoords($coords);

        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json($shopNormalizer->shopNormalizer($shop), Response::HTTP_ACCEPTED);
    }


    /**
     * @Route("/api/delete", name="delete", methods={"PUT"})
     */
    public function delete(ShopRepository $shopRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if(!$user->getShop()){

            return $this->json([
                'message' => "Shop not found"
            ],
                Response::HTTP_NOT_FOUND
            );

        } 

        $shopID = $user->getShop()->getId();
    
    
        $shop = $shopRepository->find($shopID);

        // ejecutamos un 'soft delete', cambiando la propiedad active de la entidad Shop a falso, y vaciando la propiedad shop del usuario como null.
        $shop->setActive(false);
        $user->setShop(null);

        $entityManager->persist($user);
        $entityManager->persist($shop);
        $entityManager->flush();
    
    
        return $this->json([
            'message' => "Shop has been removed"
        ],
            Response::HTTP_ACCEPTED
        );
        
        
    }

    /**
     * @Route("/api/account", name="account", methods={"PUT"})
     */
    public function account(UserPasswordHasherInterface $hasher, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $data = json_decode($request->getContent(), true); // recibimos el JSON en formato string, lo decodificamos, lo recogemos y con 'true' nos devuelve un array asociativo

        $userID = $this->getUser()->getID();
        $user = $userRepository->find($userID);

        $plainPassword = $data['password'];
        $hashedPassword = $hasher->hashPassword($user, $plainPassword);

        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_ACCEPTED); 
        
        
    }
}
