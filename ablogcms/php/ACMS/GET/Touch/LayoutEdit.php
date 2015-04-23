<?php
/**
 * ACMS_GET_Touch_LayoutEdit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_LayoutEdit extends ACMS_GET
{
    function get()
    {
        return ( 1
            and sessionWithAdministration()
            and LAYOUT_EDIT
        ) ? $this->tpl : false;
    }
}
