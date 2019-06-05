<?php
/**
 * Disease Insertion Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class DiseaseInsertionType extends ConfigurationAbstractType
{
    protected $disabledDiseaseReference;

    public function __construct($locales)
    {
        parent::__construct($locales);
        $this->disabledDiseaseReference = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id',             IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        $builder->add('disease',        TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Disease.Reference.Label', array('disabled' => $this->disabledDiseaseReference)));
        $builder->add('name',           TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Common.Name.Label', array('required' => true)));
        $builder->add('keyWord',        TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Disease.KeyWord.Label', array('required' => true)));
        $builder->add('position',       IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Disease.Position.Label', array('required' => false)));
        $builder->add('reportDataSourceId',          DiseaseReportDataSourceType::class,
            $this->_getFieldOptions('Configuration.FormItems.Common.ReportDataSource.Label', array('required' => false)));
    }

    public function getName()
    {
        return 'app_disease_insertion';
    }
}