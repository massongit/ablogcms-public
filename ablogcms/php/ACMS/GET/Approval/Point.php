<?php
/**
 * ACMS_GET_Approval_Point
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Approval_Point extends ACMS_GET
{
    function get()
    {
        if ( !enableApproval() ) return false;
        if ( !editionIsEnterprise() ) return false;
        if ( !RVID ) return false;

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $vars   = array();

        $SQL    = SQL::newSelect('workflow');
        $SQL->addSelect('workflow_type');
        $SQL->addSelect('workflow_public_point');
        $SQL->addSelect('workflow_reject_point');
        $SQL->addWhereOpr('workflow_status', 'open');
        $SQL->addWhereOpr('workflow_blog_id', BID);
        $work   = $DB->query($SQL->get(dsn()), 'row');

        $type       = $work['workflow_type'];
        $vars['approval_public_pass_point']    = $work['workflow_public_point'];
        $vars['approval_reject_pass_point']    = $work['workflow_reject_point'];
        $vars['approval_user_point']           = approvalUserPoint(BID);

        if ( $type != 'parallel' ) {
            return '';
        }

        $SQL    = SQL::newSelect('entry_rev');
        $SQL->addWhereOpr('entry_id', EID);
        $SQL->addWhereOpr('entry_rev_id', RVID);
        $SQL->addWhereOpr('entry_blog_id', BID);
        if ( $entry = $DB->query($SQL->get(dsn()), 'row') ) {
            foreach ( $entry as $key => $val ) {
                $key = substr($key, strlen('entry_'));
                $vars[$key] = $val;
            }
        }
        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}
