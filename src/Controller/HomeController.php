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
        return $this->render('full_page_base.html.twig', [
            'page_title' => 'Propy Admin',
        ]);
    }
}
