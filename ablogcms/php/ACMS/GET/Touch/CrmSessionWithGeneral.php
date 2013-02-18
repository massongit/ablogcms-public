<?php
/**
 * ACMS_GET_Touch_CrmSessionWithGeneral
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_CrmSessionWithGeneral extends ACMS_GET
{
    function get()
    {
        return crmSessionWithGeneral() ? $this->tpl : false;
    }
}
