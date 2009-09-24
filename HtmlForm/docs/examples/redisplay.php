<?php
// build and initialize the form

require_once 'ezc/Base/ezc_bootstrap.php';
ezcBase::addClassRepository( '../../src' );

// BEGINFORMINIT
class SimpleGreetingForm extends ymcHtmlFormGeneric
{
    public function __construct()
    {
        parent::__construct();
        $this->group->add( new ymcHtmlFormElementText( 'forename' ) );
        $this->group->add( new ymcHtmlFormElementText( 'surname' ) );
        $this->init();
    }
}

$form = new SimpleGreetingForm;
// ENDFORMINIT

$input = new ymcHtmlFormInputSourceFilterExtension;
if( $input->hasData() )
{
    $form->validate( $input );
}

?> 

<html>
  <head>
    <style>
      form .failed{
        border:1px solid red;
      }
    </style>
  </head>
  <body>

  <form method="POST">
    <!--BEGINCHANGEDHTML-->
    <?php
      foreach( $form->group->getElements() as $e )
      {
        printf( '<label for="form-element-%1$s">%1$s</label>'.
                '<input id="form-element-%1$s" type="text" name="%1$s" value="%2$s" class="%3$s" />',
                $e->name,
                htmlentities( $e->value ),
                $e->failed ? 'failed' : ''
                );
      }
    ?>
    <!--ENDCHANGEDHTML-->
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
