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
        $data = $request->request;
        dump($data);

        $user = new User();

        $plainPassword = $data->get('newPassword');
        $hashedPassword = $hasher->hashPassword($user, $plainPassword);

        $user->setUsername($request->get('newUsername'));
        $user->setEmail($request->get('newEmail'));
        $user->setPassword($hashedPassword);
        $user->setActive(true);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user);

        // TODO: conseguir el POST desde el formulario ($data me lo devuelve vacio) 
    }
}
