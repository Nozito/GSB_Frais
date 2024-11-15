<?php

namespace App\Form;

use App\Entity\FicheFrais;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DateTimeInterface;

class MoisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ficheFraisCollection = $options['fiche_frais_collection'];

        $builder->add('ficheFrais', ChoiceType::class, [
            'choices' => $ficheFraisCollection,
            'choice_label' => function ($ficheFrais) {
                return $ficheFrais->getMois()->format('F Y');
            },
            'placeholder' => 'Sélectionnez une fiche de frais',
            'label' => 'Choisir une fiche de frais',
        ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('fiche_frais_collection');

        $resolver->setAllowedTypes('fiche_frais_collection', 'array');
    }
}