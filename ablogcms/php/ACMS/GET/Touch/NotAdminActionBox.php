<?php
/**
 * ACMS_GET_Touch_NotAdminActionBox
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotAdminActionBox extends ACMS_GET
{
    function get()
    {
        return ('on' != config('admin_action_box') ) ? $this->tpl : false;
    }
}
