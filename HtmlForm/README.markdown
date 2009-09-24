<div class="document" id="ymchtmlform">
<h1 class="title">ymcHtmlForm</h1>

<div class="contents topic" id="table-of-contents">
<p class="topic-title first">Table of Contents</p>
<ul class="simple">
<li><a class="reference internal" href="#introduction" id="id1">Introduction</a></li>
<li><a class="reference internal" href="#characteristics" id="id2">Characteristics</a></li>
<li><a class="reference internal" href="#comparission-with-other-php-libraries" id="id3">Comparission with other PHP libraries</a><ul>
<li><a class="reference internal" href="#agavi" id="id4">Agavi</a></li>
<li><a class="reference internal" href="#codeigniter" id="id5">Codeigniter</a></li>
<li><a class="reference internal" href="#symfony" id="id6">Symfony</a></li>
<li><a class="reference internal" href="#not-so-interesting" id="id7">Not so interesting</a></li>
<li><a class="reference internal" href="#crap" id="id8">Crap</a></li>
</ul>
</li>
<li><a class="reference internal" href="#class-overview" id="id9">Class overview</a></li>
<li><a class="reference internal" href="#usage-examples" id="id10">Usage Examples</a><ul>
<li><a class="reference internal" href="#simple-textboxes" id="id11">Simple Textboxes</a></li>
<li><a class="reference internal" href="#one-class-for-one-form" id="id12">One class for one form</a></li>
</ul>
</li>
<li><a class="reference internal" href="#more-information" id="id13">More information</a></li>
</ul>
</div>
<div class="section" id="introduction">
<h1><a class="toc-backref" href="#id1">Introduction</a></h1>
</div>
<div class="section" id="characteristics">
<h1><a class="toc-backref" href="#id2">Characteristics</a></h1>
<p>The component is not a form builder. The data structure of this component is
optimized to parse the $_POST/$_GET arrays. Building forms requires totally
different structures and should therefor be handled by another component.</p>
<p>Access to input is abstracted in an interface. This makes it much easier to
test the component itself and program build upon it with unit tests.</p>
<p>It is possible to program validation rules that depend on multiple elements.</p>
<ul class="simple">
<li>Form objects can be used<ul>
<li>inside a template to mark erroneous fields</li>
<li>in a system like eZ Publish, where a form is build from many
datatypes</li>
<li>in the controller to get quick access to validated user input</li>
</ul>
</li>
<li>Element Groups can be reused in different forms</li>
<li>Forms can be represented as PHP classes:
$regForm = new myAppRegistrationForm</li>
<li>All Classes can be replaced by other implementations, since checks are
done against interfaces.</li>
</ul>
</div>
<div class="section" id="comparission-with-other-php-libraries">
<h1><a class="toc-backref" href="#id3">Comparission with other PHP libraries</a></h1>
<div class="section" id="agavi">
<h2><a class="toc-backref" href="#id4">Agavi</a></h2>
<ul class="simple">
<li>Agavi: AgaviValidator
<a class="reference external" href="http://www.agavi.org/">http://www.agavi.org/</a>
interesting</li>
</ul>
</div>
<div class="section" id="codeigniter">
<h2><a class="toc-backref" href="#id5">Codeigniter</a></h2>
<p><a class="reference external" href="http://codeigniter.com/user_guide/libraries/form_validation.html">http://codeigniter.com/user_guide/libraries/form_validation.html</a></p>
</div>
<div class="section" id="symfony">
<h2><a class="toc-backref" href="#id6">Symfony</a></h2>
<p><a class="reference external" href="http://www.symfony-project.org/forms/1_2/en/">http://www.symfony-project.org/forms/1_2/en/</a></p>
<ul class="simple">
<li>Two different components for form parsing and building</li>
</ul>
</div>
<div class="section" id="not-so-interesting">
<h2><a class="toc-backref" href="#id7">Not so interesting</a></h2>
<ul class="simple">
<li><a class="reference external" href="http://phpfuse.net/wiki/index.php?title=API:FuseFormValidator">http://phpfuse.net/wiki/index.php?title=API:FuseFormValidator</a></li>
</ul>
</div>
<div class="section" id="crap">
<h2><a class="toc-backref" href="#id8">Crap</a></h2>
<ul class="simple">
<li>ZF.</li>
<li>CakePHP: One class FormHelper</li>
<li>kohanaphp
<a class="reference external" href="http://dev.kohanaphp.com/projects/formo/repository/browse/trunk/libraries">http://dev.kohanaphp.com/projects/formo/repository/browse/trunk/libraries</a>
CRAP</li>
</ul>
</div>
</div>
<div class="section" id="class-overview">
<h1><a class="toc-backref" href="#id9">Class overview</a></h1>
<dl class="docutils">
<dt>ymcHtmlForm, ymcHtmlFormGeneric</dt>
<dd>Represents a form, allows direct access to all elements and is the main
access point for applications using this component.</dd>
<dt>ymcHtmlFormElementsGroup, ymcHtmlFormElementsGroupGeneric</dt>
<dd>Groups elements into logical units.</dd>
<dt>ymcHtmlFormElement, ymcHtmlFormElementBase</dt>
<dd>Represents one input element.</dd>
</dl>
</div>
<div class="section" id="usage-examples">
<h1><a class="toc-backref" href="#id10">Usage Examples</a></h1>
<div class="section" id="simple-textboxes">
<h2><a class="toc-backref" href="#id11">Simple Textboxes</a></h2>
<p>Just show two textboxes, a button and the values, if they're valid, that means not empty.</p>
<pre class="literal-block">
&lt;?php
// build and initialize the form

