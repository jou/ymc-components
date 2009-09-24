<?php
/**
 * Interface for visitable pipe elements that can be visited by ezcPipeVisitor implementations for
 * processing using the Visitor design pattern.
 *
 * All elements that will be part of the pipe tree must implement this interface.
 *
 * {@link http://en.wikipedia.org/wiki/Visitor_pattern Information on the Visitor pattern.}
 */
interface ymcPipeVisitable
{
    /**
     * Accepts the visitor.
     *
     * @param ymcPipeVisitor $visitor
     */
    public function accept( ymcPipeVisitor $visitor );
}
