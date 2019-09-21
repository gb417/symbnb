<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Repository\AdRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    private $data = array();

    /**
     * Liste les annonces
     *
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {
        $ads = array_reverse($repo->findAll());

        $this->data['ads'] = $ads;

        return $this->render('ad/index.html.twig', $this->data);
    }

    /**
     * Pour créer une annonce
     *
     * @Route("/ads/create", name="ads_create")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     *
     */
    public function create(Request $request, ObjectManager $manager)
    {
        $ad = new Ad();
        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $ad->setAuthor($this->getUser());

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée."
            );

            $this->data['slug'] = $ad->getSlug();
            return $this->redirectToRoute('ads_read', $this->data);
        }

        $this->data['form'] = $form->createView();

        return $this->render('ad/create.html.twig', $this->data);
    }

    /**
     * Juste une annonce
     *
     * @Route("/ads/{slug}/read", name="ads_read")
     *
     * @return Response
     *
     */
    public function read(Ad $ad)
    {
        $this->data['ad'] = $ad;

        return $this->render('ad/read.html.twig', $this->data);
    }

    /**
     * Editer une annonce
     *
     * @Route("/ads/{slug}/update", name="ads_update")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas !")
     *
     * @return Response
     *
     */
    public function update(Ad $ad, Request $request, ObjectManager $manager)
    {
        $form = $this->createForm(AdType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> ont bien été enregistrées."
            );

            $this->data['slug'] = $ad->getSlug();
            return $this->redirectToRoute('ads_read', $this->data);
        }

        $this->data['form'] = $form->createView();
        $this->data['ad'] = $ad;

        return $this->render('ad/update.html.twig', $this->data);
    }

    /**
     * Supprimer une annonce
     *
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas !")
     *
     * @return Response
     *
     */
    public function delete(Ad $ad, Request $request, ObjectManager $manager)
    {
        $manager->remove($ad);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée."
        );

        return $this->redirectToRoute('ads_index');
    }

}
