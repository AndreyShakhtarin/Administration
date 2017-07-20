<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 6:26 PM
 */

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Entity\User;

class DefaultControllerTest extends WebTestCase
{

    private $em;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

    }

    public function testShows( $page = 0, $sort = 'name', $tag = null )
    {

        $client = static::createClient( );

        //      check permission for any user
        $crawler = $client->request('GET', "/users/edit/$page/$sort/");
        $this->assertEquals( 302, $client->getResponse()->getStatusCode());

        //      create Super Admin
        $this->createAdmin();
        $admin = $this->getAdmin();

        //      login for admin
        $crawler = $client->request( 'GET', "/");
        $client = $this->login( $admin, $client, $crawler );

        //      check wish permission Super Admin
        $crawler = $client->request('GET', "/users/edit/$page/$sort/");
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        $_token = $admin->getConfirmationToken();

        //    delete admin from database
        $this->removedUser( $admin );
    }

    public function testShow( $token = 'token' )
    {
        $admin = $this->quickTest( 'show' );

        //      delete admin from database
        $this->removedUser( $admin );
    }

    public function testCreate()
    {
        $client = static::createClient();

        //      check permission for any user
        $crawler = $client->request( 'GET', "/users/create/");
        $this->assertEquals( 302, $client->getResponse()->getStatusCode());

        //      create Super Admin
        $this->createAdmin();
        $admin = $this->getAdmin();

        //      login for admin
        $crawler = $client->request( 'GET', "/");
        $client = $this->login( $admin, $client, $crawler );

        //      check for admin
        $crawler = $client->request( 'GET', "/users/create/");
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        //      submit form
        $user = array(
            'fos_user_registration_form[username]'              => md5(uniqid('adminTest') . rand(0, 9999)),
            'fos_user_registration_form[name]'                  => md5(uniqid('adminTest') . rand(0, 9999)),
            'fos_user_registration_form[surname]'               =>  md5(uniqid('adminTest') . rand(0, 9999)),
            'fos_user_registration_form[email]'                 =>  md5(uniqid('adminTest') . rand(0, 9999)).'@mail.com',
            'fos_user_registration_form[plainPassword][first]'  =>  'adminTest',
            'fos_user_registration_form[plainPassword][second]' =>  'adminTest',
            'fos_user_registration_form[gender]'                =>  0,
            'fos_user_registration_form[birthday][month]'       =>  1,
            'fos_user_registration_form[birthday][day]'         => 1,
            'fos_user_registration_form[birthday][year]'        => 2012,
            "fos_user_registration_form[_token]"                => "nY1d2DFfU79LcMF3bMKc79_54X8EO0dV_rGOLhEQmn0"
        );
        $form = $crawler->selectButton('Create')->form();
        $crawler = $client->submit($form, $user);

        //      delete Super Admin from db
        $this->removedUser( $admin );
    }

    public function testEdit(  $token = 'token' )
    {
        $admin = $this->quickTest( 'edit' );

        //     delete Super Admin from db
        $this->removedUser( $admin );
    }

    public function testUpdate( $token = 'token' )
    {
        $admin = $this->quickTest( "update", true );
        //      delete Super Admin from db
        $this->removedUser( $admin );
    }

    public function testDelete( $token = 'token' )
    {
        $admin = $this->quickTest( 'delete', true );
        //      delete Super Admin from db
        $this->removedUser( $admin );
    }

    public function testErrorAdmin()
    {
        $client = static::createClient( );

        //      create Super Admin
        $this->createAdmin();
        $admin = $this->getAdmin();
        $_token = $admin->getConfirmationToken();

        //      check access for any user
        $crawler = $client->request( 'GET', "/users/error_admin/" );
        $this->assertEquals( 302, $client->getResponse()->getStatusCode());

        //      login for admin
        $crawler = $client->request( 'GET', "/" );
        $client = $this->login( $admin, $client, $crawler );

        //      check permission for admin
        $crawler = $client->request( 'GET', "/users/error_admin/" );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        //      delete Super Admin from db
        $this->removedUser( $admin );
    }

    private function quickTest( $path, $notFound = false )
    {
        $client = static::createClient( );
        //      create Super Admin
        $this->createAdmin();
        $admin = $this->getAdmin();
        $token = $admin->getConfirmationToken();

        //      check access for any user
        $crawler = $client->request( 'GET', "/users/$path/$token/" );
        $this->assertEquals( 302, $client->getResponse()->getStatusCode());

        //      login for admin
        $crawler = $client->request( 'GET', "/");
        $client = $this->login( $admin, $client, $crawler );

        if ( $notFound )
        {
            //      check for not found user
            $crawler = $client->request( 'GET', "/users/$path/token/" );
            $this->assertTrue( $crawler->filter( 'html:contains( "User not found." )' )->count( ) == 1 );

            //      check access user
            $_token = $this->getUser()[ 'token' ];
            $crawler = $client->request( 'GET', "/users/$path/$_token/" );
            $this->assertEquals( 200, $client->getResponse()->getStatusCode());

            //      check permission for admin
            $crawler = $client->request( 'GET', "/users/$path/$token/" );
            $this->assertEquals( 302, $client->getResponse()->getStatusCode());
        }
        else
        {
            //      check permission for admin
            $crawler = $client->request( 'GET', "/users/$path/$token/" );
            $this->assertEquals( 200, $client->getResponse()->getStatusCode());
        }

        return $admin;
    }


    public function login( $admin, $client, $crawler )
    {
        $_admin = array(
            '_username' => $admin->getUsername(),
            '_password' => 111
        );
        $form = $crawler->selectButton( 'Login' )->form($_admin);
        $crawler = $client->submit($form);

        return $client;
    }
    public function createAdmin()
    {
        $admin = new User();
        $admin->setName( 'adminTest' );
        $admin->setUsername( 'adminTest' );
        $admin->setSurname( 'adminTest' );
        $admin->setBirthday( new \DateTime( date( 'Y-m-d', 11111111111 ) ) );
        $admin->setSuperAdmin( true );
        $admin->setEnabled( 1 );
        $admin->setGender( 0 );
        $admin->setEmail(  'admintest@gmail.com' );
        $admin->setConfirmationToken( md5(uniqid( $admin->getEmail( ) ) . rand(1,99999) ) );
        $admin->setPassword( '$2y$13$/bDqcNr/MFKeAawNhlnM/u110urHn8Pbg5FdVL/hTXiMJm4k0m/YO' );
        $this->em->persist( $admin );
        $this->em->flush();
    }

    public function removedUser( $user )
    {
        $this->em->remove( $user );
        $this->em->flush( $user );
    }

    public function getUser()
    {
        $user = $this->em->createQuery( 'Select u FROM UserBundle:User u' )
            ->setMaxResults( 1 )
            ->getResult( );

        $token = $user[ 0 ]->getConfirmationToken( );
        $name = $user[ 0 ]->getName( );

        $_user = array( 'name' => $name, 'token' => $token );
        return $_user;
    }

    public function getAdmin()
    {
        $admin = $this->em->getRepository( 'UserBundle:User' )->findOneByName( 'adminTest' );

        return $admin;
    }



    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();

    }

}