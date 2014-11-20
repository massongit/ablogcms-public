<?php
/**
 * ACMS_GET_Touch_CartStock
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_CartStock extends ACMS_GET
{
    function get()
    {
        return config('shop_stock_change') == 'on' ? $this->tpl : false;
    }
}
