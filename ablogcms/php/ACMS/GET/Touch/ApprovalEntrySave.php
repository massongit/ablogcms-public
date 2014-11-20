<?php
/**
 * ACMS_GET_Touch_ApprovalEntrySave
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_ApprovalEntrySave extends ACMS_GET
{
    function get()
    {
        $Session    =& Field::singleton('session');
        $action     = $Session->get('entry_action', '');
        $Session->delete('entry_action');

        return empty($action) ? false : $this->tpl;
    }
}