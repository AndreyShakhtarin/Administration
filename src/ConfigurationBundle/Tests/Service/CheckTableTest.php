<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 5:59 PM
 */

namespace ConfigurationBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use ConfigurationBundle\Service\CheckTable;

class CheckTableTest extends WebTestCase
{
    public $checkTable;
    public $em;
    public $parameters = array( );

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->parameters['dbh'] = "mysql:host=" . static::$kernel->getContainer()->getParameter( 'database_host') . ";dbname=" . static::$kernel->getContainer()->getParameter( 'database_name');
        $this->parameters['user'] = static::$kernel->getContainer()->getParameter( 'database_user');
        $this->parameters['pass'] = static::$kernel->getContainer()->getParameter( 'database_password');
    }

    public function testSetInit( $dbh = 'connection' )
    {
        $this->checkTable = new CheckTable();
        $this->assertEquals(null, $this->checkTable->setInit( $dbh ) );
    }

    public function testHasAdmin( $dbh = true )
    {
        $db = new CheckTable();
        $db->setInit( $this->parameters );
        $this->assertTrue( true, $db->hasAdmin( $dbh ));
    }
}