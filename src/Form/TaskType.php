<?php

namespace App\Form;

use App\Entity\TasksModel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        $userId = $user?->getId();

        $builder
            ->add('user_id', IntegerType::class, [
                'label' => 'User ID',
                'data' => $userId
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('due_date', DateTimeType::class, [
                'label' => 'Due Date',
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 0,
                    'In Progress' => 1,
                    'Completed' => 2,
                ],
                'label' => 'Status',
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Low' => 0,
                    'Medium' => 1,
                    'High' => 2,
                ],
                'label' => 'Priority',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TasksModel::class,
        ]);
    }
}
