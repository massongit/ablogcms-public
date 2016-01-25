<?php
/**
 * ACMS_GET_Admin_Workflow_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Workflow_Index extends ACMS_GET_Admin_Edit
{
    function edit(& $Tpl)
    {
        if ( !sessionWithEnterpriseAdministration() ) die(); 
        $Workflow =& $this->Post->getChild('workflow');

        if ( $Workflow->isNull() ) {
            $Workflow->overload(loadWorkflow(BID));
        }
        return $Workflow;
    }
}
