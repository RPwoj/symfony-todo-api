<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
    public $res = [];

    #[Route('/api/users/', methods: ['GET'])]
    public function showAll(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        foreach ($users as $user) {
            $this->res[$user->getId()] = $user->getRoles();
        }

        return new JsonResponse($this->res);
    }

    #[Route('/api/user/{id}', methods: ['GET'])]
    public function show(UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);
        $this->res['user_email'] = $user->getEmail();

        return new JsonResponse($this->res);
    }

    #[Route('/api/register/', methods: ['POST'])]
    public function register(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $email = $request->query->get('email');
        // $name = $request->query->get('name');
        $plainTextPassword = $request->query->get('password');
        $repeatedPassword = $request->query->get('repeat_password');

        // $this->res['email'] = $email;
        // $this->res['name'] = $name;
        // $this->res['password'] = $password;
        // $this->res['repeatedPassword'] = $repeatedPassword;

        if ($email && $plainTextPassword && $repeatedPassword) {

            /* To add function to check if is password strong */
            if ($plainTextPassword == $repeatedPassword) {
                /* check if user exists */
                if (!$userRepository->findOneBy(array('email' => $email))) {
                    $user = new User();
                    $user->setEmail($email);
                    // $user->setName($name);

                    $hashedPassword = $passwordHasher->hashPassword($user, $plainTextPassword);
                    $user->setPassword($hashedPassword);

                    $user->setRoles(['ROLE_USER']);
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->res['info'] = 'Registered new user with id: ' . $user->getId();
                }
            }
            
        } else {
            $this->res['error'] = 'too few data';
        }

        return new JsonResponse($this->res);
    }

    // #[Route('/api/user/{id}', methods: ['PATCH'])]
    // public function edit(EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository, int $id): Response
    // {
    //     $somethingNew = $request->query->get('new_name');

    //     if ($userID && $somethingNew) {
    //         $user = $userRepository->find($userID);

    //         $user->setSomethingNew($somethingNew);
    //         $entityManager->persist($user);
    //         $entityManager->flush();

    //         $this->res['info'] = "User with id {$userID} has new something: {$somethingNew}";

    //     } else {
    //         $this->res['error'] = 'too few data';
    //     }

    //     return new JsonResponse($this->res);
    // }

    // #[Route('/api/user/{id}', methods: ['DELETE'])]
    // public function delete(EntityManagerInterface $entityManager, UserRepository $userRepository, int $userID): Response
    // {
    //     if ($userID) {
    //         $user = $userRepository->find($userID);

    //         $entityManager->remove($user);
    //         $entityManager->flush();

    //         $this->res['info'] = "user with id {$userID} deleted!";
        
    //     } else {
    //         $this->res['error'] = 'too few data';
    //     }

    //     return new JsonResponse($this->res);
    // }
}
