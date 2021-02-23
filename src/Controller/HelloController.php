<?php
/**
 * Created by PhpStorm.
 * User: msi-n
 * Date: 23/02/2021
 * Time: 18:39
 */


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HelloController
{
    /**
     * @Route("/hello/{prenom}", name="hello")
     */
    public function hello($prenom)
    {
        return new Response("Bonjour $prenom!");
    }
}