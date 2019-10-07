<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    private $data = array();

    /**
     * formulaire de connexion utilisateur
     *
     * @Route("/login", name="account_login")
     *
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $this->data['hasError'] = $utils->getLastAuthenticationError();
        $this->data['username'] = $utils->getLastUsername();

        return $this->render('account/login.html.twig', $this->data);
    }

    /**
     * se déconnecter
     *
     * @Route("/logout", name="account_logout")
     *
     * @return void
     */
    public function logout()
    {

    }

    /**
     * le formulaire d'inscription
     *
     * @Route("/register", name="account_register")
     *
     * @return Response
     */
    public function register(ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getHash());
            $user->setHash($hash);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute('account_login');
        }

        $this->data['form'] = $form->createView();
        return $this->render('account/registration.html.twig', $this->data);

    }

    /**
     * formulaire de modification utilisateur
     *
     * @Route("/account/update", name="account_profile")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function profile(Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre compte a bien été modifié.'
            );

        }

        $this->data['form'] = $form->createView();
        return $this->render('account/profile.html.twig', $this->data);
    }

    /**
     * formulaire de modification mot de passe
     *
     * @Route("/account/password", name="account_password")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function updatePassword(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);
                $user->setHash($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié.'
                );
            } else {
                $form->get('oldPassword')->addError(new FormError('Le mot de passe est incorrect.'));
            }
        }

        $this->data['form'] = $form->createView();
        return $this->render('account/password.html.twig', $this->data);
    }

    /**
     * voir mon profil utilisateur
     *
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function myAccount()
    {
        $this->data['user'] = $this->getUser();
        return $this->render('user/index.html.twig', $this->data);
    }

    /**
     * Permet d'afficher la liste des réservations faites pas l'utilisateur
     *
     * @Route("/account/bookings", name="account_bookings")
     * @return Response
     */
    public function bookings()
    {
        return $this->render('booking/bookings.html.twig');
    }
}
