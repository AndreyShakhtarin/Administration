<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 6:26 PM
 */

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

class DefaultControllerTest extends WebTestCase
{

    private $em;
    private $application;
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

//        $this->application = new Application(static::$kernel);


//        $command = new DropDatabaseDoctrineCommand();
//        $this->application->add($command);
//        $input = new ArrayInput(array(
//            'command' => 'doctrine:database:drop',
//            '--force' => true
//        ));
//        $command->run($input, new NullOutput());
//
//
//        $connection = $this->application->getKernel()->getContainer()->get('doctrine')->getConnection();
//        if ($connection->isConnected()) {
//            $connection->close();
//        }
//
//        $command = new CreateDatabaseDoctrineCommand();
//        $this->application->add($command);
//        $input = new ArrayInput(array(
//            'command' => 'doctrine:database:create',
//        ));
//        $command->run($input, new NullOutput());
//
//
//        $command = new CreateSchemaDoctrineCommand();
//        $this->application->add($command);
//        $input = new ArrayInput(array(
//            'command' => 'doctrine:schema:create',
//        ));
//        $command->run($input, new NullOutput());


        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

//        $client = static::createClient();
//        $loader = new \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader($client->getContainer());
//        $loader->loadFromDirectory(static::$kernel->locateResource('@UserBundle/DataFixtures/ORM'));
//        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->em);
//        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->em, $purger);
//        $executor->execute($loader->getFixtures());
    }

    public function testShows( $page = 0, $sort = 'name', $tag = null )
    {

        $client = static::createClient( );
        $crawler = $client->request('GET', "/users/edit/$page/$sort/");

        $this->assertEquals( 200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('.users th.username:contains("username")')->count() == 0);

        $this->assertContains(
            "Show",
            $client->getResponse()->getContent()
        );

        $users = $this->em->createQuery( 'Select u FROM UserBundle:User u')->getResult();
        $_users = $this->em->getRepository( 'UserBundle:User')->findByAll( $page, $sort, $tag );
        $this->assertCount(count($users), $_users['all_users']);


    }

    public function testShow( $token = '' )
    {
        $client = static::createClient();

        $user = $this->em->createQuery( 'Select u FROM UserBundle:User u')->setMaxResults( 1 )->getResult();

        $token = $this->getUser( )['token'];
        $name  = $this->getUser( )['name'];


        $crawler = $client->request( 'GET', "/users/show/$token/");

        $this->assertEquals(
            1,
            $crawler->filter("html:contains($name)")->count()
        );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

    }

    public function testCreate()
    {
        $client = static::createClient();
        $crawler = $client->request( 'GET', "/users/create/");

        $this->assertTrue($crawler->filter('h3')->count() > 0);
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

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

    }

    public function testEdit(  $token = 'token' )
    {
        $client = static::createClient( );
        $_token = $this->getUser( )[ 'token' ];

        $crawler = $client->request( 'GET', "/users/edit/$_token/" );
        $this->assertTrue( $crawler->filter( 'h3' )->count( ) > 0 );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        $crawler = $client->request( 'GET', "/users/edit/$token/" );
        $this->assertTrue( $crawler->filter( 'html:contains( "User not found." )' )->count( ) == 1 );

        $_tokenAdmin = $this->getAdmin();
        $crawler = $client->request( 'GET', "/users/$_tokenAdmin/update/" );

    }

    public function testUpdate( $token = 'token' )
    {
        $client = static::createClient( );
        $client2 = static::createClient( );
        $_token = $this->getUser( )[ 'token' ];

        $crawler = $client->request( 'GET', "/users/$_token/update/" );

        $this->assertTrue( $crawler->filter( 'h3' )->count( ) > 0 );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        $crawler = $client->request( 'GET', "/users/$token/update/" );
        $this->assertTrue( $crawler->filter( 'html:contains( "User not found." )' )->count( ) == 1 );

        $_tokenAdmin = $this->getAdmin();
        $crawler = $client->request( 'GET', "/users/$_tokenAdmin/update/" );
    }

    public function testDelete( $token = 'token' )
    {
        $client = static::createClient( );
        $_token = $this->getUser( )[ 'token' ];

        $crawler = $client->request( 'GET', "/users/$_token/delete/" );
        $this->assertTrue( $crawler->filter( 'h3' )->count( ) > 0 );

        $crawler = $client->request( 'GET', "/users/$token/delete/" );
        $this->assertTrue( $crawler->filter( 'html:contains( "User not found." )' )->count( ) == 1 );

        $_tokenAdmin = $this->getAdmin();
        $crawler = $client->request( 'GET', "/users/$_tokenAdmin/delete/" );
    }

    public function testErrorAdmin()
    {
        $client = static::createClient( );
        $crawler = $client->request( 'GET', "/users/error_admin/" );
        $this->assertTrue( $crawler->filter( 'h3' )->count( ) > 0 );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());
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
        $users = $this->em->getRepository( 'UserBundle:User' )->findAll( );

        foreach ( $users as $key=>$user )
        {
            $roles = $user->getRoles();
            foreach ( $roles as $key=>$role)
            if ( $role == 'ROLE_SUPER_ADMIN' )
            {
                $admin = $user;
            }
        }
        $token =  $admin->getConfirmationToken( );
        return $token;
    }



    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();

    }

}