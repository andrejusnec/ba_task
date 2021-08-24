<?php

namespace App\Form;

use App\Entity\QueryList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiver', EmailType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Email', 'mapped' => false],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QueryList::class,
        ]);
    }
}
