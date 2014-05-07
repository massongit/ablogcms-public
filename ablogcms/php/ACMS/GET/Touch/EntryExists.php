<?php
/**
 * ACMS_GET_Touch_EntryExists
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_EntryExists extends ACMS_GET
{
    function get()
    {
        if ( 1
            && !!EID
            && ACMS_RAM::entryStatus(EID)
            && ACMS_RAM::entryStatus(EID) !== 'trash'
        ) {
            return $this->tpl;
        }
        return false;
    }
}
