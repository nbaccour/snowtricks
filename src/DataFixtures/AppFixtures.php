<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
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

        foreach ($tricksName as $name) {

            $trick = new Trick();
            $trick->setName($name)
                ->setDescription($faker->paragraph(5))
                ->setSlug(strtolower($this->slugger->slug($trick->getName())))
                ->setUser($faker->randomElement($aUser))
                ->setCategory($faker->randomElement($aCategory));

            $aImage = [];
            // 3 Image by Trick
            for ($k = 1; $k < 4; $k++) {
                $image = new Image();
                $image->setName('uploads/trick/img' . $faker->numberBetween(1, 39) . '.jpg')
                    ->setTrick($trick);

                $manager->persist($image);
                $aImage[] = $image;
            }

            $trick->setMainImage($faker->randomElement($aImage));
//            $manager->persist($trick);
            $manager->persist($trick);

        }


        $manager->flush();
    }
}
