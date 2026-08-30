<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\ReviewInputDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<ReviewInputDto> */
final class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Cégnév',
                'attr' => ['autocomplete' => 'organization', 'maxlength' => 255],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Értékelés',
                'choices' => ['★' => 1, '★★' => 2, '★★★' => 3, '★★★★' => 4, '★★★★★' => 5],
                'expanded' => true,
                'multiple' => false,
                'row_attr' => ['class' => 'form-row rating-field'],
            ])
            ->add('reviewText', TextareaType::class, [
                'label' => 'Véleményed',
                'attr' => ['maxlength' => 5000, 'rows' => 6],
            ])
            ->add('authorEmail', EmailType::class, [
                'label' => 'E-mail-cím',
                'help' => 'Az e-mail-címedet eltároljuk a véleménnyel, de nyilvánosan soha nem jelenítjük meg.',
                'attr' => ['autocomplete' => 'email', 'maxlength' => 255],
            ])
            ->add('website', TextType::class, [
                'label' => 'Weboldal',
                'required' => false,
                'row_attr' => ['class' => 'honeypot', 'aria-hidden' => 'true'],
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReviewInputDto::class,
            'csrf_token_id' => 'review_submit',
        ]);
    }
}
