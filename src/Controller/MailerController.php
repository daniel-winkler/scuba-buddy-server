<?php

namespace App\Controller;

use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerController extends AbstractController
{
    /**
     * @Route("/mailer", name="mailer", methods={"POST"})
     */
    public function sendEmail(MailerInterface $mailer, Request $request, ShopRepository $shopRepository): Response
    {
        $messageData = json_decode($request->getContent(), true);
        $shop = $shopRepository->find($messageData['shopID']);

        if(!$shop->getUser() || !$shop->getUser()->getEmail()){
            return $this->json([
                'ok' => false
            ],
                Response::HTTP_NOT_FOUND
            );
        }

        $shopEmail = $shop->getUser()->getEmail();
        
        $email = (new Email())
            ->from($messageData['email'])
            ->to($shopEmail)
            ->subject('New message from: '.$messageData['name'].' ('.$messageData['email'].')')
            ->text($messageData['message']);

        $mailer->send($email);

        return $this->json([
            'ok' => true
        ],
            Response::HTTP_ACCEPTED
        );
    }
}
