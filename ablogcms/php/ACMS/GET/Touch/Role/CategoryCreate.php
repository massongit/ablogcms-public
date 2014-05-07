<?php
/**
 * ACMS_GET_Touch_Role_CategoryCreate
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_CategoryCreate extends ACMS_GET
{
    function get()
    {
        return ( roleAuthorization('category_create', BID) || !roleAvailableUser() ) ? $this->tpl : false;
    }
}
