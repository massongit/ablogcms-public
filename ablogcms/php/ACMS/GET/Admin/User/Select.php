<?php
/**
 * ACMS_GET_Admin_User_Select
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_User_Select extends ACMS_GET_Admin
{
    var $_scope = array(
        'uid'   => 'global',
    );
    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $Tpl->add(null, $this->buildUserSelect(
            $Tpl, BID, $this->uid, 'loop'
            , array('administrator', 'editor', 'contributor'), 'sort-asc'
        ));
        return $Tpl->get();
    }
}
