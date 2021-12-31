<?php

namespace App\Form;

use App\Entity\UserRole;
use FervoEnumBundle\Generated\Form\ActiveDirectorySyncSourceTypeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ActiveDirectoryRoleSyncOptionType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.description'
            ])
            ->add('sourceType', ActiveDirectorySyncSourceTypeType::class, [
                'label' => 'label.source',
                'label_attr' => [
                    'class' => 'radio-custom'
                ],
                'expanded' => true
            ])
            ->add('source', TextType::class, [
                'label' => 'label.value'
            ])
            ->add('userRole', EntityType::class, [
                'class' => UserRole::class,
                'label' => 'label.user_role',
                'choice_label' => 'name'
            ]);
    }
}