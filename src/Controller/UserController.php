<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name:'user', methods:'GET')]
    public function user() : Response
    {
        return new Response('Bienvenue sur ton espace perso');
    }
}