<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Image;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickController extends AbstractController
{


    protected $trickRepository;
    protected $manager;

    public function __construct(TrickRepository $trickRepository, EntityManagerInterface $manager)
    {
        $this->trickRepository = $trickRepository;
        $this->manager = $manager;
    }


    /**
     * @Route("/{category_slug}/{slug}", name="trick_show", priority=-1)
     */
    public function show($slug): Response
    {

        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);

        return $this->render('trick/show.html.twig', ['trick' => $trick]);
    }

    /**
     * @Route("/mytricks", name="trick_mytricks")
     */
    public function mytricks()
    {

        $user = $this->getUser();
//        dd($user->getTrick());
        return $this->render('/user/mytricks.html.twig', ['tricks' => $user->getTrick()]);


    }

    /**
     * @Route("/createtrick", name="trick_create")
     */
    public function create(Request $request, SluggerInterface $slugger)
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

                $bddFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
//                $pathFilename = 'uploads/trick/' . $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
                $pathFilename = 'uploads/trick/' . $bddFilename;

                try {
                    $image->move(
                        $this->getParameter('imgTrick_directory'),
                        $pathFilename
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
                $img->setName($bddFilename)
                    ->setTrick($trick);

                $trick->addImage($img)
                    ->setMainImage($img);

                $this->manager->persist($img);

            }
            $trick->setSlug($slugger->slug($trick->getName()))
                ->setUser($user);

            $this->manager->persist($trick);

            $this->manager->flush();
            $this->addFlash('success', "Votre figure a été ajoutée");
        }


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
     * @Route("/deletemytrick/{id}", name="trick_delete", requirements={"id": "\d+"})
     */
    public function delete($id)
    {
        $trick = $this->trickRepository->find($id);

        if (!$trick) {
            throw $this->createNotFoundException("La figure $id n'existe pas");
        }

        $fileSystem = new Filesystem();

        foreach($trick->getImage() as $image)
        {
            $fileSystem->remove('/uploads/trick/' . $image->getName());
        }
        $imagesTrick = $trick->getImage();

        foreach ($imagesTrick as $image) {
            $this->manager->remove($image);

        }
//dd($trick->getImage());
//        $this->manager->remove($trick);
        $this->manager->flush();

        $this->addFlash("success", "La figure a bien été suprimée ");

        return $this->redirectToRoute("mytricks");
    }

//    /**
//     * @param int $id
//     */
//    public function remove(int $id)
//    {
//        $cart = $this->getCart();
//
//        unset($cart[$id]);
//
//        $this->saveCart($cart);
//    }
}
