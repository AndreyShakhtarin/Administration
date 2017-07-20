<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 4:04 PM
 */

namespace UserBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Entity\User;

class UserTest extends WebTestCase
{

    public function testGetId()
    {
        $user = new User();
        $this->assertEquals( 0 , $user->getId() );
    }
    public function testSetName()
    {
        $user = new User();
        $user->setName( "John" );
        $this->assertEquals( "John", $user->getName() );
    }

    public function testSetGender()
    {
        $user = new User();
        $user->setGender( 0 );
        $this->assertEquals( 'female', $user->getGender() );
    }

    public function testSetSurname()
    {
        $user = new User();
        $user->setSurname( "Jonson" );
        $this->assertEquals( "Jonson" , $user->getSurname() );
    }

    public function testSetBirthday()
    {
        $user = new User();
        $user->setBirthday( 123 );
        $this->assertEquals( 123 , $user->getBirthday() );
    }
}