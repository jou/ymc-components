<?php

interface ymcHtmlForm
{
    /**
     * Whether the input data is valid.
     * 
     * @return boolean
     */
    public function isValid();

    /**
     * Recursively registers all groups and elements with the form
     */
    public function init();

    /**
     * Validates and parses the input.
     * 
     * @param ymcHtmlFormInputSource $inputSource 
     */
    public function validate( ymcHtmlFormInputSource $inputSource );

    /**
     * Called from a button element to register itself as the pressed form button.
     * 
     * @param ymcHtmlFormElement $button 
     */
    public function setButton( ymcHtmlFormElement $button );

    /**
     * Called during init() from all elements and groups to register themselves.
     * 
     * @param ymcHtmlFormElement $element 
     */
    public function registerOnInit( ymcHtmlFormElement $element );

    /**
     * Returns validation failures.
     * 
     * @return array
     */
    public function getFailures();
}
