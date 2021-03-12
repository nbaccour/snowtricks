<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/{category_slug}/{slug}", name="trick_show")
     */
    public function show($slug, TrickRepository $trickRepository): Response
    {

        $trick = $trickRepository->findOneBy(['slug' => $slug]);
//        dd($trick);

        return $this->render('trick/show.html.twig', ['trick' => $trick]);
    }
}
