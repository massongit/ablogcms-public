<?php
/**
 * ACMS_GET_Touch_Role_NotPublishExec
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_NotPublishExec extends ACMS_GET
{
    function get()
    {
        return roleAuthorization('publish_exec', BID) ? false : $this->tpl;
    }
}
