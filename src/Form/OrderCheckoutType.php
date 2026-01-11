<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderCheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom complet'])
                ]
            ])
            ->add('shippingAddress', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Numéro et rue'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse'])
                ]
            ])
            ->add('shippingPostalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre code postal'])
                ]
            ])
            ->add('shippingCity', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre ville'])
                ]
            ])
            ->add('shippingPhone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control', 'placeholder' => '06 12 34 56 78'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre téléphone'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}