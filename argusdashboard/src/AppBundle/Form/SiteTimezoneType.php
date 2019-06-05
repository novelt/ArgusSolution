<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23/02/2017
 * Time: 16:09
 */

namespace AppBundle\Form;

use AppBundle\Services\Timezone\TimezoneService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class SiteTimezoneType extends ConfigurationAbstractType
{
    /**
     * Stores the available timezone choices.
     *
     * @var array
     */
    private $timezones;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TimezoneService
     */
    private $timezoneService;

    public function __construct($locales, TranslatorInterface $translator, TimezoneService $timezoneService)
    {
        parent::__construct($locales);
        $this->translator = $translator;
        $this->timezoneService = $timezoneService;
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
            'choices' => $this->buildTimezones(),
            'choices_as_values' => true,
            'choice_translation_domain' => false,
        ));
    }


    public function getParent()
    {
        return ChoiceType::class;
    }

    private function buildTimezones() {
        if($this->timezones === null) {
            $this->timezones = [];
            $inheritedTranslationString = $this->translator->trans('Configuration.FormItems.Site.Timezone.InheritedValue', array(), ConfigurationAbstractType::TRANSLATION_DOMAIN);
            $this->timezones[$inheritedTranslationString][$inheritedTranslationString] = null;

            foreach ($this->timezoneService->getTimezones() as $timezone) {
                $this->timezones[$timezone->getRegion()][$timezone->getName()] = $timezone->getId();
            }
        }

        return $this->timezones;
    }

    /**
     * @return array
     */
    public function getTimezones()
    {
        return $this->timezones;
    }

    /**
     * @param array $timezones
     */
    public function setTimezones($timezones)
    {
        $this->timezones = $timezones;
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
     * @return TimezoneService
     */
    public function getTimezoneService()
    {
        return $this->timezoneService;
    }

    /**
     * @param TimezoneService $timezoneService
     */
    public function setTimezoneService($timezoneService)
    {
        $this->timezoneService = $timezoneService;
    }
}