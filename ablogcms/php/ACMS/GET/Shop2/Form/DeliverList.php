<?php
/**
 * ACMS_GET_Shop2_Form_DeliverList
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Shop2_Form_DeliverList extends ACMS_GET_Shop2
{
    function get()
    {
        $this->initVars();
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $SESSION =& $this->openSession();
        $ADDRESS = $SESSION->getChild('address');

        $delivers = configArray('shop_order_deliver_label');
        $charge   = configArray('shop_order_deliver_charge');
        $common   = config('shop_order_shipping_common');

        foreach ( $delivers as $key => $deliver ) {

            $vars = array('deliver' => $deliver,
                          );

            /**
             * detect shipping
             */
            if ( isset($charge[$key]) && is_numeric($charge[$key]) ) {
                $vars += array('charge' => @$charge[$key]);
            } else {
                if ( is_numeric($common) )
                {
                $vars += array(
                                'charge' => intval(config('shop_order_shipping_common')),
                                'prefecture' => '全国一律',
                               );
                }
                else
                {
                $vars += array(
                                'charge' => $this->shipping($ADDRESS->get('prefecture')),
                                'prefecture' => $ADDRESS->get('prefecture'),
                               );
                }
            }

            if ( $SESSION->get('deliver') == $deliver ) {
                $vars += array('selected' => config('attr_selected'),
                               'checked'  => config('attr_checked'),
                               );
            }

            $Tpl->add('deliver:loop', $vars);
        }
        
        return $Tpl->get();
    }

    function shipping($prefecture)
    {
        $labels = configArray('shop_order_shipping_label');
        $charge = configArray('shop_order_shipping_charge');

        foreach ( $labels as $key => $label ) {
            if ( $prefecture == $label ) {
                return @$charge[$key];
            }
        }
    }

}