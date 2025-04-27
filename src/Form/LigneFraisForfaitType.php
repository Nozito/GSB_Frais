<?php

namespace App\Form;

use App\Entity\LigneFraisForfait;
use App\Entity\FraisForfait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneFraisForfaitType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Ajout du champ fraisForfait qui est une liste des frais forfaits existants
        $builder
            ->add('fraisForfaits', ChoiceType::class, [
                'choices' => $this->getFraisForfaits(),
                'choice_label' => function ($fraisForfait) {
                    return $fraisForfait->getLibelle();
                },
                'expanded' => false,
                'multiple' => false,
                'label' => 'Frais forfait',
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LigneFraisForfait::class,
        ]);
    }

    private function getFraisForfaits()
    {
        $fraisForfaitRepository = $this->entityManager->getRepository(FraisForfait::class);
        return $fraisForfaitRepository->findAll();
    }
}