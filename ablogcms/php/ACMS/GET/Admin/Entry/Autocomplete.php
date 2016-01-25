<?php
/**
 * ACMS_GET_Admin_Entry_Autocomplete
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Entry_Autocomplete extends ACMS_GET_Entry_Summary
{
    var $_axis = array(
        'bid'   => 'descendant-or-self',
        'cid'   => 'descendant-or-self',
    );

    var $_scope = array(
        'keyword'   => 'global',
    );

    function initVars()
    {
        return array(
            'order'            => 'datetime-desc',
            'limit'            => 20,
            'offset'           => 0,
            'indexing'         => 'on',
            'secret'           => 'off',
            'newtime'          => 'off',
            'unit'             => 1,
            'notfound'         => 'off',
            'notfoundStatus404'=> 'off',
            'noimage'          => 'on',
            'imageX'           => 0,
            'imageY'           => 0,
            'imageTrim'        => 'off',
            'imageZoom'        => 'off',
            'imageCenter'      => 'off',
            'pagerDelta'       => 3,
            'pagerCurAttr'     => ' class="cur"',
            'hiddenCurrentEntry'    => 'off',
            'loop_class'            => '',
            'categoryInfoOn'    => 'on',
            'categoryFieldOn'   => 'off',
        );
    }
}
