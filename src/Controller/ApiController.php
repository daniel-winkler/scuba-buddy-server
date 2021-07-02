<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Repository\DestinationRepository;
use App\Repository\LanguageRepository;
use App\Repository\ShopRepository;
use App\Service\DestinationNormalizer;
use App\Service\LanguageNormalizer;
use App\Service\ShopNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function shop(ShopRepository $shopRepository, ShopNormalizer $shopNormalizer, Request $request): Response
    {
        if ($request->query->has('term')) {
            $data = $shopRepository->findByTerm($request->query->get('term'));
        } else {
            $data = $shopRepository->findBy(['active' => true]);
        }
        $shops = [];

        foreach($data as $shop){
            $shops[] = $shopNormalizer->shopNormalizer($shop);
        }

        return $this->json($shops);
    }


    /**
     * @Route("/post", name="post_shop", methods={"POST"})
     */
    public function postShop(
        Request $request,
        EntityManagerInterface $entityManager,
        LanguageRepository $languageRepository,
        DestinationRepository $destinationRepository,
        ShopNormalizer $shopNormalizer,
        SluggerInterface $sluggerInterface
        ): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $shop = new Shop();
    
        $shop->setName($data['shopname']);
        $shop->setLocation($data['shoplocation']);
        $shop->setActive(true);
        $shop->setDestination($destinationRepository->find($data['destination']['id']));
        
        foreach($data['badges'] as $badge){
            $language = $languageRepository->find($badge['id']);
            $shop->addLanguage($language);
        }

        if($request->files->has('avatar')){
            $avatarFile = $request->files->get('avatar'); // crea un objeto con toda la informacion del archivo disponible en la superglobal $_FILES (metodo File Upload)

            $avatarOriginalFileName = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);

            $safeFilename = $sluggerInterface->slug($avatarOriginalFileName); // SluggerInterface normaliza los nombres de los ficheros para depurar caracteres raros.
            $avatarNewFileName = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension(); // le añadimos un id unico para evitar problemas de nombre de fichero

            try {
                $avatarFile->move(
                    $request->server->get('DOCUMENT_ROOT').DIRECTORY_SEPARATOR.'shop/avatar',
                    $avatarNewFileName
                );
            } catch (FileException $e) {
                throw new \Exception($e->getMessage());
            }

            // $shop->addPicture();
        }
       
        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json($shopNormalizer->shopNormalizer($shop));
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
}
