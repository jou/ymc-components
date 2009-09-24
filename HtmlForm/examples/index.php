<?php

require_once 'ezc/Base/base.php';
spl_autoload_register( array( 'ezcBase', 'autoload' ) );

ezcBase::addClassRepository( realpath( dirname( __FILE__ ).'/../src' ) );

$exampleFiles = glob( dirname( __FILE__ ).'/dir/*.php' );
$examples = array();
foreach( $exampleFiles as $exampleFile )
{
    $examples[pathinfo( $exampleFile, PATHINFO_FILENAME )] = $exampleFile; 
}

if( isset( $_GET['example'] ) && array_key_exists( $_GET['example'], $examples ) )
{
    $example = $_GET['example'];
    require $examples[$example];
}
else
{
    $example = '';
}
?>
<html>
  <head>
    <style>
      .failed
      {
          background-color: #CC0000;
      }
    </style>
  </head>
  <body>
    <div>
      <?php
        foreach( $examples as $key => $file )
        {
            echo '<a href="?example=',$key,'">', $key, '</a>, ';
        }
      ?>
    </div>
    <div>
      <form method="POST" action="?example=<?php echo $example ?>" >
        <?php 
          if( $example )
          {
              $inputSource = new ymcHtmlFormInputSourceFilterExtension( INPUT_POST );
              $form = getForm();
              $form->init();
              if( $inputSource->hasData() )
              {
                $form->validate( $inputSource );
              }
              echo getBody( $example, $form );
          }
        ?>
        <input type="submit" value="submit" />
      </form>
    </div>
    <div>
      <h1>$_POST</h1>
      <?php var_dump( $_POST ) ?>
      <h1>parse form</h1>
      <?php
        if( $example )
        {
          echo 'is valid: ';
          var_dump( $form->isValid() );
          echo 'failures: ';
          foreach( $form->failures as $failure )
          {
            echo $failure->getIdentifier();
          }
          if( $form->button instanceof ymcHtmlFormElement )
          {
              echo '<div>button name: ', $form->button->name, ' button value: ', $form->button->value,'</div>';
          }
        }
      ?>
    </div>
    <div>
      <?php
        if( $example )
        {
          echo nl2br( htmlspecialchars( file_get_contents( 'dir/'.$example.'.php' ) ) ); 
        }
      ?>
    </div>
  </body>
</html>

<?php

function getBody( $example, $form )
{
    $c = ezcTemplateConfiguration::getInstance();
    $c->templatePath = dirname( __FILE__ )."/dir";
    $c->compilePath = "/tmp";

    $t = new ezcTemplate;
    $t->send->form = $form;
    $t->process( $example.'.ezt' );
    return $t->output;
}

