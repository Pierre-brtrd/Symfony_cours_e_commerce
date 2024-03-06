<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Taxe;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre du produit'
                ]
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description courte',
                'attr' => [
                    'placeholder' => 'Description courte du produit',
                    'rows' => 2,
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description longue',
                'attr' => [
                    'placeholder' => 'Description longue du produit',
                    'rows' => 5,
                ]
            ])
            ->add('priceHT', MoneyType::class, [
                'label' => 'Prix HT',
                'attr' => [
                    'placeholder' => 'Prix HT du produit'
                ]
            ])
            ->add('taxe', EntityType::class, [
                'label' => 'Taxe',
                'placeholder' => 'Choisir une taxe',
                'class' => Taxe::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('t')
                        ->where('t.enable = :enable')
                        ->setParameter('enable', true)
                        ->orderBy('t.name', 'ASC');
                },
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('image', VichImageType::class, [
                'required' => false,
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
                'label' => 'Image',
                'allow_delete' => false,
                'attr' => [
                    'class' => 'file-upload',
                ],
            ])
            ->add('enable', CheckboxType::class, [
                'label' => 'Activer',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
