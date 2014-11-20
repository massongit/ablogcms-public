<?php
/**
 * ACMS_GET_Touch_NotCartStock
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotCartStock extends ACMS_GET
{
    function get()
    {
        return config('shop_stock_change') == 'on' ? false : $this->tpl;
    }
}
