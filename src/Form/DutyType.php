<?php

namespace App\Form;

use App\Entity\Duty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DutyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            // ->add('createdAt')
            // ->add('checkedAt')
            // ->add('askerValidAt')
            // ->add('offererValidAt')
            // ->add('doneAt')
            // ->add('setbackAt')
            ->add('duration')
            ->add('place')
            // ->add('status')
            ->add('price')
            // ->add('yesVote')
            // ->add('noVote')
            // ->add('voteCommentary')
            ->add('dutyType')
            // ->add('asker')
            // ->add('offerer')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Duty::class,
        ]);
    }
}
