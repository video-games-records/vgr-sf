<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Presentation\Form;

use App\BoundedContext\VideoGamesRecords\Core\Domain\Entity\Country;
use App\BoundedContext\VideoGamesRecords\GamingPlatform\Domain\ValueObject\PlatformEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class PlayerFilterType extends AbstractType
{
    public const array LAST_ACTIVE_CHOICES = ['today', 'week', 'month', 'year', 'never'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'];

        $countryChoices = [];
        /** @var Country $country */
        foreach ($options['countries'] as $country) {
            $countryChoices[$country->getName($locale) ?? $country->getDefaultName()] = $country->getId();
        }

        $platformChoices = [];
        foreach (PlatformEnum::supported() as $platform) {
            $platformChoices[$platform->getLabel()] = $platform->value;
        }

        $lastActiveChoices = [];
        foreach (self::LAST_ACTIVE_CHOICES as $choice) {
            $lastActiveChoices['player.index.filter.last_active_' . $choice] = $choice;
        }

        $builder
            // Declared (rather than raw <input type="hidden"> in the template) so the current
            // sort/order carried alongside a filter submission don't make Symfony treat them
            // as unrecognized extra fields (which marks the form invalid -> auto 422 response).
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
            ->add('q', TextType::class, [
                'label' => 'player.index.filter.search',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'player.index.filter.search_placeholder'],
            ])
            ->add('platforms', ChoiceType::class, [
                'label' => 'player.index.filter.platforms',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $platformChoices,
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'player.index.filter.country',
                'required' => false,
                'placeholder' => 'player.index.filter.all_countries',
                'choices' => $countryChoices,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('team', TextType::class, [
                'label' => 'player.index.filter.team',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'player.index.filter.team_placeholder',
                    'data-controller' => 'tomselect',
                    'data-tomselect-url-value' => '/api/teams/autocomplete',
                    'data-tomselect-locale-value' => $locale,
                    'data-tomselect-max-items-value' => 1,
                ],
            ])
            ->add('pointsMin', IntegerType::class, [
                'label' => 'player.index.filter.points_min',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('pointsMax', IntegerType::class, [
                'label' => 'player.index.filter.points_max',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('lastActive', ChoiceType::class, [
                'label' => 'player.index.filter.last_active',
                'required' => false,
                'placeholder' => 'player.index.filter.last_active_any',
                'choices' => $lastActiveChoices,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('hasBadges', CheckboxType::class, [
                'label' => 'player.index.filter.has_badges',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'countries' => [],
            'locale' => 'en',
            'method' => 'GET',
            'csrf_protection' => false,
            'translation_domain' => 'VgrCore',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
