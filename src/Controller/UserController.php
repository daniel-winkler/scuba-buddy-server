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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

        return $this->json([
            'message' => "Account created."
        ],
            Response::HTTP_CREATED
        ); 
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
    public function updateShop(
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
        
        $data['shopname'] !== "" ? $shop->setName($data['shopname']) : null;
        $data['shoplocation'] !== "" ? $shop->setLocation($data['shoplocation']) : null;
        $data['shopoverview'] !== "" ? $shop->setOverview($data['shopoverview']) : null;

        if(count($data['destination']) > 0) {
            $shop->setDestination($destinationRepository->find($data['destination']['id']));
        }

        if(count($data['badges']) > 0) {
            foreach($shop->getLanguages() as $lang){
                $language = $languageRepository->find($lang->getID());
                $shop->removeLanguage($language);
            }
            
            foreach($data['badges'] as $badge){
                $language = $languageRepository->find($badge['id']);
                $shop->addLanguage($language);
            }
        }

        if ($data['coords']['lat'] !== 0 && $data['coords']['lng'] !== 0) {
            $coordsID = $this->getUser()->getShop()->getCoords()->getId();
            $coords = $coordsRepository->find($coordsID);

            $coords->setLatitude($data['coords']['lat']);
            $coords->setLongitude($data['coords']['lng']);
            $shop->setCoords($coords);
        }


        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json($shopNormalizer->shopNormalizer($shop), Response::HTTP_ACCEPTED);
    }

    /**
    * @Route("/api/updateimage", name="update_image", methods={"POST"})
    */
    public function updateShopImage(
        ShopRepository $shopRepository, 
        Request $request, 
        EntityManagerInterface $entityManager,
        Filesystem $filesystem
        ):Response 
    {
        if (!$this->getUser()->getShop()){
            return $this->json([
                'message' => "Shop not found."
            ],
                Response::HTTP_NOT_FOUND)
            ;
        }

        $shopID = $this->getUser()->getShop()->getID();
        $shop = $shopRepository->find($shopID);

        if($request->files->has('File')) {

            if ($shop->getImage()){
                $filename = $shop->getImage();
                $filesystem->remove("images/shops/" . $filename);
            }
            
            $avatarFile = $request->files->get('File');

            $newFilename = uniqid().'.'.$avatarFile->guessExtension();

            try {
                $avatarFile->move(
                    $request->server->get('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'shops',
                    $newFilename
                );
            } catch (FileException $error) {
                throw new \Exception($error->getMessage());
            }
            $shop->setImage($newFilename);
        }

        $entityManager->persist($shop);
        $entityManager->flush();
        
        return $this->json([
            'ok' => true
        ],
            Response::HTTP_NO_CONTENT
        );
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
    public function updateAccount(UserPasswordHasherInterface $hasher, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $data = json_decode($request->getContent(), true); // recibimos el JSON en formato string, lo decodificamos, lo recogemos y con 'true' nos devuelve un array asociativo

        $userID = $this->getUser()->getID();
        $user = $userRepository->find($userID);

        if ($data['password']) {
            $plainPassword = $data['password'];
            $hashedPassword = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $data['username'] ? $user->setUsername($data['username']) : null;
        $data['email'] ? $user->setEmail($data['email']) : null;

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'ok' => true
        ], 
            Response::HTTP_ACCEPTED
        ); 
        
        
    }
}
