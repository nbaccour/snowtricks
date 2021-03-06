<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\ForgotpwdType;
use App\Form\PictureType;
use App\Form\ProfileType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="user_profile")
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos données")
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
                $newFilename = 'uploads/user/' . $safeFilename . '.' . $photoFile->guessExtension();
                try {
                    $photoFile->move(
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
     * @IsGranted("ROLE_USER", message="Vous devez etres connecté pour acceder à vos données")
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

    /**
     * @Route("/forgotpassword", name="user_forgotpassword")
     */
    public function forgotpassword(Request $request, UserRepository $userRepository)
    {


        $form = $this->createForm(ForgotpwdType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailUser = $userRepository->findByEmail($form->getData()['email']);

            if (count($emailUser) === 0) {

                $this->addFlash("warning",
                    "Erreur : Email n'existe pas dans notre base: '" . $form->getData()['email'] . "' ");
                return $this->redirectToRoute("user_forgotpassword");
            } else {

                $this->addFlash("success",
                    "Merci pour l'intérêt que vous portez à SnowTricks !
                Le site est en version de démonstration, l'action que vous souhaitez effectuer n'est pas activée");
                return $this->redirectToRoute("user_forgotpassword");
            }

        }

        return $this->render('security/forgotpassword.html.twig',
            ['formView' => $form->createView()]);
    }
}
