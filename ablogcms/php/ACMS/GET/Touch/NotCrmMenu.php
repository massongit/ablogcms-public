<?php
/**
 * ACMS_GET_Touch_NotCrmMenu
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotCrmMenu extends ACMS_GET_Touch_CrmMenu
{
    function get()
    {
        return in_array(ADMIN, $this->crm_admin_path) ? false : $this->tpl;
    }
}
