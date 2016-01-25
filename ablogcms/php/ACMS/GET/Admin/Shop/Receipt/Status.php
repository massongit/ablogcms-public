<?php
/**
 * ACMS_GET_Admin_Shop_Receipt_Status
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Shop_Receipt_Status extends ACMS_GET_Admin_Edit
{
    function get()
    {
        if ( !sessionWithAdministration() ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
    
        if ( !!ADMIN ) {
            $status = configArray('shop_receipt_status');
            foreach ( $status as $code ) {
                $Tpl->add(array('status:touch#'.$code, 'status:loop'));
                $Tpl->add('status:loop', array('status' => $code));
            }
        }

        return $Tpl->get();
    }

}