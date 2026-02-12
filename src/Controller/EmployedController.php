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

final class EmployedController extends AbstractController
{
    #[Route('/employed', name: 'app_employed')]
    public function index(Request $request, 
    UserAuthenticatorInterface $userAuthenticator,
    AppCustomAuthenticator $authenticator,
    UserPasswordHasherInterface $userPasswordHasher, 
    EntityManagerInterface $entityManager): Response
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


            return $this->redirectToRoute('app_dashboard');

           
        }

        return $this->render('employed/index.html.twig', [
            'employedForm' => $form->createView(),
            
        ]);
    }
}
