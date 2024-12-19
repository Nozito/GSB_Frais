<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            'MMMM yyyy'
        );

        // Traductions pour les mois (à éviter de faire manuellement si possible)
        $translations = [
            'January' => 'janvier',
            'February' => 'février',
            'March' => 'mars',
            'April' => 'avril',
            'May' => 'mai',
            'June' => 'juin',
            'July' => 'juillet',
            'August' => 'août',
            'September' => 'septembre',
            'October' => 'octobre',
            'November' => 'novembre',
            'December' => 'décembre'
        ];

        // Création des choix pour le champ "mois"
        foreach ($options['ficheFraisCollection'] as $ficheFrais) {
            $moisLabel = ucfirst($formatter->format($ficheFrais->getMois()->getTimestamp()));
            // Traduction manuelle
            $moisLabel = ucfirst(strtr($moisLabel, $translations));
            // Utilisation de l'ID de la fiche pour la valeur
            $choices[$moisLabel] = $ficheFrais->getId();
        }

        // Ajout du champ "mois" au formulaire avec les choix
        $builder->add('mois', ChoiceType::class, [
            'choices' => $choices,
            'required' => true,
            'label' => 'Sélectionnez un mois',
            'placeholder' => 'Choisir un mois',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définir l'option par défaut
        $resolver->setDefaults([
            'ficheFraisCollection' => [],
        ]);
    }
}