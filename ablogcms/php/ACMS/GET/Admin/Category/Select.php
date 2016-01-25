<?php
/**
 * ACMS_GET_Admin_Category_Select
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Category_Select extends ACMS_GET_Admin
{
    var $_scope  = array(
        'cid'   => 'global',
    );

    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $Tpl->add(null, $this->buildCategorySelect($Tpl
            , BID, $this->cid, 'loop'
        ));
        return $Tpl->get();
    }
}
