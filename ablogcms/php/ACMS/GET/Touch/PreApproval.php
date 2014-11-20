<?php
/**
 * ACMS_GET_Touch_PreApproval
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_PreApproval extends ACMS_GET
{
    function get()
    {
        if ( !EID ) return false;

        $entry = ACMS_RAM::entry(EID);

        return ($entry['pre_approval'] == 'pre_approval') ? $this->tpl : null;
    }
}