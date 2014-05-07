<?php
/**
 * ACMS_GET_Touch_Role_NotPublishEdit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_NotPublishEdit extends ACMS_GET
{
    function get()
    {
        return roleAuthorization('publish_edit', BID) ? false : $this->tpl;
    }
}
