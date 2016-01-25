<?php
/**
 * ACMS_GET_Admin_Category_SelectGlobal
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Category_SelectGlobal extends ACMS_GET_Admin
{
    var $_scope  = array(
        'cid'   => 'global',
    );

    function get()
    {
        if ( !sessionWithContribution() || (!ADMIN && !is_ajax()) ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $target_bid = $this->Get->get('_bid', $this->bid);
        if ( !$target_bid ) {
            $target_bid = BID;
        }

        $order  = 'sort-asc';
        $order2 = config('category_select_global_order');
        if ( !empty($order2) ) {
            $order  = $order2;
        }

        $Tpl->add(null, $this->buildCategorySelect(
            $Tpl,
            $target_bid,
            $this->cid,
            'loop',
            true,
            $order
        ));
        return $Tpl->get();
    }
}
