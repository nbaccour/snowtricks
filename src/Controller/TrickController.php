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
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


        }

        return $this->render('/trick/create.html.twig', ['formView' => $form->createView()]);
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
