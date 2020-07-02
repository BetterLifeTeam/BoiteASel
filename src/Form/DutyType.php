<?php

namespace App\Form;

use App\Entity\Duty;
use App\Entity\DutyType as DutyT;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('title', TextType::class, ['label' => 'Titre :'])
            ->add('description', TextareaType::class, ['label' => 'Description :'])
            ->add('duration', TextType::class, ['label' => 'Durée (en heure) :'])
            ->add('place', TextType::class, ['label' => 'Lieu :'])
            ->add('dutyType', EntityType::class, [
                'class' => DutyT::class,
                'choices' => $this->dutyTypeRepository->findValidType(),
                'required' => true,
                'label' => 'Type de service :'
            ])
            ->add('price', TextType::class, ['label' => 'Prix en grains de sel :']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Duty::class,
        ]);
    }
}
