<?php

require_once dirname( __FILE__ ).'/../../tests/forms/definitions/user_registration.php';

function getForm()
{
    $form = new ymcHtmlFormGeneric( new ymcHtmlFormTestGroupUserRegistration );
    return $form;
}
