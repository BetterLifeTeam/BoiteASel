<?php

namespace App\Form;

use App\Entity\DutyType;
use App\Repository\DutyTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchDutyType extends AbstractType
{

    public function __construct(DutyTypeRepository $dutyTypeRepository)
    {
        $this->dutyTypeRepository = $dutyTypeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, ['label' => false], ['required' => false])
            ->add('type', EntityType::class, [
                'class' => DutyType::class,
                'choices' => $this->dutyTypeRepository->findValidType(),
                'choice_label' => 'title',
                'attr' =>  ['onchange' => 'this.form.submit()'],
                'required' => false,
                'placeholder' => 'Tous',
                'label' => false
            ])
            ->add('order', ChoiceType::class, [
                'choices'  => [
                    'plus récent' => 'DESC',
                    'plus ancien' => 'ASC',
                ],
                'attr' =>  ['onchange' => 'this.form.submit()'],
                'required' => false,
                'label' => false
            ])
            ->add('Rechercher', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
