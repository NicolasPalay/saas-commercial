<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Company;
use App\Form\EmployedFormType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppCustomAuthenticator;
use App\Services\JwtService;
use App\Services\SendMailService;

final class EmployedController extends AbstractController
{
    #[Route('/employed', name: 'app_employed')]
    public function index(Request $request, 
    UserAuthenticatorInterface $userAuthenticator,
    AppCustomAuthenticator $authenticator,
    UserPasswordHasherInterface $userPasswordHasher, 
    EntityManagerInterface $entityManager,
    JwtService $jwt, 
    SendMailService $mail): Response
    {
        
       $dirigeant= $this->getUser();
       if (!$this->isGranted('ROLE_DIRIGEANT')) {
            return $this->redirectToRoute('app_login');
        }

        $user = new User();
        
     
        $form = $this->createForm(EmployedFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $company = $dirigeant->getCompany();
            $user->setCompany($company);    

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(["ROLE_EMPLOYE"]);
                 
            $entityManager->persist($user);
            $entityManager->flush();
 // do anything else you need here, like send an email

            // Générer le token
            // Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwt_secret'));

            // Envoyer l'e-mail
            $mail->send(
                'no-reply@openblog.test',
                $user->getEmail(),
                'Activation de votre compte sur le site OpenBlog',
                'register',
                compact('user', 'token') // ['user' => $user, 'token'=>$token]
            );
            $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vérifier votre boîte e-mail pour l\'activation de votre compte.');

            return $this->redirectToRoute('app_dashboard');

           
        }

        return $this->render('employed/index.html.twig', [
            'employedForm' => $form->createView(),
            
        ]);
    }
}
