<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    //USER
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $adminUser = new User();

        $adminUser->setLogin('admin');
        $adminUser->setPassword($this->passwordEncoder->encodePassword(
            $adminUser,
            'admin'
        ));
        $adminUser->setRoles([
            'ROLE_ADMIN'
        ]);
        $manager->persist($adminUser);

        for ($i = 0; $i <= 5; $i++) {
            $user = new User();
            $user->setLogin('user' . $i);
            //the password is equal to the login name.
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'user' . $i
            ));
            $manager->persist($user);
        }
        //COUNTRIES
        if (($countriesFile = fopen(__DIR__ . '/../../data/countriesList.csv', "r")) !== FALSE) {
            //fgetcsv prend chaque ligne du fichier et en fait un tableau. L'index du tableau créé indique donc la colonne.
            while (($data = fgetcsv($countriesFile)) !== FALSE ){
                $country = new Country();
                $country->setName($data[0]);
                $manager->persist($country);
            }

            fclose($countriesFile);
        }

        $manager->flush();
    }
}
