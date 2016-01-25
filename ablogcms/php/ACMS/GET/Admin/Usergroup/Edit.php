<?php
/**
 * ACMS_GET_Admin_Usergroup_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Usergroup_Edit extends ACMS_GET_Admin_Edit
{
    function edit(& $Tpl)
    {
        if ( BID !== 1 || !sessionWithEnterpriseAdministration() ) die(); 
        $Usergropu  =& $this->Post->getChild('usergroup');

        if ( $Usergropu->isNull() ) {
            if ( $ugid = intval($this->Get->get('ugid')) ) {
                $Usergropu->overload(loadUsergroup($ugid));
            }
        }
        return true;
    }
}
