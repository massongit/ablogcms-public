<?php
/**
 * ACMS_GET_Touch_Role_NotMediaUpload
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Role_NotMediaUpload extends ACMS_GET
{
    function get()
    {
        return roleAuthorization('media_upload', BID) ? false : $this->tpl;
    }
}
