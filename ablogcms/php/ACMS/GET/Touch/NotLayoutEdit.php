<?php
/**
 * ACMS_GET_Touch_NotLayoutEdit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotLayoutEdit extends ACMS_GET
{
    function get()
    {
        return ( 0
            || !sessionWithAdministration()
            || LAYOUT_EDIT
        ) ? false : $this->tpl;
    }
}
