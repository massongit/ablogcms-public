<?php
/**
 * ACMS_GET_Touch_Role_BackupIndex
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_BackupIndex extends ACMS_GET
{
    function get()
    {
        if ( roleAvailableUser() ) {
            return ( roleAuthorization('backup_export', BID) || roleAuthorization('backup_import', BID) || !roleAvailableUser() ) ? $this->tpl : false;
        } else {
            return sessionWithAdministration(BID) ? $this->tpl : false;
        }
    }
}
