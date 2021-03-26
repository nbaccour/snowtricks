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
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(
        TrickRepository $trickRepository,
        ImageRepository $imageRepository,
        PaginatorInterface $paginator,
        Request $request
    ) {

        $user = $this->getUser();
        if ($user) {
            return $this->render('/user/mytricks.html.twig', ['tricks' => $user->getTrick()]);
        }

        $tricks = $trickRepository->findBy([], ['id' => 'DESC']);

        $trickslist = $paginator->paginate(
            $tricks, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );

        return $this->render("home.html.twig", ['tricks' => $trickslist]);
    }
}