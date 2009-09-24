<?php

function getForm()
{
    $form = new ymcHtmlFormGeneric;
    $radio = new ymcHtmlFormElementRadio( 'type' );
    $radio->values = array( 'user', 'accountadmin', 'consultant' );
    
    $form->group->add( $radio );

    return $form;
}
