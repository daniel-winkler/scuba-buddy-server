<?php

namespace App\Controller;

use App\Entity\User;
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

        return $this->json($user); 
    }

    /**
     * @Route("/api/dashboard", name="dashboard", methods={"GET"})
     */
    public function dashboard(): Response
    {
        dump($this->getUser()->getShop());
        die();
        return $this->getUser();
    }
}
