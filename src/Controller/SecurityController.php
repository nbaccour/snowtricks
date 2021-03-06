<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SecurityController extends AbstractController
{

    /**
     * @Route("registration", name="security_registration")
     */
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ) {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user, ['validation_groups' => 'verif-pwd']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $password = $encoder->encodePassword($user, $user->getPassword());


            $user->setPassword($password);
            $user->setPhoto('uploads/user/avatar.png');

            $manager->persist($user);
            $manager->flush();
//message : Compte crée avec succès ! Veuillez valider votre compte via le mail qui vous a été envoyé pour pouvoir vous connecter !
            $this->addFlash(
                'success',
                "Compte crée avec succès ! "
            );

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig',
            ['formView' => $form->createView()]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $utils): Response
    {

        $form = $this->createForm(LoginType::class, ['email' => $utils->getLastUsername()]);

        return $this->render('security/login.html.twig',
            ['formView' => $form->createView(), 'error' => $utils->getLastAuthenticationError('message')]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {

    }
}
