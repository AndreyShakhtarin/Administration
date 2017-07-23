<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/16/17
 * Time: 1:33 PM
 */

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use UserBundle\Entity\Users;
use  Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('surname')
            ->add('profession')
            ->add( 'country')
            ->add( 'city')
            ->add( 'email' )
            ->add( 'born', DateType::class  )
            ->add('gender', ChoiceType::class, array(
                'choices' => array(
                    'male' => true,
                    'female' => false,
                    'other' => 2,
                    )
                )
            )
            ->add( 'status', ChoiceType::class, array(
                'choices' => array(
                    'Marred'        => 'marred',
                    'Not marred'    => 'not marred'
                )
            ) )
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Users::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'userbundle_user';
    }
}