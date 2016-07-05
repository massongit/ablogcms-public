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
        if ( config('snslogin') !== 'on' ) { return false; }
        if ( !snsLoginAuth(UID) ) { return false; }

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $User       = loadUser(UID);
        $googleid   = $User->get('google_id');
        $twid       = $User->get('twitter_id');
        $fbid       = $User->get('facebook_id');

        // google auth check
        if ( config('google_login_client_id') ) {
            if ( empty($googleid) ) {
                $Tpl->add(array('google_notVerified', 'google'));
            } else {
                $Tpl->add(array('google_verified', 'google'), array(
                    'googleid'  => $googleid,
                ));
            }
            $Tpl->add('google');
        }

        // twitter auth check
        if ( config('twitter_sns_login_consumer_key') ) {
            if ( empty($twid) ) {
                $Tpl->add(array('tw_notVerified', 'twitter'));
            } else {
                $Tpl->add(array('tw_verified', 'twitter'), array(
                    'twid'  => $twid,
                ));
            }
            $Tpl->add('twitter');
        }   

        // facebook auth check
        if ( config('facebook_app_id') ) {
            if ( empty($fbid) ) {
                $Tpl->add(array('fb_notVerified', 'facebook'));
            } else {
                $Tpl->add(array('fb_verified', 'facebook'), array(
                    'fbid'  => $fbid,
                ));
            }
            $Tpl->add('facebook');
        }

        return $Tpl->get();
    }
}
