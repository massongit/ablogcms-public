<?php
/**
 * ACMS_GET_Touch_sessionWithApprovalAdministrator
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_sessionWithApprovalAdministrator extends ACMS_GET
{
    function get()
    {
        if ( 0
            || !enableApproval(BID)
            || ( enableApproval(BID) && sessionWithApprovalAdministrator(BID) )
        ) {
            return $this->tpl;
        } else {
            return false;
        }
    }
}
