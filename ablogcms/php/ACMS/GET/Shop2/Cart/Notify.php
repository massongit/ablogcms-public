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
        $added  = $this->session->get('added');

        if ( !empty($added) ) {
            if ( 1
                and config('shop_tax_calculate') !== 'intax'
                and isset($added[$this->item_price])
            ) {
                $added[$this->item_price] -= $added[$this->item_price.'#tax'];
            }
            $Tpl->add('added', $this->sanitize($added));
            $this->session->delete('added');
            $this->session->save();
        } else if ( !empty($_SESSION['deleted']) ) {
            $Tpl->add('deleted', $this->sanitize($this->session->get('deleted')));
            $this->session->delete('deleted');
            $this->session->save();
        } else {
            return '';
        }

        return $Tpl->get();
    }
}