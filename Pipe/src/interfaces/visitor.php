<?php

/**
 * Interface for visitor implementations that want to process a pipe using the Visitor design
 * pattern.
 *
 * visit() is called on each of the nodes at least once. The Visitor is in charge to ensure that
 * he visits every node only once, if he want's that.
 *
 * Start the processing of the pipe by calling accept() on the pipe passing the visitor object as
 * the sole parameter.
 */
interface ymcPipeVisitor
{
    /**
     * Visit the $visitable.
     *
     * Each node in the graph is visited once.
     *
     * @param ymcPipeVisitable $visitable
     * @return bool
     */
    public function visit( ymcPipeVisitable $visitable );
}
