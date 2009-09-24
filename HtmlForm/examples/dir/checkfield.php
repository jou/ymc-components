<?php

require_once dirname( __FILE__ ).'/../../tests/forms/definitions/user_registration.php';

function getForm()
{
    $values = array( 
        10 => 'on',
        11 => 'on',
        13 => 0,
        14 => 0,
        16 => 'on',
        18 => 'on',
    );

    $element = new ymcHtmlFormElementCheckboxArray( 'agents' );
    $element->value = $values;

    $form = new ymcHtmlFormGeneric();
    $form->group->add( $element );
    return $form;
}
