<?php
// build and initialize the form

require_once 'ezc/Base/ezc_bootstrap.php';
ezcBase::addClassRepository( '../../src' );

$form = new ymcHtmlFormGeneric;

$form->group->add( new ymcHtmlFormElementText( 'surname' ) );
$form->group->add( new ymcHtmlFormElementText( 'forename' ) );
$form->init();

$input = new ymcHtmlFormInputSourceFilterExtension;
if( $input->hasData() )
{
    $form->validate( $input );
}

?>

<html><body>

  <form method="POST">
  
    <label for="form-element-forename">forename:</label>
    <input id="form-element-forename" type="text" name="forename" />
    <label for="form-element-surname">surname:</label>
    <input id="form-element-surname" type="text" name="surname" />
  
    <input type="submit" />
  
  </form>

<?php
// Process the values only, if the form is valid
if( $input->hasData() && $form->isValid() )
{
    echo "Hi ".htmlentities( $form['forename']->value ).' '.htmlentities( $form['surname']->value );
}

?>

</body></html>
