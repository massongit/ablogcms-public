<?php
/**
 * ACMS_GET_Touch_Approval
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Approval extends ACMS_GET
{
    function get()
    {
        return enableApproval(BID) ? $this->tpl : false;
    }
}
