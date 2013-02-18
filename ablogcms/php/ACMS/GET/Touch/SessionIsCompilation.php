<?php
/**
 * ACMS_GET_Touch_SessionIsCompilation
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_SessionIsCompilation extends ACMS_GET
{
    var $_scope = array(
        'bid'   => 'global',
    );

    function get()
    {
        return ( isSessionEditor() && sessionWithCompilation($this->bid) ) ? $this->tpl : false;
    }
}
