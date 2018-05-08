<?php

namespace RetardTransilien\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class IncidentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array( '' => '');
        for($i=0;$i<=300;$i++){
            $choices[$i] = $i;
        }
        $builder
            ->add('date', HiddenType::class)
            ->add('tripId', HiddenType::class)
            ->add('serviceId', HiddenType::class)
            ->add('headsign', HiddenType::class)
            ->add('routeId', HiddenType::class)
            ->add('incidentType', ChoiceType::class,array(
                'choices' => array(
                        '' => '',
                        'Aucun (à l\'heure)' => '3',
                        'Retard' => '1',
                        'Modif desserte' => '4',
                        'Suppression' => '2',
                    ),
                'required' => true,
                ))
            ->add('delay', ChoiceType::class, array('choices' => $choices, 'required' => true) );
    }

    public function getName()
    {
        return 'incident';
    }
}
