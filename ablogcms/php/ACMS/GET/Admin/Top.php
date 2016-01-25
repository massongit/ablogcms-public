<?php
/**
 * ACMS_GET_Admin_Top
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Top extends ACMS_GET_Admin
{
    function get()
    {
        if ( 'top' <> ADMIN ) return false;
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        return $Tpl->get();
    }
}
