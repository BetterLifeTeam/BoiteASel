<?php

namespace App\Form;

use App\Entity\DutyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DutyTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('hourlyPrice')
            ->add('status')
            ->add('noVote')
            ->add('yesVote')
            ->add('voteCommentary')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DutyType::class,
        ]);
    }
}
