<?php

namespace Urbem\TributarioBundle\Form\Imobiliario;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReativarTrechoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldOptions = array();

        $fieldOptions['motivo'] = array(
            'label' => 'label.imobiliarioTrecho.motivoReativacao',
            'required' => true
        );

        $builder->add('justificativaTermino', 'textarea', $fieldOptions['motivo']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Urbem\CoreBundle\Entity\Imobiliario\BaixaTrecho'
        ));
    }
}
