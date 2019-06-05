<?php

namespace AppBundle\Form;

use AppBundle\Entity\Gateway\GatewayDevice;
use AppBundle\Services\Gateway\GatewayDeviceService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GatewayDeviceType
 *
 * @package AppBundle\Form
 */
class GatewayDeviceType extends ConfigurationAbstractType
{
    /**
     * @var GatewayDeviceService
     */
    private $gatewayDeviceService;

    public function __construct($locales, GatewayDeviceService $gatewayDeviceService)
    {
        parent::__construct($locales);
        $this->gatewayDeviceService = $gatewayDeviceService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->buildGatewayDevices(),
            'choice_translation_domain' => false,
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    private function buildGatewayDevices()
    {
        $result = [];

        $gatewaysDevices = $this->gatewayDeviceService->getAllGatewayDevices();

        /** @var GatewayDevice $gatewaysDevice */
        foreach ($gatewaysDevices as $gatewaysDevice) {
            $result[$gatewaysDevice->getGatewayId()] = $gatewaysDevice->getGatewayId() . " (" . $gatewaysDevice->getOperator() . ")";
        }

        return $result;
    }
}