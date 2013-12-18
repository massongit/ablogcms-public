<?php
/**
 * ACMS_GET_Touch_RevisionAdministrator
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_RevisionAdministrator extends ACMS_GET
{
    function get()
    {
        if ( 0
            || !enableApproval(BID)
            || sessionWithApprovalAdministrator(BID)
        ) {
            return $this->tpl;
        }
        return false;
    }
}
