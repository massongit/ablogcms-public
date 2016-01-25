<?php
/**
 * ACMS_GET_Admin_Trim
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Trim extends ACMS_GET
{
    function get()
    {
        $Tpl = $this->tpl;

        $Tpl = strip_tags($Tpl);
        $Tpl = trim(mb_convert_kana($Tpl, "s"));
        $Tpl = str_replace(array("\r\n","\r","\n"), '', $Tpl);

        return $Tpl;
    }
}