require_once 'ezc/Base/ezc_bootstrap.php';
ezcBase::addClassRepository( '../../src' );

$form = new ymcHtmlFormGeneric;

$form-&gt;group-&gt;add( new ymcHtmlFormElementText( 'surname' ) );
$form-&gt;group-&gt;add( new ymcHtmlFormElementText( 'forename' ) );

$input = new ymcHtmlFormInputSourceFilterExtension;
if( $input-&gt;hasData() )
{
    $form-&gt;init( $input );
}
else
{
    $form-&gt;init();
}

?&gt;

&lt;html&gt;&lt;body&gt;

  &lt;form method=&quot;POST&quot;&gt;
  
    &lt;label for=&quot;form-element-forename&quot;&gt;forename:&lt;/label&gt;
    &lt;input id=&quot;form-element-forename&quot; type=&quot;text&quot; name=&quot;forename&quot; /&gt;
    &lt;label for=&quot;form-element-surname&quot;&gt;surname:&lt;/label&gt;
    &lt;input id=&quot;form-element-surname&quot; type=&quot;text&quot; name=&quot;surname&quot; /&gt;
  
    &lt;input type=&quot;submit&quot; /&gt;
  
  &lt;/form&gt;

&lt;?php
// Process the values only, if the form is valid
if( $input-&gt;hasData() &amp;&amp; $form-&gt;isValid() )
{
    echo &quot;Hi &quot;.htmlentities( $form['forename']-&gt;value ).' '.htmlentities( $form['surname']-&gt;value );
}

?&gt;

&lt;/body&gt;&lt;/html&gt;

</pre>
<p>Now it's of course annoying to reenter all data only because one field failed.
So we take the value of the elements and redisplay them:</p>
<pre class="literal-block">
&lt;input id=&quot;form-element-forename&quot;
       type=&quot;text&quot; name=&quot;forename&quot;
       value=&quot;&lt;?php echo htmlentities( $form['forename']-&gt;value ); ?&gt;&quot;
       /&gt;
</pre>
<p>As a next step we could mark the failed fields with a css class:</p>
<pre class="literal-block">
&lt;input id=&quot;form-element-forename&quot;
       type=&quot;text&quot; name=&quot;forename&quot;
       class=&quot;&lt;?php echo $form['forename']-&gt;failed ? 'failed' : ''; ?&gt;&quot;
       value=&quot;&lt;?php echo htmlentities( $form['forename']-&gt;value ); ?&gt;&quot;
       /&gt;
</pre>
<p>The failed property is true if the field failed the validation.</p>
<p>As you can see, the input element already starts to look messy, so we better
don't repeat all the stuff for the second element but display both in a foreach
loop (or use a template engine):</p>
<pre class="literal-block">

    &lt;?php
      foreach( $form-&gt;group-&gt;getElements() as $e )
      {
        printf( '&lt;label for=&quot;form-element-%1$s&quot;&gt;%1$s&lt;/label&gt;'.
                '&lt;input id=&quot;form-element-%1$s&quot; type=&quot;text&quot; name=&quot;%1$s&quot; value=&quot;%2$s&quot; class=&quot;%3$s&quot; /&gt;',
                $e-&gt;name,
                htmlentities( $e-&gt;value ),
                $e-&gt;failed ? 'failed' : ''
                );
      }
    ?&gt;
    
</pre>
</div>
<div class="section" id="one-class-for-one-form">
<h2><a class="toc-backref" href="#id12">One class for one form</a></h2>
<p>A design principle that is encouraged by this component is too have one PHP
class for each HTML form in your application. So instead of adding the form
elements in the controller one just instantiates the right class:</p>
<pre class="literal-block">

class SimpleGreetingForm extends ymcHtmlFormGeneric
{
    public function __construct()
    {
        parent::__construct();
        $this-&gt;group-&gt;add( new ymcHtmlFormElementText( 'forename' ) );
        $this-&gt;group-&gt;add( new ymcHtmlFormElementText( 'surname' ) );
    }
}

$form = new SimpleGreetingForm;

</pre>
<p>This principle seems to be overkill for such a simple form but can quickly
become useful ones the forms become more complicate.</p>
</div>
</div>
<div class="section" id="more-information">
<h1><a class="toc-backref" href="#id13">More information</a></h1>
<p>The filters and their parameters are documented in the
<a class="reference external" href="http://php.net/filter">filter documentation</a>.</p>
</div>
</div>
