<?php
/**
 * Created by PhpStorm.
 * User: msi-n
 * Date: 24/02/2021
 * Time: 00:03
 */

namespace App\Controller;


use App\Entity\Image;
use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(TrickRepository $trickRepository)
    {


        $tricks = $trickRepository->findBy([], [], 6);
        $image = new Image();
        $imageDir = $image->getUploadDir();


        return $this->render("home.html.twig", ['tricks' => $tricks, 'imageDir' => $imageDir]);
    }
}