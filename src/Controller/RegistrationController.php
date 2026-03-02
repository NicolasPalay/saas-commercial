<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Company;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
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

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, 
    UserAuthenticatorInterface $userAuthenticator,
    AppCustomAuthenticator $authenticator,
    UserPasswordHasherInterface $userPasswordHasher, 
    EntityManagerInterface $entityManager,
    JwtService $jwt, SendMailService $mail): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $company = new Company();
            $name= $form->get("name")->getData();
            $devis = $form->get("refDevis")->getData();
            $facture = $form->get("refFacture")->getData();
            $company->setRefDevis($devis);
            $company->setRefFacture($facture);
            $company->setName($name);

            $entityManager->persist($company); 

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(["ROLE_DIRIGEANT"]);
            $user->setCompany($company);  
              
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

            
                return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );

           
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
     #[Route('/verif/{token}', name: 'verify_user')]
    public function verifUser($token, JwtService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        // On vérifie si le token est valide (cohérent, pas expiré et signature correcte)
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwt_secret'))){
            // Le token est valide
            // On récupère les données (payload)
            $payload = $jwt->getPayload($token);
            
            // On récupère le user
            $user = $userRepository->find($payload['user_id']);

            // On vérifie qu'on a bien un user et qu'il n'est pas déjà activé
            if($user && !$user->isVerified()){
                $user->setIsVerified(true);
                $em->flush();

                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_home');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
