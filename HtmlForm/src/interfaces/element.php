<?php

interface ymcHtmlFormElement
{
    /**
     * Register the element with the form.
     * 
     * @param ymcHtmlForm $form 
     */
    public function init( ymcHtmlForm $form );

    /**
     * Validate the input.
     * 
     * @param ymcHtmlFormInputSource $inputSource 
     * @return Array failures
     */
    public function validate( ymcHtmlFormInputSource $inputSource );

    public function getFailures();

    /**
     * Returns a unique, html compliant name.
     *
     * Everything which is parsable and validable must have a name to indentify elements that
     * caused problems.
     * 
     * @return string
     */
    public function getName();

    public function hasData();
}
