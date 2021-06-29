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
    public function shop(ShopRepository $shopRepository, ShopNormalizer $shopNormalizer): Response
    {
        $data = $shopRepository->findAll();

        $shops = [];
        
        foreach($data as $shop) {
            $shops[] = $shopNormalizer->shopNormalizer($shop);
        }

        return $this->json($shops);
    }

    /**
     * @Route("/post", name="post_shop", methods={"POST"})
     */
    public function postShop(ShopRepository $shopRepository, ShopNormalizer $shopNormalizer, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        dump($data);
        die();
        // TODO: terminar de recibir el request y crear el POST 

        $shop = new Shop();



        return $this->json($shop);
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
