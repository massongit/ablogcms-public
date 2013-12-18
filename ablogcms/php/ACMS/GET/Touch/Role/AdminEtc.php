<?php
/**
 * ACMS_GET_Touch_Role_AdminEtc
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_AdminEtc extends ACMS_GET
{
    function get()
    {
        return ( roleAuthorization('admin_etc', BID) || !roleAvailableUser() ) ? $this->tpl : false;
    }
}
