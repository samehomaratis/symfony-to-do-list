<?php

namespace App\Controller\API;

use App\Entity\UserModal;
use App\Form\RegistrationFormTypeForm;
use App\Repository\UserModalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserModalRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request,
                             UserPasswordHasherInterface $passwordHasher,
                             JWTTokenManagerInterface $jwtManager,
                             EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['email'], $data['name'] , $data['password'])) {
            return new JsonResponse(['error' => 'Name, email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new UserModal();
        $form = $this->createForm(RegistrationFormTypeForm::class, $user, [
            'csrf_protection' => false,
        ]);

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $token = $jwtManager->create($user);

            return new JsonResponse(['token' => $token]);
        }

        return new JsonResponse([
            'errors' => (string)$form->getErrors(true, false)
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
