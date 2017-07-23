<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/1/17
 * Time: 12:48 PM
 */

namespace UserBundle\DataFixture\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Entity\Users;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * load in data base users
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager )
    {
        $users_male = array( 'Noah', 'Liam', 'William', 'Mason', 'James', 'Benjamin', 'Jacob', 'Michael',  'Elijah', 'Ethan', 'Alexander', 'Oliver', 'Lucas' );
        $users_female = array( 'Emma', 'Olivia', 'Ava', 'Sophia', 'Isabella', 'Mia', 'Charlotte', 'Abigail', 'Emily', 'Harper', 'Amelia', 'Evelyn', 'Elizabeth' );
        $users_surname = array( 'Smith', 'Jones', 'Brown', 'Johnson', 'Williams', 'Miller', 'Taylor', 'Wilson', 'Davis', 'White' );
        $users = array( 0 => $users_female, 1 => $users_male );
        $countries =array(
            'Germany' => array( 'Berlin', 'Coburg', 'Ansbach', 'Potsdam', 'Giessen', 'Cuxhaven', 'Oldenburg', 'Rostock', 'Bad Godesberg', 'Essen'),
            'U.K.'    => array( 'Aberdeen', 'Birmingham', 'Carlisle', 'Leicester', 'Newry', 'Lincoln', 'Salisbury', 'Truro', 'St Albans', 'Preston'),
            'Italy'   => array( 'Rome', 'Genoa', 'Bologna', 'Taranto', 'Perugia', 'Ravenna'),
            'U.S.A.'  => array( 'Alabama', 'California', 'Florida', 'Kansas', 'Boston', 'New York', 'Raleigh', 'Austin'),
            'France'  => array( 'Paris', 'Parisot', 'Moselle', 'Loire-Atlantique', 'Morbihan', 'Pyrenees-Atlantiques'),
            'Belgium' => array( 'Antwerp', 'Mons', 'Brugge', 'Mechelen', 'Verviers', 'Turnhout', 'Lokeren'),
            'Spain'   => array( 'Montilla', 'Cabra', 'Guadix', 'Melilla', 'Martos', 'Oviedo', 'Villaviciosa'),
        );

        $statuses = array( 'married',  'not married' );

        $admin = $this->container->get('admin');
        if ( empty($admin))
        {
            throw new \Exception('service with id current_admin not found');
        }

        $professions = array( 'engineer', 'teacher', 'doctor', 'handyman', 'on welfare', 'retired', 'student', 'designer', 'developer', 'musikanten' );
        for ( $i = 0; $i < 40; $i++)
        {
            $user = new Users();

            $user->setUser( $admin );

            $rand = rand( 0, 1 );
            $_users = $users[ $rand ];
            $key_n = array_rand( $_users, 1 );
            $key_l = array_rand( $users_surname, 1 );

            $country_r = array_rand( $countries, 1);
            $city_r = array_rand( $countries[$country_r], 1);

            $user->setName( $_users[$key_n] );
            $user->setSurname( $users_surname[$key_l]);

            $time_r = rand( -199999999, 1400000000 );
            $born = date('Y-m-d', $time_r);
            $date = new \DateTime($born);
            $user->setBorn( $date );

            $user->setToken();

            $country = array_rand( $countries, 1 );
            $city_r = array_rand( $countries[ $country ], 1 );
            $user->setCountry( $country ) ;
            $user->setCity( $countries[ $country ][ $city_r ] );

            $status_r = array_rand( $statuses, 1 );
            $user->setStatus( $statuses[ $status_r ] );
            $profession_r = array_rand( $professions, 1);
            $user->setProfession( $professions[ $profession_r ] );
            $user->setGender( $rand );
            $user->setEmail( strtolower( $user->getName() ) . "_" . strtolower( $user->getSurname() ) . date( 'Y', $time_r ) . '@mail.com' );
            $user->setUser($admin);
            $manager->persist( $user );
            $manager->flush();
        }
    }
}