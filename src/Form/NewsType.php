<?php

namespace App\Form;


use App\Entity\News;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsType extends AbstractType implements FormTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',
                TextType::class
            )
            ->add('description',
                TextType::class
            )
            ->add(
                'createdAt',
                DateTimeType::class
            )
            ->add(
                'createdBy',
                NumberType::class,
                [
                    'property_path' => 'createdBy',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'             => News::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}