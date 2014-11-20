<?php
/**
 * ACMS_GET_Approval_History
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Approval_History extends ACMS_GET
{
    function get()
    {
        if ( !enableApproval() ) return false;
        if ( !RVID ) return false;

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $vars   = array();

        $SQL    = SQL::newSelect('approval');
        $SQL->addWhereOpr('approval_revision_id', RVID);
        $SQL->addWhereOpr('approval_entry_id', EID);
        $SQL->addWhereOpr('approval_blog_id', BID);
        $SQL->setOrder('approval_datetime', 'DESC');
        if ( !($all = $DB->query($SQL->get(dsn()), 'all')) ) return '';

        foreach ( $all as $row ) {
            //--------------
            // 操作ユーザ情報
            $reqUserField   = loadUser($row['approval_request_user_id']);
            $reqUser        = $this->buildField($reqUserField, $Tpl, array('requestUser', 'approval:loop'));

            $Tpl->add(array('requestUser', 'approval:loop'), $reqUser);

            //------------------
            // 担当者 承認依頼のみ
            if ( $row['approval_type'] === 'request' ) {
                $receive['deadline']    = $row['approval_deadline_datetime'];
                if ( !!$row['approval_receive_user_id'] ) {
                    $receive['userOrGroupp'] = ACMS_RAM::userName($row['approval_receive_user_id']);
                } else if ( !!$row['approval_receive_usergroup_id'] ) {
                    $SQL    = SQL::newSelect('usergroup');
                    $SQL->addSelect('usergroup_name');
                    $SQL->addWhereOpr('usergroup_id', $row['approval_receive_usergroup_id']);
                    $groupName = $DB->query($SQL->get(dsn()), 'one');
                    $receive['userOrGroupp'] = $groupName;
                }
                $Tpl->add(array('receiveUser', 'approval:loop'), $receive);
            }

            //---------
            // 承認情報
            $approvalField  = new Field();
            foreach ( $row as $key => $val ) {
                $key_       = substr($key, strlen('approval_'));
                $approvalField->add($key_, $val);
            }

            $SQL    = SQL::newSelect('entry_rev');
            $SQL->addSelect('entry_status');
            $SQL->addWhereOpr('entry_rev_id', RVID);
            $SQL->addWhereOpr('entry_id', EID);
            $SQL->addWhereOpr('entry_blog_id', BID);
            if ( $revStatus = $DB->query($SQL->get(dsn()), 'one') ) {
                if ( $approvalField->get('type') === 'request' && $revStatus === 'trash' ) {
                    $approvalField->set('type', 'trash');
                }
            }
            $approval  = $this->buildField($approvalField, $Tpl, array('approval:loop'));

            $Tpl->add('approval:loop', $approval);
        }
        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}