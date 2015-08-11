<?php
/**
 * ACMS_GET_Shop2_Cart_Notify
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Shop2_Cart_Notify extends ACMS_GET_Shop2
{
    function get()
    {	
        $this->initVars();

        $step   = $this->Post->get('step', 'apply');
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        if ( !empty($_SESSION['added']) ) {
            if ( 1
                and config('shop_tax_calculate') !== 'intax'
                and isset($_SESSION['added'][$this->item_price])
            ) {
                $_SESSION['added'][$this->item_price] -= $_SESSION['added'][$this->item_price.'#tax'];
            }
            $Tpl->add('added', $this->sanitize($_SESSION['added']));
            unset($_SESSION['added']);
        } else if ( !empty($_SESSION['deleted']) ) {
            $Tpl->add('deleted', $this->sanitize($_SESSION['deleted']));
            unset($_SESSION['deleted']);
        } else {
            return '';
        }

        return $Tpl->get();
    }
}