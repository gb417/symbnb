<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    private $data=array();

    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function book(Ad $ad, Request $request, ObjectManager $manager)
    {
        $booking=new Booking();
        $form=$this->createForm(BookingType::class,$booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $booking->setBooker($user);
            $booking->setAd($ad);

            // si date non disponible -> erreur
            if(!$booking->isBookableDates())
            {
                $this->addFlash(
                    'warning',
                    'Les dates que vous avez choisi ne peuvent pas être réservées : elles sont déjà prises.'
                );
            }else{
                // ou enregistrement
                $manager->persist($booking);
                $manager->flush();

                $this->data['id']=$booking->getId();
                $this->data['withAlert']=true;
                return $this->redirectToRoute('booking_show',$this->data);
            }
        }

        $this->data['ad']=$ad;
        $this->data['form']=$form->createView();

        return $this->render('booking/book.html.twig', $this->data);
    }

    /**
     *
     * Permet d'afficher la page d'une réservation
     *
     * @Route("/booking/{id}", name="booking_show")
     *
     * @return Response
     */
    public function show(Booking $booking)
    {
        $this->data['booking']=$booking;
        return $this->render('booking/show.html.twig', $this->data);
    }
}
