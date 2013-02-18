<?php
/**
 * ACMS_GET_Touch_NotRootBlog
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_NotRootBlog extends ACMS_GET
{
    function get()
    {
        return RBID !== BID ? $this->tpl : false;
    }
}
