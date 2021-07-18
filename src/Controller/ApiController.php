<?php

namespace App\Controller;

use App\Entity\Coords;
use App\Entity\Picture;
use App\Entity\Shop;
use App\Repository\DestinationRepository;
use App\Repository\LanguageRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Service\DestinationNormalizer;
use App\Service\LanguageNormalizer;
use App\Service\ShopNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/languages", name="languages", methods={"GET"})
     */
    public function languages(LanguageRepository $languageRepository, LanguageNormalizer $languageNormalizer): Response
    {
        $data = $languageRepository->findAll();

        $languages = [];

        foreach($data as $language) {
            $languages[] = $languageNormalizer->languageNormalizer($language);
        }

        return $this->json($languages);
    }

    /**
     * @Route("/shops", name="shops", methods={"GET"})
     */
    public function shop(
        ShopRepository $shopRepository,
        ShopNormalizer $shopNormalizer,
        Request $request,
        PaginatorInterface $paginator
        ): Response
    {

        //TODO: crear query Destinations
        
        if ($request->query->has('term')) {
            $query = $shopRepository->findByTerm($request->query->get('term'));
        } else if ($request->query->has('lang')) {
            $query = $shopRepository->findByLanguages($request->query->get('lang'));
        } else if ($request->query->has('dest')) {
            $query = $shopRepository->findByDestination($request->query->get('dest'));
        } else {
            $query = $shopRepository->findActive();
        }

        $paginatedData = $shopRepository->getPagination($paginator, $request, $query);

        if (count($paginatedData->getItems()) !== 0) { // nos protege si el resultado de la query es 0.
            $data = $paginatedData->getItems();
            foreach($data as $shop){
                $shops[] = $shopNormalizer->shopNormalizer($shop);
            }
        } else {
            $shops = [];
        }

        $totalPages = $paginatedData->getTotalItemCount() / $paginatedData->getItemNumberPerPage();

        $finaljson = [
            'total_results' => $paginatedData->getTotalItemCount(),
            'total_pages' => ceil($totalPages), // ceil redondea un numero decimal al proximo numero entero, para conseguir el numero de paginas exactas.
            'current_page' => $paginatedData->getCurrentPageNumber(),
            'results' => $shops
        ];


        return $this->json($finaljson);
    }


    /**
     * @Route("/api/post", name="post_shop", methods={"POST"})
     */
    public function postShop(
        Request $request,
        EntityManagerInterface $entityManager,
        LanguageRepository $languageRepository,
        DestinationRepository $destinationRepository,
        ShopNormalizer $shopNormalizer,
        SluggerInterface $sluggerInterface,
        UserRepository $userRepository
        ): Response
    {
        $data = json_decode($request->getContent(), true);

        if($this->getUser()->getShop()){
            return $this->json([
                'message' => "Shop already posted"
            ],
                Response::HTTP_FORBIDDEN
            );
        }
        
        $shop = new Shop();
        
        $shop->setName($data['shopname']);
        $shop->setLocation($data['shoplocation']);
        $shop->setActive(true);
        $shop->setDestination($destinationRepository->find($data['destination']['id']));

        $userId = $this->getUser()->getId();
        $shop->setUser($userRepository->find($userId));
        
        foreach($data['badges'] as $badge){
            $language = $languageRepository->find($badge['id']);
            $shop->addLanguage($language);
        }

        

        // TODO: crear un endpoint distinto para hacer post con content-type: multipart/form-data??
        // foreach($request->files as $file) {
        //     dump($file);
        //     $picture = new Picture();

        //     $fileOriginalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        //     $safeFilename = $sluggerInterface->slug($fileOriginalFileName); // SluggerInterface normaliza los nombres de los ficheros para depurar caracteres raros.
        //     $pictureNewFileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension(); // le añadimos un id unico para evitar problemas de nombre de fichero

        //     try {
        //         $file->move(
        //             $request->server->get('DOCUMENT_ROOT').DIRECTORY_SEPARATOR.'shop/pictures',
        //             $pictureNewFileName
        //         );
        //     } catch (FileException $e) {
        //         throw new \Exception($e->getMessage());
        //     }

        //     $shop->addPicture($picture);

        //     // Setear las propiedades del objeto picture.

        //     // Renombrar y mover el fichero a su ubicación final.

        //     // Añadas al objeto shop el picture, con $shop->addPicture($picture);

        //     // persistir objeto picture en el entity manager.
        //     $entityManager->persist($picture);
        //     $entityManager->flush();

        // }

        // if($request->files->has('avatar')){
        //     $avatarFile = $request->files->get('avatar'); // crea un objeto con toda la informacion del archivo disponible en la superglobal $_FILES (metodo File Upload)

        //     $avatarOriginalFileName = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);

        //     $safeFilename = $sluggerInterface->slug($avatarOriginalFileName); // SluggerInterface normaliza los nombres de los ficheros para depurar caracteres raros.
        //     $avatarNewFileName = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension(); // le añadimos un id unico para evitar problemas de nombre de fichero

        //     try {
        //         $avatarFile->move(
        //             $request->server->get('DOCUMENT_ROOT').DIRECTORY_SEPARATOR.'shop/pictures',
        //             $avatarNewFileName
        //         );
        //     } catch (FileException $e) {
        //         throw new \Exception($e->getMessage());
        //     }

        //     $shop->addPicture();
        // }

        $coords = new Coords();
        $coords->setLatitude($data['coords']['lat']);
        $coords->setLongitude($data['coords']['lng']);

        $entityManager->persist($coords);
        // $entityManager->flush();

        $shop->setCoords($coords);
    
        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json($shopNormalizer->shopNormalizer($shop), Response::HTTP_CREATED);
    }

    /**
    * @Route(
    *      "/api/uploadshopimage/{id}",
    *      name="uploadshopimage",
    *      methods={"POST"},
    *      requirements={
    *          "id": "\d+"
    *      }     
    * )
    */
    public function uploadShopImage(
        Shop $shop, 
        Request $request, 
        EntityManagerInterface $entityManager):Response {

        if($request->files->has('File')) {
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

        $entityManager->flush();
        
        return $this->json([
            'ok' => true
        ],
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route(
     *      "/shopdetails/{id}",
     *      name="shopdetails",
     *      methods={"GET"},
     *      requirements={
     *          "id": "\d+"
     *      }     
     * )
     */
    public function show(int $id, ShopRepository $shopRepository, ShopNormalizer $shopNormalizer): Response
    {
        $data = $shopRepository->find($id);
        return $this->json($shopNormalizer->shopNormalizer($data));
    }

    /**
     * @Route("/destinations", name="destinations", methods={"GET"})
     */
    public function destinations(DestinationRepository $destinationRepository, DestinationNormalizer $destinationNormalizer): Response
    {
        $data = $destinationRepository->findAll();

        $destinations = [];

        foreach($data as $destination) {
            $destinations[] = $destinationNormalizer->destinationNormalizer($destination);
        }

        return $this->json($destinations);
    }

    /**
     * @Route("/clickcounter", name="clickcounter", methods={"PUT"})
     */
    public function clickcounter(DestinationRepository $destinationRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $destinationID = json_decode($request->getContent(), true);
        $destination = $destinationRepository->find($destinationID);
        $currentCounter = $destination->getClickcounter();

        $destination->setClickcounter($currentCounter + 1);

        $entityManager->persist($destination);
        $entityManager->flush();

        return $this->json([
            'message' => 'Ok'
        ],
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * @Route("/popular", name="popular", methods={"GET"})
     */
    public function popular(DestinationRepository $destinationRepository, Request $request, DestinationNormalizer $destinationNormalizer): Response
    {
        $popularDestinations = $destinationRepository->findPopular();

        $destinations = [];

        foreach($popularDestinations as $destination){
            $destinations[] = $destinationNormalizer->popularNormalizer($destination);
        }
        
        return $this->json($destinations,
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * @Route("/api/token_check", name="token_check")
     */

    public function auth_token(): Response
    {
        return $this->json([
            'ok' => true
        ],
            Response::HTTP_ACCEPTED
        );
    }
}
