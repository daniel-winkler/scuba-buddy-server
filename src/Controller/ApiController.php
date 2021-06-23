<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Repository\LanguageRepository;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/languages", name="languages", methods={"GET"})
     */
    public function languages(LanguageRepository $languageRepository): Response
    {
        $data = $languageRepository->findAll();

        $languages = [];

        foreach($data as $language) {
            $languages[] = $language;
        }

        // TODO: Solicitud desde otro origen bloqueada: la política de mismo origen impide leer el recurso remoto en http://localhost:8000/ (razón: falta la cabecera CORS 'Access-Control-Allow-Origin'). https://developer.mozilla.org/es/docs/Web/HTTP/CORS/Errors/CORSMissingAllowOrigin
        // fetch("http://localhost:8000")
        // .then(r => r.json())
        // .then(data => console.log(data))

        return $this->json($languages);
    }

    /**
     * @Route("/shops", name="shops", methods={"GET"})
     */
    public function shop(ShopRepository $shopRepository): Response
    {
        $data = $shopRepository->findAll();

        $shops = [];

        foreach($data as $shop) {
            $shops[] = $shop;
        }

        return $this->json($shops);
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
    public function show(int $id, ShopRepository $shopRepository): Response
    {
        $data = $shopRepository->find($id);
        return $this->json($data);
    }
}
