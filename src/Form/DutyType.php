<?php

namespace App\Form;

use App\Entity\Duty;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('duration')
            ->add('place')
            ->add('dutyType')
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
