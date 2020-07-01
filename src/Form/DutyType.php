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
                'choice_label' => 'title',
                'required' => true,
            ])
            ->add('price');

        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        //     $duty = $event->getData();
        //     $form = $event->getForm();

        //     // checks if the Product object is "new"
        //     // If no data is passed to the form, the data is "null".
        //     // This should be considered a new "Product"
        //     if ($duty->getDutyType() && $duty->getDuration()) {
        //         echo $duty->getDutyType()->getHourlyPrice();
        //         $form->add('price', TextType::class, [
        //             'data' => $duty->getDutyType()->getHourlyPrice() * $duty->getDuration()
        //         ]);
        //     }
        // });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Duty::class,
        ]);
    }
}
