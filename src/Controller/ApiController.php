<?php

namespace App\Controller;

use App\Repository\LanguageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("", name="languages", methods={"GET"})
     */
    public function index(LanguageRepository $languageRepository): Response
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
}
