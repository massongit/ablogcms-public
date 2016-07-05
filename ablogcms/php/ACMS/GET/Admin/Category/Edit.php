<?php
/**
 * ACMS_GET_Admin_Category_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Category_Edit extends ACMS_GET_Admin_Edit
{
    function auth()
    {
        if ( roleAvailableUser() ) {
            if ( !roleAuthorization('category_edit', BID) ) return false;
        } else {
            if ( !sessionWithCompilation() ) return false;
        }
        return true;
    }

    function edit()
    {
        $Category   =& $this->Post->getChild('category');
        $Field      =& $this->Post->getChild('field');

        if ( $Category->isNull() ) {
            if ( CID ) {
                $Category->overload(loadCategory(CID));
                $Field->overload(loadCategoryField(CID));
            } else {
                $Category->set('status', 'open');
                $Category->set('scope', 'local');
                $Category->set('indexing', 'on');
            }
        }
        if ( !!($pid = $Category->get('parent')) && $pid != 0 ) {
            $Category->set('parent_name', ACMS_RAM::categoryName($pid));
            $Category->set('parent_code', ACMS_RAM::categoryCode($pid));
        }
        //if ( !isBlogGlobal(BID) ) $Category->setField('scope', null);

        return true;
    }
}
