<?php

namespace App\Form;

use App\Entity\AddressBook;
use App\Entity\QueryList;
use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                'attr' => ['class' => 'form-control', 'placeholder' => 'Email', 'mapped' => false]
            ])->add('sender', EntityType::class, [
                'class' => User::class,
                'attr' => ['class' => 'form-control', 'disabled' => true]
            ])->add('addressRecord', EntityType::class, [
                'class' => AddressBook::class,
                'attr' => ['class' => 'form-control', 'disabled' => true]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QueryList::class,
        ]);
    }
}
