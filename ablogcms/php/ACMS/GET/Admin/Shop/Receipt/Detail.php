<?php
/**
 * ACMS_GET_Admin_Shop_Receipt_Detail
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Shop_Receipt_Detail extends ACMS_GET_Shop_Order_Detail
{
    function get()
    {
        if ( !sessionWithAdministration() ) return '';

        $this->initVars();
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $this->receiptDetail($Tpl);

        return $Tpl->get();
    }
}