<?php

namespace App\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param LoggerInterface $logger
     * @return
     */
    public function index(LoggerInterface $logger)
    {
        $logger->info('Rendering home page.');

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/error", name="home.error")
     * @return
     * @throws Exception
     */
    public function error(LoggerInterface $logger)
    {
        $logger->info('Throwing an error.');

        throw new Exception('It went wrong!');

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
