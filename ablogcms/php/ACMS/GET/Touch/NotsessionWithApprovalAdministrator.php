<?php
/**
 * ACMS_GET_Touch_NotsessionWithApprovalAdministrator
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotsessionWithApprovalAdministrator extends ACMS_GET
{
    function get()
    {
        if ( enableApproval(BID) && !sessionWithApprovalAdministrator(BID) ) {
            return $this->tpl;
        } else {
            return false;
        }
    }
}
