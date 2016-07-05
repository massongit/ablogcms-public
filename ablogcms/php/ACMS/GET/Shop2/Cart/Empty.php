<?php
/**
 * ACMS_GET_Shop2_Cart_Empty
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Shop2_Cart_Empty extends ACMS_GET_Shop2
{
    function get()
    {	
        $this->initVars();

        $step   = $this->Post->get('step', 'apply');
        $bid    = BID;

        if ( $step == 'result' ) {
            $cart = $this->session->get($this->cname.$bid);
            if ( !empty($cart) ) {
                $this->session->delete($this->cname.$bid);
                $this->session->save();
            }
        }

        return '';
    }
}