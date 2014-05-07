<?php
/**
 * ACMS_GET_Touch_NotApprovalReject
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotApprovalReject extends ACMS_GET
{
    function get()
    {
        return sessionWithApprovalReject(BID) ? false : $this->tpl;
    }
}
