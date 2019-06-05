<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23/02/2017
 * Time: 16:09
 */

namespace AppBundle\Form;

use AppBundle\Services\LocaleService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class SiteLanguageType extends ConfigurationAbstractType
{
    /**
     * Stores the available language choices.
     *
     * @var array
     */
    private $locales;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $localeService;

    public function __construct($locales, TranslatorInterface $translator, LocaleService $localeService)
    {
        parent::__construct($locales);
        $this->translator = $translator;
        $this->localeService = $localeService;
    }

    /*public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('timezone', ChoiceType::class, [
            'choices' => $this->getTimezones()
        ]);
    }*/

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getLanguages(),
            'choices_as_values' => true,
            'choice_translation_domain' => false,
        ));
    }


    public function getParent()
    {
        return ChoiceType::class;
    }

    private function getLanguages() {
        if($this->locales === null) {
            $inheritedTranslationString = $this->translator->trans('Configuration.FormItems.Site.Timezone.InheritedValue', array(), ConfigurationAbstractType::TRANSLATION_DOMAIN);
            $this->locales[$inheritedTranslationString] = null;

            $availableLocales = $this->localeService->getAvailableLocales();
            foreach($availableLocales as $locale) {
                $language = $this->translator->trans($locale, array(), 'messages');
                $this->locales[$language] = $locale;
            }
        }

        return $this->locales;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return LocaleService
     */
    public function getLocaleService()
    {
        return $this->localeService;
    }

    /**
     * @param LocaleService $localeService
     */
    public function setLocaleService($localeService)
    {
        $this->localeService = $localeService;
    }
}