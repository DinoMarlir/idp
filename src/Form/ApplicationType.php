<?php

namespace App\Form;

use App\Entity\ApplicationScope;
use App\Entity\SamlServiceProvider;
use Doctrine\ORM\EntityRepository;
use FervoEnumBundle\Generated\Form\ApplicationScopeType;
use SchulIT\CommonBundle\Form\FieldsetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApplicationType extends AbstractType {

    public function __construct(private readonly TranslatorInterface $translator) { }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('group_general', FieldsetType::class, [
                'legend' => 'label.general',
                'fields' => function(FormBuilderInterface $builder) {
                    $builder
                        ->add('name', TextType::class, [
                            'label' => 'label.name'
                        ])
                        ->add('scope', ApplicationScopeType::class, [
                            'label' => 'label.application_scope',
                            'expanded' => true,
                            'label_attr' => [
                                'class' => 'radio-custom'
                            ]
                        ])
                        ->add('service', EntityType::class, [
                            'class' => SamlServiceProvider::class,
                            'query_builder' => fn(EntityRepository $repository) => $repository->createQueryBuilder('s')
                                ->orderBy('s.name', 'asc'),
                            'choice_label' => 'name',
                            'label' => 'label.service',
                            'required' => false,
                            'label_attr' => [
                                'class' => 'radio-custom'
                            ],
                            'multiple' => false,
                            'expanded' => true
                        ])
                        ->add('description', TextType::class, [
                            'label' => 'label.description'
                        ]);
                }
            ]);
    }
}