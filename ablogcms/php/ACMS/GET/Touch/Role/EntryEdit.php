<?php
/**
 * ACMS_GET_Touch_Role_EntryEdit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_EntryEdit extends ACMS_GET
{
    function get()
    {
        return ( roleAuthorization('entry_edit', BID, EID) || !roleAvailableUser() ) ? $this->tpl : false;
    }
}
