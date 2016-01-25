<?php
/**
 * ACMS_GET_Admin_User_Sns
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_User_Sns extends ACMS_GET_Admin
{
    function get()
    {
        if ( !SUID || !sessionWithSubscription() ) { return false; }
        if ( UID <> SUID && !sessionWithAdministration() ) { return false; }
        if ( config('snslogin') !== 'on' ) { return false; }
        if ( !snsLoginAuth(UID) ) { return false; }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $User   = loadUser(UID);
        $twid   = $User->get('twitter_id');
        $fbid   = $User->get('facebook_id');

        //----------------------
        // twitter auth check
        if ( empty($twid) ) {
            $Tpl->add('tw_notVerified');
        } else {
            $Tpl->add('tw_verified', array(
                'twid'  => $twid,
            ));
        }

        //----------------------
        // facebook auth check
        if ( empty($fbid) ) {
            $Tpl->add('fb_notVerified');
        } else {
            $Tpl->add('fb_verified', array(
                'fbid'  => $fbid,
            ));
        }

        return $Tpl->get();
    }
}
