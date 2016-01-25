<?php
/**
 * ACMS_GET_Admin_Tag_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Tag_Edit extends ACMS_GET_Admin_Edit
{
    var $_scope = array(
        'tag'   => 'global',
    );

    function edit()
    {
        if ( !$this->Post->isExists('tag') ) $this->Post->set('tag', $this->Q->get('tag'));
        return true;
    }

    function _get()
    {
        if ( 'tag_edit' <> ADMIN ) return false;
        if ( !TAG ) return false;

        if ( roleAvailableUser() ) {
            if ( !roleAuthorization('tag_edit', BID) ) return false;
        } else {
            if ( !sessionWithCompilation() ) return false;
        }
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $Tpl->add(null, array('tag' => TAG));
        return $Tpl->get();
    }
}
