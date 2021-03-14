<?php

namespace App\Controller;


use App\Form\PictureType;
use App\Form\ProfileType;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager, SluggerInterface $slugger)
    {
        $user = $this->getUser();
        $formPicture = $this->createForm(PictureType::class, $user);
        $formPicture->handleRequest($request);

        if ($formPicture->isSubmitted() && $formPicture->isValid()) {

            $photoFile = $formPicture->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
//                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                $newFilename = 'uploads/user/' . $safeFilename . '.' . $photoFile->guessExtension();
//                dd($photoFile);
                try {
                    $photoFile->move(
//                        $this->getParameter('uploads/user'),
                        $this->getParameter('imgUser_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash(
                        'error',
                        "Erreur téléchargement de votre photo"
                    );
                }
                $user->setPhoto($newFilename);
            }

            $manager->flush();
            $this->addFlash('success', "Votre photo a été modifiée");
        }


        $formResetPwd = $this->createForm(ResetPasswordType::class, [],
            ['action' => $this->generateUrl('user_resetPassword')]);

        return $this->render("/user/profile.html.twig",
            [
                'formProfilView' => $formPicture->createView(),
                'user'           => $user,
                'formPassword'   => $formResetPwd->createView(),
            ]);


    }

    /**
     * @Route("/resetpwd", name="user_resetPassword")
     */
    public function resetPassword(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $manager
    ) {
        $user = $this->getUser();
        $form = $this->createForm(ResetPasswordType::class, $user, ['validation_groups' => 'verif-pwd']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->flush();

            $this->addFlash('success', "Votre mot de passe a été modifié");

        }
        $formPicture = $this->createForm(PictureType::class, $user);
        $formPicture->handleRequest($request);
        return $this->render("/user/profile.html.twig",
            ['formProfilView' => $formPicture->createView(), 'user' => $user, 'formPassword' => $form->createView()]);

    }
}
