<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Image;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Ad;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("fr-FR");
        $users = array();
        $genres = ['male', 'female'];

        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('Henri')
            ->setLastName('Martin')
            ->setEmail('gbarrois@live.fr')
            ->setIntroduction($faker->sentence())
            ->setDescription('<p>' . implode('</p><p>', $faker->paragraphs(3)) . '</p>')
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->setAvatar('https://tinypng.com/images/social/website.jpg')
            ->addUserRole($adminRole);
        $manager->persist($adminUser);

        //utilisateurs
        for ($i = 0; $i < 50; $i++) {
            $user = new User;
            $genre = $faker->randomElement($genres);
            $avatar = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';
            $hash = $this->encoder->encodePassword($user, 'password');
            switch ($genre) {
                case 'male':
                    $avatar .= 'men/' . $pictureId;
                    break;
                case 'female':
                    $avatar .= 'women/' . $pictureId;
                    break;
            }
            $user->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence())
                ->setDescription('<p>' . implode('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setHash($hash)
                ->setAvatar($avatar);
            $manager->persist($user);
            $users[] = $user;
        }

        //ads
        for ($i = 0; $i < 1000; $i++) {
            $title = $faker->sentence(5);
            $coverImage = $faker->imageUrl(1000, 350);
            $introduction = $faker->paragraph(2);
            $content = '<p>' . implode('</p><p>', $faker->paragraphs(5)) . '</p>';
            $user = $users[random_int(0, count($users) - 1)];

            $ad = new Ad();
            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(random_int(10, 100))
                ->setRooms(random_int(1, 10))
                ->setAuthor($user);

            //images
            for ($j = 0, $jMax = random_int(1, 10); $j < $jMax; $j++) {
                $image = new Image();
                $image->setCaption($faker->sentence())
                    ->setUrl($faker->imageUrl())
                    ->setAd($ad);

                $manager->persist($image);
            }


            // gestion des réservations
            for ($j = 1, $jMax = random_int(0, 10); $j <= $jMax; $j++) {
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3 months');

                $duration = random_int(3, 10);

                $endDate = clone $startDate;
                $endDate->modify("+$duration days");

                $amount = $ad->getPrice() * $duration;

                $booker = $users[random_int(0, count($users) - 1)];

                $comment = $faker->paragraph();

                $booking->setBooker($booker)
                    ->setAd($ad)
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setCreatedAt($createdAt)
                    ->setAmount($amount)
                    ->setComment($comment);

                $manager->persist($booking);
            }


            $manager->persist($ad);
        }


        $manager->flush();
    }
}
