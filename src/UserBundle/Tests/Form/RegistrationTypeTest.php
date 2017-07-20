<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 11:37 PM
 */

namespace UserBundle\Tests\Form;

use UserBundle\Form\RegistrationType;
use UserBundle\Entity\User;
use Symfony\Component\Form\Test\TypeTestCase;
use FOS\UserBundle\Form\Type\RegistrationFormType;

class RegistrationTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {

        $formData = array(
            'name'      => 'admin',
            'surname'   => 'admin',
            'username'  => 'admin',
            'email'     => 'admin@mail.ru',
            'gender'    => 'female',
        );
        $user = new User();
        $user->setName('admin');
        $user->setSurname('admin');
        $user->setUsername('admin');
        $user->setGender(0);


        $form = $this->factory->create( RegistrationType::class);


        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($user, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}