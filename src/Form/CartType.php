<?php

namespace App\Form;

use App\Entity\Order;
use App\Form\EventListener\ClearCartListener;
use App\Form\EventListener\RemoveCartItemListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('items', CollectionType::class, [
                'label' => 'Produits',
                'entry_type' => CartItemType::class,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour le panier',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->add('clear', SubmitType::class, [
                'label' => 'Vider le panier',
                'attr' => ['class' => 'btn btn-danger'],
            ]);

        $builder->addEventSubscriber(new RemoveCartItemListener);
        $builder->addEventSubscriber(new ClearCartListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
