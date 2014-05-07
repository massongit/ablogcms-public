<?php
/**
 * ACMS_GET_Touch_Version
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Version extends ACMS_GET
{
    function get()
    {
        return enableRevision(false) ? $this->tpl : false;
    }
}