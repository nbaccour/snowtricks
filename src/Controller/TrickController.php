<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Image;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Cocur\Slugify\Slugify;
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
    protected $slugger;

    public function __construct(
        TrickRepository $trickRepository,
        EntityManagerInterface $manager,
        SluggerInterface $slugger
    ) {
        $this->trickRepository = $trickRepository;
        $this->manager = $manager;
        $this->slugger = $slugger;
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
     * @param $form
     * @param $trick
     * @param string $type
     * @return bool
     */
    public function createOrUpdate($form, $trick, string $type = 'create')
    {

        $user = $this->getUser();
        $return = false;
        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('image')->getData();
            foreach ($images as $image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);

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
            $trick->setSlug($this->slugger->slug($trick->getName()))
                ->setUser($user);

            $this->manager->persist($trick);

            $this->manager->flush();
            $msg = ($type === 'create') ? "Votre figure a été ajoutée" : "Votre figure a été modifiée";
            $this->addFlash('success', $msg);
            $return = true;
//            return $this->redirectToRoute("trick_mytricks");
        }
        return $return;
    }

    /**
     * @Route("/createtrick", name="trick_create")
     */
    public function create(Request $request, SluggerInterface $slugger)
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        $create = $this->createOrUpdate($form, $trick);
        if ($create === true) {
            return $this->redirectToRoute("trick_mytricks");
        }


        return $this->render('/trick/create.html.twig', ['formView' => $form->createView(), 'trick' => $trick]);
    }

    /**
     * @Route("/modifymytrick/{id}", name="trick_modify")
     */
    public function modify($id, Request $request)
    {
        $trick = $this->trickRepository->find($id);

        if (!$trick) {
            throw $this->createNotFoundException("La figure $id n'existe pas");
        }
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        $modify = $this->createOrUpdate($form, $trick, 'modify');
        if ($modify === true) {
            return $this->redirectToRoute("trick_mytricks");
        }


        return $this->render('/trick/modify.html.twig', ['formView' => $form->createView(), 'trick' => $trick]);


    }

    /**
     * @Route("/deletepicturetrick/{id}", name="trick_delte_picture")
     */
    public function deletePicture()
    {
        dd('delete image');
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

        $filesystem = new Filesystem();

        foreach ($trick->getImage() as $image) {


            try {
                $filesystem->remove('/uploads/trick/' . $image->getName());

            } catch (IOExceptionInterface $exception) {
                $this->addFlash(
                    'warning',
                    "Erreur sur la suppression de la photo"
                );
            }
        }
        $imagesTrick = $trick->getImage();

        foreach ($imagesTrick as $image) {
            $this->manager->remove($image);

        }
        $this->manager->remove($trick);
        $this->manager->flush();

        $this->addFlash("warning", "La figure a bien été suprimée ");

        return $this->redirectToRoute("trick_mytricks");
    }

}
