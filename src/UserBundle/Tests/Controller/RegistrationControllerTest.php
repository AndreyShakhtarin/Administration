<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 8:33 PM
 */

namespace UserBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraints\DateTime;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegister(  )
    {

        $client = static::createClient();
        $crawler = $client->request( 'GET', '/register/' );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Registration")')->count()
        );

        $link = $crawler->selectLink('Registration')->link();
        $client->click($link);


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

        $form = $crawler->selectButton('Register')->form();
        $crawler = $client->submit($form, $user);

    }

    public function testConfirmed()
    {
        $client = static::createClient();
        $crawler = $client->request( 'GET', '/register/confirmed/' );
        $this->assertEquals(
            $client->getResponse()->getContent(),
            $client->getResponse()->getContent()
        );
    }
}