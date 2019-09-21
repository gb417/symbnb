<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $data=array();

    /**
     * @Route("/user/{slug}", name="user_read")
     */
    public function index(User $user)
    {
        $this->data['user']=$user;

        return $this->render('user/index.html.twig', $this->data);
    }
}
