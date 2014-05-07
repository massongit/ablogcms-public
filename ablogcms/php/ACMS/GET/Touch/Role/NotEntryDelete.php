<?php
/**
 * ACMS_GET_Touch_Role_NotEntryDelete
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_NotEntryDelete extends ACMS_GET
{
    function get()
    {
        return roleAuthorization('entry_delete', BID, EID) ? false : $this->tpl;
    }
}
