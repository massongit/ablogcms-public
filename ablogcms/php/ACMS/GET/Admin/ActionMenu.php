<?php
/**
 * ACMS_GET_Admin_ActionMenu
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_ActionMenu extends ACMS_GET
{
    function get()
    {
        if ( 0
            || !sessionWithSubscription()
            || LAYOUT_PREVIEW
        ) {
            return '';
        }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $vars   = array();

        $expire = null;
        if ( IS_LICENSED ) {
            if ( 0
                || is_int(strpos(DOMAIN, LICENSE_DOMAIN))
                || is_private_ip(DOMAIN)
            ) {
                $status = 'licensed';
            } else if ( !is_null(LICENSE_EXPIRE) ) {
                $status = 'limited';
                $expire = LICENSE_EXPIRE;
            } else {
                $status = 'trial';
            }
        }
        $Tpl->add('status#'.$status, array('expire' => $expire));

        $vars   += array(
            'name'      => ACMS_RAM::userName(SUID),
            'icon'      => loadUserIcon(SUID),
            'logout'    => acmsLink(array('_inherit' => true)),
        );

        if ( sessionWithContribution() ) {
            if ( IS_LICENSED ) {
                $Tpl->add('insert', array('cid' => CID));
                foreach ( configArray('ping_weblog_updates_endpoint') as $val ) {
                    $Tpl->add('ping_weblog_updates_endpoint:loop', array(
                        'ping_weblog_updates_endpoint'  => $val,
                    ));
                }
                foreach ( configArray('ping_weblog_updates_extended_endpoint') as $val ) {
                    $Tpl->add('ping_weblog_updates_extended_endpoint:loop', array(
                        'ping_weblog_updates_extended_endpoint' => $val,
                    ));
                }

                if ( IS_LICENSED ) {
                    $DB     = DB::singleton(dsn());
                    $SQL    = SQL::newSelect('moblog');
                    $SQL->setSelect('moblog_id');
                    $SQL->addWhereOpr('moblog_blog_id', BID);
                    if ( !sessionWithAdministration() ) {
                        $SQL->addWhereOpr('moblog_user_id', SUID);
                    }
                    $SQL->setLimit(1);
                    if ( !!$DB->query($SQL->get(dsn()), 'one') ) $Tpl->add('moblog');
                }
            }
        }

        //-------
        // admin
        $Tpl->add('admin');

        //---------------------
        // approval infomation
        if ( approvalAvailableUser() ) {
            if ( $amount = ACMS_GET_Approval_Notification::notificationCount() ) {
                $Tpl->add('approval', array(
                    'badge' => $amount,
                    'url'   => acmsLink(array(
                        'bid'   => BID,
                        'admin' => 'approval_notification',
                    )),
                ));
            }
        }

        $Tpl->add(null, $vars);
        return $Tpl->get();
    }
}
