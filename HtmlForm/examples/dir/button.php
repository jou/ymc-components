<?php

function getForm()
{
    $form = new ymcHtmlFormGeneric;
    $form->group->add( new ymcHtmlFormElementButton( 'save' ) );
    $form->group->add( new ymcHtmlFormElementButton( 'delete' ) );
    return $form;
}
