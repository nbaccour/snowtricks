<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Image;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickController extends AbstractController
{
    /**
     * @Route("/{category_slug}/{slug}", name="trick_show", priority=-1)
     */
    public function show($slug, TrickRepository $trickRepository): Response
    {
        $image = new Image();
        $imageDir = $image->getUploadDir();
        $trick = $trickRepository->findOneBy(['slug' => $slug]);

        return $this->render('trick/show.html.twig', ['trick' => $trick, 'imageDir' => $imageDir]);
    }

    /**
     * @Route("/mytricks", name="trick_mytricks")
     */
    public function mytricks()
    {
        $image = new Image();
        $imageDir = $image->getUploadDir();

        $user = $this->getUser();
        return $this->render('/user/mytricks.html.twig', ['tricks' => $user->getTrick(), 'imageDir' => $imageDir]);


    }

    /**
     * @Route("/createtrick", name="trick_create")
     */
    public function create(Request $request, EntityManagerInterface $manager, SluggerInterface $slugger)
    {
        $trick = new Trick();
        $user = $this->getUser();
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('image')->getData();
            foreach ($images as $image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = 'uploads/trick/' . $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('imgTrick_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash(
                        'error',
                        "Erreur téléchargement de votre photo"
                    );
                }
                // On crée l'image dans la base de données
                $img = new Image();
                $img->setName($newFilename)
                    ->setTrick($trick);
//
                $trick->addImage($img)
                    ->setMainImage($img);

                $manager->persist($img);

            }
            $trick->setSlug($slugger->slug($trick->getName()))
                ->setUser($user);

            $manager->persist($trick);

            $manager->flush();
            $this->addFlash('success', "Votre figure a été ajoutée");
        }


//        if ($form->isSubmitted() && $form->isValid()) {
//
//            // On récupère les images transmises
//            $images = $form->get('image')->getData();
//
//            // On boucle sur les images
//            foreach ($images as $image) {
//                // On génère un nouveau nom de fichier
////                $newFilename = md5(uniqid()) . '.' . $image->guessExtension();
////                $newFilename = md5(uniqid());
//                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
//                $safeFilename = $slugger->slug($originalFilename);
//                $newFilename = $safeFilename . '.' . $image->guessExtension();
////                $newFilename = 'uploads/trick/' . $safeFilename . '.' . $image->guessExtension();
////                  dd($image);
//                $image->move(
//                    $this->getParameter('imgTrick_directory'),
//                    $newFilename
//                );
//                // On crée l'image dans la base de données
//                $img = new Image();
//                $img->setName($newFilename)
//                    ->setExtension($image->guessExtension())
//                    ->setTrick($trick);
//                $trick->addImage($img)
//                    ->setMainImage($img);
//
//                $manager->persist($img);
//            }
//            $trick->setSlug($slugger->slug($trick->getName()))
//                ->setUser($user);
//
//            $manager->persist($trick);
//            $manager->flush();
//            $this->addFlash('success', "Votre figure a été ajoutée");
//
//        }

        return $this->render('/trick/create.html.twig', ['formView' => $form->createView(), 'trick' => $trick]);
    }

    /**
     * @Route("/modifymytrick/{id}", name="trick_modify")
     */
    public function modify($id)
    {
        dd('modify');
    }

    /**
     * @Route("/deletemytrick/{id}", name="trick_delete")
     */
    public function delete($id)
    {
        dd('delete');
    }
}
