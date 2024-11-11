<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class MoisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mois', ChoiceType::class, [
            'choices' => $options['mois_choices'],  // Les mois passés du contrôleur
            'placeholder' => 'Choisissez un mois',  // Ajoute une option de placeholder
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mois_choices' => [],  // Valeur par défaut vide
        ]);
    }
}
