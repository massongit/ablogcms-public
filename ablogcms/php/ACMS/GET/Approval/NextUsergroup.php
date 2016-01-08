<?php
/**
 * ACMS_GET_Approval_NextUsergroup
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Approval_NextUsergroup extends ACMS_GET
{
    function get()
    {
        if ( !sessionWithApprovalRequest() ) return false;

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $vars           = array();
        $userGroup      = array();
        $lastGroup      = 0;
        $lastGroupName  = '';
        $currentGroup   = 0;

        if ( editionIsEnterprise() ) {

            $SQL    = SQL::newSelect('workflow');
            $SQL->addSelect('workflow_type');
            $SQL->addWhereOpr('workflow_status', 'open');
            $SQL->addWhereOpr('workflow_blog_id', BID);
            $type   = $DB->query($SQL->get(dsn()), 'one');

            // 並列承認
            if ( $type == 'parallel' ) {
                return '';
            }

            //-------------------------------------------
            // ワークフローの逆承認順序でユーザグループを列挙
            $Config     = loadBlogConfig(BID);
            $lastGroup  = $Config->getArray('workflow_last_group');

            $SQL    = SQL::newSelect('workflow_usergroup');
            $SQL->addSelect('usergroup_id');
            $SQL->addWhereOpr('workflow_blog_id', BID);
            $SQL->addOrder('workflow_sort', 'DESC');
            $all    = $DB->query($SQL->get(dsn()), 'all');
            if ( is_array($all) ) {
                foreach ( $all as $group ) {
                    $userGroup[] = $group['usergroup_id'];
                }
            }

            $nextGroup = array();
            foreach ( $userGroup as $ugid ) {
                $SQL = SQL::newSelect('usergroup_user');
                $SQL->addSelect('usergroup_id');
                $SQL->addWhereOpr('usergroup_id', $ugid);
                $SQL->addWhereOpr('user_id', SUID);
                if ( $group = $DB->query($SQL->get(dsn()), 'one') ) {
                    $currentGroup = $group;
                    break;
                }
                $nextGroup = array($ugid);
            }

            if ( empty($nextGroup) ) {
                $nextGroup = $lastGroup;
            }
            if ( empty($currentGroup) ) {
                $startGroup  = $Config->getArray('workflow_start_group');
                foreach ( $startGroup as $ugid ) {
                    $SQL = SQL::newSelect('usergroup_user');
                    $SQL->addSelect('usergroup_id');
                    $SQL->addWhereOpr('usergroup_id', $ugid);
                    $SQL->addWhereOpr('user_id', SUID);
                    if ( $group = $DB->query($SQL->get(dsn()), 'one') ) {
                        $currentGroup = $group;
                        break;
                    }
                }
            }
            $vars['currentGroup'] = $currentGroup;

            if ( !empty($nextGroup) ) {
                $SQL = SQL::newSelect('usergroup');
                $SQL->addWhereIn('usergroup_id', $nextGroup);
                $all = $DB->query($SQL->get(dsn()), 'all');

                if ( count($all) > 1 ) {
                    $nameAry = array();
                    foreach ( $all as $row ) {
                        $nameAry[] = $row['usergroup_name'];
                    }
                    $Tpl->add('group:loop', array(
                        'nextGroup'     => 0,
                        'nextGroupName' => implode(', ', $nameAry),
                    ));
                }
                foreach ( $all as $row ) {
                    $Tpl->add('group:loop', array(
                        'nextGroup'     => $row['usergroup_id'],
                        'nextGroupName' => $row['usergroup_name'],
                    ));
                }

                $SQL = SQL::newSelect('usergroup_user', 't_usergroup_user');
                $SQL->addLeftJoin('user', 'user_id', 'user_id', 't_user', 't_usergroup_user');
                $SQL->addWhereIn('usergroup_id', $nextGroup);
                $all = $DB->query($SQL->get(dsn()), 'all');

                foreach ( $all as $user ) {
                    $user['icon']       = loadUserIcon($user['user_id']);
                    $user['nextGroup']  = $user['usergroup_id'];
                    $Tpl->add('user:loop', $user);
                }
            }
            $Tpl->add(null, $vars);
        } else if ( editionIsProfessional() ) {
            $SQL    = SQL::newSelect('user');
            $SQL->addLeftJoin('blog', 'blog_id', 'user_blog_id');
            if ( config('blog_manage_approval') == 'on' ) {
                ACMS_Filter::blogTree($SQL, BID, 'self-ancestor');
            } else {
                $SQL->addWhereOpr('user_blog_id', BID);
            }
            ACMS_Filter::blogStatus($SQL);
            $SQL->addWhereIn('user_auth', array('editor', 'administrator'));

            $all = $DB->query($SQL->get(dsn()), 'all');

            $vars['currentGroup']   = 0;

            $Tpl->add('group:loop', array(
                'nextGroup'     => 0,
                'nextGroupName' => '編集者, 管理者',
            ));
            
            foreach ( $all as $user ) {
                $user['icon']       = loadUserIcon($user['user_id']);
                $user['nextGroup']  = 0;
                $Tpl->add('user:loop', $user);
            }
            $Tpl->add(null, $vars);
        }

        return $Tpl->get();
    }
}
