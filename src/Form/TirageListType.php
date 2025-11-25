<?php

namespace App\Form;

use App\Entity\TirageList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TirageListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateTirage')
            ->add('numeroUn')
            ->add('numeroDeux')
            ->add('numeroTrois')
            ->add('numeroQuatre')
            ->add('numeroCinq')
            ->add('etoileUn')
            ->add('etoileDeux')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TirageList::class,
        ]);
    }
}
