<?php
/**
 * ACMS_GET_Touch_Role_NotAdminEtc
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_NotAdminEtc extends ACMS_GET
{
    function get()
    {
        return ( roleAuthorization('admin_etc', BID) ) ? false : $this->tpl;
    }
}
