<?php
/**
 * ACMS_GET_Admin_Moblog_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Moblog_Edit extends ACMS_GET_Admin_Edit
{
    function edit(& $Tpl)
    {
        if ( !IS_LICENSED ) { return ''; }
        $mbid   = intval($this->Get->get('mbid'));
        $Moblog =& $this->Post->getChild('moblog');
        if ( !!$mbid and $Moblog->isNull() ) {
            $DB     = DB::singleton(dsn());
            $SQL    = SQL::newSelect('moblog');
            $SQL->addWhereOpr('moblog_id', $mbid);
            if ( !!($row = $DB->query($SQL->get(dsn()), 'row')) ) {
                $Moblog->set('status', $row['moblog_status']);
                $Moblog->set('mail', $row['moblog_mail']);
                $Moblog->set('server', $row['moblog_server']);
                $Moblog->set('user', $row['moblog_user']);
                $Moblog->set('pass', $row['moblog_pass']);
                $Moblog->set('allowed_mail', $row['moblog_allowed_mail']);
                $Moblog->set('entry_status', $row['moblog_entry_status']);
                $Moblog->set('image_size', $row['moblog_image_size']);
                $Moblog->set('image_align', $row['moblog_image_align']);
                $Moblog->set('text_unit', $row['moblog_text_unit']);
                $Moblog->set('user_id', $row['moblog_user_id']);
                $Moblog->set('category_id', $row['moblog_category_id']);
            }
        }

        //----------
        // category
        $this->buildCategorySelect(
            $Tpl, BID, intval($Moblog->get('category_id')), 'category:loop', true
        );

        //------
        // user
        $this->buildUserSelect(
            $Tpl, BID, intval($Moblog->get('user_id')), 'user:loop'
            , array('administrator', 'editor', 'contributor'), true
        );

        $Moblog->set('user_name', ACMS_RAM::userName(intval($Moblog->get('user_id'))));
        $Moblog->set('category_name', ACMS_RAM::categoryName(intval($Moblog->get('category_id'))));

        return true;
    }
}
