<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 11:28 AM
 */

namespace ConfigurationBundle\Tests\Controller;

use ConfigurationBundle\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use UserBundle\Entity\User;

class DefaultControllerTest extends WebTestCase
{

    private $em;
    private $application;
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->application = new Application(static::$kernel);
    }

    public function testWelcome( )
    {

        $this->checkDB( 'GET', '/' );

        $client = static::createClient( );
        $crawler = $client->request( 'GET', "/" );

        $this->assertEquals( 200, $client->getResponse()->getStatusCode());
    }

    public function testDataBaseConfig()
    {

        $client = $this->checkDB( 'GET', '/config/parameters/for/database' );

        $crawler = $client->request( 'GET', '/config/parameters/for/database' );
        $this->assertEquals( 500, $client->getResponse()->getStatusCode());

        $crawler = $client->request( 'GET', '/' );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        $this->assertTrue( $crawler->filter('html:contains("Welcome")')->count() > 0 );
        $link = $crawler->selectLink('Home')->link();
        $client->click($link);
    }

    public function testAdminAction()
    {
        $this->dropDB();
        $this->closeConnect();

        $client = static::createClient();
        $crawler = $client->request( 'GET', '/config/parameter/for/admin' );
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
        $form = $crawler->selectButton('Next')->form();
        $crawler = $client->submit($form, $user);
        $this->setUp();
        $this->createAdmin();
        $this->closeConnect();
        $crawler = $client->request( 'GET', '/config/parameter/for/admin' );
        $this->assertEquals( 500, $client->getResponse()->getStatusCode());

        $crawler = $client->request( 'GET', '/config/parameters/for/database' );
        $this->assertEquals( 500, $client->getResponse()->getStatusCode());
    }

    public function testConfirmed()
    {
        $client = static::createClient();
        $crawler = $client->request( 'GET', '/all/parameter/was/confirmed' );
        $this->assertEquals( 500, $client->getResponse()->getStatusCode());
    }

    public function testReplacer( $field = null , $parameter = 'n_a' )
    {
        $dc = new DefaultController();
        $this->assertEquals( 'n a', $dc->replacer( $field, $parameter ) );
    }

    public function testIssetConfig( $parameter = null )
    {
        $dc = new DefaultController();
        $this->assertEquals( $parameter, $dc->issetConfig( $parameter ) );
    }


//    public function testSetUp()
//    {
//        $dc = new DefaultController;
//        $this->assertTrue( true, $dc->setUp( ) );
//    }

    public function checkDB( $method, $path )
    {
        $this->dropDB();
        $this->createDB();
        $this->closeConnect();

        $client = static::createClient();
        $crawler = $client->request( $method, $path );
        $this->assertEquals( 200, $client->getResponse()->getStatusCode());

        $this->setUp();
        $this->createSchemeDB();
        $this->createAdmin();
        $this->closeConnect();

        return $client;
    }

    public function dropDB()
    {


        $command = new DropDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:drop',
            '--force' => true
        ));
        $command->run($input, new NullOutput());
    }

    public function closeConnect()
    {

        $connection = $this->application->getKernel()->getContainer()->get('doctrine')->getConnection();
        if ($connection->isConnected()) {
            $connection->close();
        }
    }

    public function createDB()
    {
        $command = new CreateDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:database:create',
        ));
        $command->run($input, new NullOutput());
    }

    public function createSchemeDB()
    {
        $command = new CreateSchemaDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array(
            'command' => 'doctrine:schema:create',
        ));
        $command->run($input, new NullOutput());
    }

    public function createAdmin()
    {
        $admin = new User();

        $admin->setName( 'adminuser' );
        $admin->setUsername( 'adminuser');
        $admin->setSurname( 'adminuser');
        $admin->setBirthday( new \DateTime( date( 'Y-m-d', 11111111111 )) );
        $admin->setSuperAdmin( true );
        $admin->setEnabled( 1 );
        $admin->setGender( 0 );
        $admin->setEmail(  'adminuser@gmail.com' );
        $admin->setConfirmationToken( md5(uniqid( $admin->getEmail()) . rand(1,99999)) );
        $admin->setPassword( '123' );
        $this->em->persist( $admin );
        $this->em->flush();
    }

//    public function loadFixture()
//    {
//        $client = static::createClient();
//        $loader = new \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader($client->getContainer());
//        $loader->loadFromDirectory(static::$kernel->locateResource('@UserBundle/DataFixtures/ORM'));
//        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->em);
//        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->em, $purger);
//        $executor->execute($loader->getFixtures());
//    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();

    }
}
