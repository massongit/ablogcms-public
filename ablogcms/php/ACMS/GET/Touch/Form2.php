<?php
/**
 * ACMS_GET_Touch_Form2
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Touch_Form2 extends ACMS_GET
{
    function get()
    {
        return ('on' == config('form_edit_action_direct')) ? $this->tpl : false;
    }
}
