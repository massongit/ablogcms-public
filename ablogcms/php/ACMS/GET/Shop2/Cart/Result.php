<?php
/**
 * ACMS_GET_Shop2_Cart_Result
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Shop2_Cart_Result extends ACMS_GET_Shop2_Cart_List
{
    function get()
    {	
        $this->initVars();

        $this->initPrivateVars();

        $SESSION= $this->openSession();
        $TEMP   = $SESSION->getArray('portrait_cart');
        $Tpl = $this->buildList($TEMP);

        return $Tpl;
    }

}