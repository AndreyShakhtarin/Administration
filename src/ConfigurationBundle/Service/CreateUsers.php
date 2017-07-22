<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/22/17
 * Time: 4:39 PM
 */

namespace ConfigurationBundle\Service;

use  UserBundle\Entity\User;

class CreateUsers
{

    /**
     * Create adn load users to database
     */
    public function createUsers( $manager )
    {
        $users_male = array( 'Noah', 'Liam', 'William', 'Mason', 'James', 'Benjamin', 'Jacob', 'Michael',  'Elijah', 'Ethan', 'Alexander', 'Oliver', 'Lucas' );
        $users_female = array( 'Emma', 'Olivia', 'Ava', 'Sophia', 'Isabella', 'Mia', 'Charlotte', 'Abigail', 'Emily', 'Harper', 'Amelia', 'Evelyn', 'Elizabeth' );
        $users_surname = array( 'Smith', 'Jones', 'Brown', 'Johnson', 'Williams', 'Miller', 'Taylor', 'Wilson', 'Davis', 'White' );
        $users = array( 0 => $users_female, 1 => $users_male );
        for ( $i = 0; $i < 40; $i++)
        {
            $user = new User();
            $rand = rand( 0, 1 );
            $_users = $users[ $rand ];
            $key_n = array_rand( $_users, 1 );
            $key_l = array_rand( $users_surname, 1 );

            $user->setName( $_users[$key_n] );
            $user->setUsername( $_users[$key_n] . md5($_users[$key_n] . rand(1,99999)));
            $user->setSurname( $users_surname[$key_l]);
            $time_r = rand( -199999999, 1400000000 );
            $born = date('Y-m-d', $time_r);
            $date = new \DateTime($born);
            $user->setToken();
            $user->setBirthday( $date );
            $user->setEnabled( 1 );
            $user->setGender( $rand );
            $user->setEmail( strtolower( $_users[$key_n] ). md5($_users[$key_n] . rand(0, 999999))  . '@gmail.com' );
            $user->setPassword( rand( 1, 1000 ));

            $manager->persist( $user );
            $manager->flush();
        }
    }
}