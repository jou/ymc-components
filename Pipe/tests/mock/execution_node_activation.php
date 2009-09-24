<?php

class ymcPipeExecutionActivationMock extends ymcPipeExecution
{
    public function getActivatedNodes()
    {
        return $this->activatedNodes;
    }

    public function publicActivateStartNodes()
    {
        $this->activateStartNodes();
        return $this->activatedNodes;
    }

    public function setPipe( ymcPipe $pipe )
    {
        $this->pipe = $pipe;
    }
}
