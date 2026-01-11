<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
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
            ->add('subject', TextType::class, [
                'label' => 'Sujet'
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 5]
            ])
            ->add('response', TextareaType::class, [
                'label' => 'Réponse de l\'administrateur',
                'required' => false,
                'attr' => ['rows' => 5],
                'help' => 'Laissez vide si vous n\'avez pas encore de réponse'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Nouveau' => 'nouveau',
                    'En cours' => 'en_cours',
                    'Traité' => 'traite',
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
            'data_class' => Message::class,
        ]);
    }
}
