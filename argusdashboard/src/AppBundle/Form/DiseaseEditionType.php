<?php
/**
 * Disease Edition Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;


class DiseaseEditionType extends DiseaseInsertionType
{
    public function __construct($locales)
    {
        parent::__construct($locales);
        $this->disabledDiseaseReference = true;
    }

    public function getName()
    {
        return 'app_disease_edition';
    }
}