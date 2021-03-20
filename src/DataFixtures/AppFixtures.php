<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{

    protected $slugger;
    protected $encoder;

    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $encoder)
    {
        $this->slugger = $slugger;
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        $faker = \Faker\Factory::create('FR-fr');

        $admin = new User();
        $hash = $this->encoder->encodePassword($admin, "password");
        $admin->setEmail('admin@gmail.com')
            ->setPassword($hash)
            ->setPrenom('Nizar')
            ->setNom('BACCOUR')
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $aUser = [];
        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            $hash = $this->encoder->encodePassword($user, "password");
            $user->setEmail('user' . $u . '@gmail.com')
                ->setNom($faker->firstName())
                ->setPrenom($faker->lastName())
                ->setPhoto('uploads/user/' . $faker->numberBetween(1,
                        18) . '.jpg')
                ->setPassword($hash);

            $manager->persist($user);
            $aUser[] = $user;
        }


        $categoryName = ['Grabs', 'Rotations', 'Flips', 'Rotations désaxées', 'Slides', 'One foot'];

        $aCategory = [];
        foreach ($categoryName as $name) {

            $category = new Category();
            $category->setName($name)
                ->setSlug(strtolower($this->slugger->slug($category->getName())))
                ->setDescription($faker->paragraph(5));

            $manager->persist($category);
            $aCategory[] = $category;
        }
        $tricksName = ['Mute', 'Indy', '360', '720', 'Backflip', 'Misty', 'Tail slide', 'Method air', 'Backside air'];
        $videoUrl = [
            'https://www.youtube.com/embed/V9xuy-rVj9w',
            'https://www.youtube.com/embed/n0F6hSpxaFc',
            'https://www.youtube.com/embed/NKHYEOAbFyM',
            'https://www.youtube.com/embed/1t9Pb39eW3Y',
            'https://www.youtube.com/embed/NKHYEOAbFyM',
            'https://www.youtube.com/embed/n0F6hSpxaFc',
        ];

        foreach ($aCategory as $categorie) {
            for ($i = 0; $i <= mt_rand(5, 15); $i++) {

                $trick = new Trick();
                $trick->setName($faker->randomElement($tricksName))
                    ->setDescription($faker->paragraph(5))
                    ->setSlug(strtolower($this->slugger->slug($trick->getName())))
                    ->setUser($faker->randomElement($aUser))
                    ->setCategory($categorie);

                $aImage = [];
                // 4 Image by Trick
                for ($k = 1; $k <= 4; $k++) {
                    $image = new Image();
//                    $image->setName('uploads/trick/img' . $faker->numberBetween(1, 39))
                    $image->setName('img' . $faker->numberBetween(1, 39) . '.jpg')
                        ->setTrick($trick);

                    $manager->persist($image);
                    $aImage[] = $image;
                }
                // 3 video by Trick
                for ($v = 1; $v <= 2; $v++) {
                    $video = new Video();
                    $video->setName($faker->randomElement($videoUrl))
                        ->setTrick($trick);

                    $manager->persist($video);
                }

                // 10 comment by Trick
                for ($c = 1; $c <= mt_rand(7, 15); $c++) {
                    $comment = new Comment();
                    $comment->setMessage($faker->paragraph(5))
                        ->setTrick($trick)
                        ->setUser($faker->randomElement($aUser))
                        ->setIsvalid(1)
                        ->setCreateDate($faker->dateTimeBetween('-6 months'));

                    $manager->persist($comment);
                }

                $trick->setMainImage($faker->randomElement($aImage));
                $manager->persist($trick);

            }
        }


        $manager->flush();
    }
}
