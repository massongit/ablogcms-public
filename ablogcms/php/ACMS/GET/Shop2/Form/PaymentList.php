<?php
/**
 * ACMS_GET_Shop2_Form_PaymentList
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Shop2_Form_PaymentList extends ACMS_GET_Shop2
{
    function get()
    {
        $this->initVars();
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $SESSION =& $this->openSession();

        $payments = configArray('shop_order_payment_label');
        $charge   = configArray('shop_order_payment_charge');
        
        foreach ( $payments as $key => $payment ) {

            $vars = array('payment' => $payment,
                          'charge'  => @$charge[$key],
                          );
  
            if ( $SESSION->get('payment') == $payment ) {
                $vars += array('selected' => config('attr_selected'),
                               'checked'  => config('attr_checked'),
                               );
            }

            $Tpl->add('payment:loop', $vars);
        }
        
        return $Tpl->get();
    }
}