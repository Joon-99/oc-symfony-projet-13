<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/** @extends AbstractType<array{quantity: int}> */
class AddToCartType extends AbstractType
{
    /**
     * @param array{max_quantity: int} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxQuantity = $options['max_quantity'];

        $builder->add('quantity', IntegerType::class, [
            'label' => 'Quantité',
            'attr' => ['min' => 0, 'max' => $maxQuantity],
            'constraints' => [
                new Range(
                    min: 0,
                    max: $maxQuantity,
                    notInRangeMessage: 'La quantité doit être comprise entre {{ min }} et {{ max }}.',
                ),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('max_quantity');
        $resolver->setAllowedTypes('max_quantity', 'int');
    }
}
