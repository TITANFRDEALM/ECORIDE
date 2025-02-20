<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmployeeController extends AbstractController
{
    #[Route('/employee', name: 'employee', methods: ['GET'])]
    public function employee() : Response
    {
        return new Response('Bienvenue sur l\'espace admin');
    }
}