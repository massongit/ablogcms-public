<?php
/**
 * ACMS_GET_Touch_Role_NotModuleEdit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_NotModuleEdit extends ACMS_GET
{
    function get()
    {
        return roleAuthorization('module_edit', BID) ? false : $this->tpl;
    }
}
