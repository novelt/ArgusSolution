<?php
/**
 * Contact Type
 *
 * @author François Cardinaux
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class ContactTypeInsertionType extends ConfigurationAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id',             IntegerType::class,     $this->_getFieldOptions('Configuration.FormItems.Common.Id.Label', array('disabled' => true)));
        $builder->add('reference',      TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Common.Reference.Label'));
        $builder->add('name',           TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Common.Name.Label'));
        $builder->add('desc',           TextType::class,        $this->_getFieldOptions('Configuration.FormItems.Common.Description.Label', array('required' => false)));
        $builder->add('sendsReports',   CheckboxType::class,    $this->_getFieldOptions('Configuration.FormItems.ContactType.SendsReports.Label', array('required' => false)));
        $builder->add('useInIndicatorsCalculation',   CheckboxType::class,    $this->_getFieldOptions('Configuration.FormItems.ContactType.UseInIndicatorsCalculation.Label', array('required' => false)));
    }

    public function getName()
    {
        return 'app_contact_type_insertion';
    }
}