<?php
/**
 * ACMS_GET_Admin_Module_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Module_Edit extends ACMS_GET_Admin_Edit
{
    function auth()
    {
        if ( roleAvailableUser() ) {
            if ( !roleAuthorization('module_edit', BID) ) return false;
        } else {
            if ( !sessionWithAdministration() && !ACMS_POST::checkShortcut('Module_Update', ADMIN, 'mid', $mid) ) {
                $Tpl->add('error#auth');
                return true;
            }
        }
        return true;
    }

    function edit($Tpl)
    {
        $mid    = $this->Get->get('mid');
        $Module = $this->Post->getChild('module');

        if ( !sessionWithAdministration() && ACMS_POST::checkShortcut('Module_Update', ADMIN, 'mid', $mid) ) {
            $this->Post->set('shortcut', 'yes');
        }

        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('module');
        $SQL->addSelect('module_blog_id');
        $SQL->addWhereOpr('module_id', $mid);
        $mbid   = $DB->query($SQL->get(dsn()), 'one');

        if ( !empty($mbid) && !( 1
            and BID == $mbid
            and ( 0
                or sessionWithAdministration($mbid)
                or ( roleAvailableUser() and roleAuthorization('module_edit', $mbid) )
                or ACMS_POST::checkShortcut('Module_Update', ADMIN, 'mid', $mid)
            ) )
        ) {
            $Tpl->add('error#auth');
            return true;
        }

        if ( $Module->isNull() && (empty($this->edit) or ( $this->edit !== 'insert' && $this->edit !== 'delete')) ) {
            $_Module    = loadModule($mid);
            $_Module->setField('field_', $_Module->get('field'));

            if ( !!($start = $_Module->get('start')) ) {
                $date_time = explode(' ', $start);
                $date      = $date_time[0];
                $time      = $date_time[1];
                $_Module->setField('start_date', $date);
                $_Module->setField('start_time', $time);
            }
            if ( !!($end = $_Module->get('end')) ) {
                $date_time = explode(' ', $end);
                $date      = $date_time[0];
                $time      = $date_time[1];
                $_Module->setField('end_date', $date);
                $_Module->setField('end_time', $time);
            }
            $Module->overload($_Module);
        }

        $this->buildArgLabels($Module);

        if ( in_array($Module->get('name'), array('Blog_Field', 'Entry_Field', 'Category_Field', 'User_Field')) ) {
            $Module->delete('id');
        }
/*
        if ( !isBlogGlobal(BID) ) {
            $Module->delete('scope');
        } else if ( !$Module->get('scope') ) {
            $Module->set('scope', 'local');
        }
*/
        //--------
        // field
        $Field  =& $this->Post->getChild('field');
        if ( $Field->isNull() and !!$mid ) {
            $Field->overload(loadModuleField($mid));
        }

        return true;
    }
}
