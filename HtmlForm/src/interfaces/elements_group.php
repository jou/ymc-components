<?php

/**
 * Represents a group of input elements.
 *
 * Grouping input elements into groups can have several advantages:
 * 
 * - Groups could be reused in different forms.
 * - Validation can be done on group level.
 * 
 */
interface ymcHtmlFormElementsGroup extends ymcHtmlFormElement
{
    public function getElements();
}
