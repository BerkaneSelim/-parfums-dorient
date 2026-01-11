<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Client'
            ])
            ->add('orderNumber', TextType::class, [
                'label' => 'Numéro de commande'
            ])
            ->add('totalPrice', NumberType::class, [
                'label' => 'Prix total (€)'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En cours' => 'en_cours',
                    'Expédiée' => 'expediee',
                    'Livrée' => 'livree',
                    'Annulée' => 'annulee',
                ],
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
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
