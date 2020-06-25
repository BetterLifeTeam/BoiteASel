<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperAdminEditMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('money')
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    "Membre" => "ROLE_MEMBER",
                    "Administrateur" => "ROLE_ADMIN",
                    "Super administrateur" => "ROLE_SUPER_ADMIN"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
