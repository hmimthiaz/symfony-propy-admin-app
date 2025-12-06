<?php

namespace App\Controller;

use App\Classes\Base\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'index')]
    public function indexAction(
        Request $request,
    ): Response {
        return $this->render('layouts/full_base.html.twig', [
            'page_title' => 'Home',
        ]);
    }

    #[Route('/login', name: 'login')]
    public function loginAction(
        Request $request,
    ): Response {
        return $this->render('layouts/empty_base.html.twig', [
            'page_title' => 'Login',
        ]);
    }
}
