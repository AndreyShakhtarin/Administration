<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/18/17
 * Time: 11:15 PM
 */

namespace UserBundle\Tests\Form;

use UserBundle\Form\EditType;
use UserBundle\Entity\User;
use Symfony\Component\Form\Test\TypeTestCase;

class EditTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $birthday = new \DateTime('now');
        $formData = array(
            'name' => 'admin',
            'surname' => 'admin',
            'username' => 'admin',
            'email' => 'admin@mail.ru',
            'birthday' => $birthday,
        );
        $user = new User();
        $user->setName('admin');
        $user->setSurname('admin');
        $user->setUsername('admin');
        $user->setBirthday($birthday);

        $form = $this->factory->create(EditType::class, $user);

//        $object = User::fromArray($formData);

        // submit the data to the form directly
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