<?php
/**
 * ACMS_GET_Touch_Role_ConfigEdit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_ConfigEdit extends ACMS_GET
{
    function get()
    {
        return ( roleAuthorization('config_edit', BID) || !roleAvailableUser() ) ? $this->tpl : false;
    }
}
