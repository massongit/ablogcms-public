<?php
/**
 * ACMS_GET_Admin_Role_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Role_Edit extends ACMS_GET_Admin_Edit
{
    function edit(& $Tpl)
    {
        if ( BID !== 1 || !sessionWithEnterpriseAdministration() ) die(); 
        $Role  =& $this->Post->getChild('role');

        if ( $Role->isNull() ) {
            if ( $rid = intval($this->Get->get('rid')) ) {
                $Role->overload(loadRole($rid));
            } else {
                $Role->set('entry_view', 'on');
            }
        }
        return true;
    }
}
