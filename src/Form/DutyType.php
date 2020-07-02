<?php

namespace App\Form;

use App\Entity\Duty;
use App\Entity\DutyType as DutyT;
use Symfony\Component\Form\FormEvent;
use App\Repository\DutyTypeRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DutyType extends AbstractType
{
    public function __construct(DutyTypeRepository $dutyTypeRepository)
    {
        $this->dutyTypeRepository = $dutyTypeRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('duration')
            ->add('place')
            ->add('dutyType', EntityType::class, [
                'class' => DutyT::class,
                'choices' => $this->dutyTypeRepository->findValidType(),
                'required' => true,
            ])
            ->add('price');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Duty::class,
        ]);
    }
}
