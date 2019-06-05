<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 02/11/2017
 * Time: 10:48
 */
namespace AppBundle\Form\Transformer;

use Tetranz\Select2EntityBundle\Form\DataTransformer\EntityToPropertyTransformer;
use AppBundle\Entity\SesDashboardDiseaseValue;

class SesDashboardDiseaseValueTransformer extends EntityToPropertyTransformer
{
    /**
     * Transform entity to array
     *
     * @param mixed $entity
     * @return array
     */
    public function transform($entity)
    {
        $data = [];
        if (empty($entity)) {
            return $data;
        }

        if ($entity instanceof SesDashboardDiseaseValue) {
            $data[$entity->getId()] = $entity->getParentDisease()->getName() . " : " . $entity->getValue();
        }

        return $data;
    }
}