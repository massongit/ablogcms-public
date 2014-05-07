<?php
/**
 * ACMS_GET_Touch_EditDirect
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_EditDirect extends ACMS_GET
{
    function get()
    {
        return ('on' == config('entry_edit_action_direct') && 'on' == config('entry_edit_inplace_enable') && ( !enableApproval() || sessionWithApprovalAdministrator() ) ) ? $this->tpl : false;
    }
}
