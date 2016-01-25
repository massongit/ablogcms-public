<?php
/**
 * ACMS_GET_Admin_User_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_User_Edit extends ACMS_GET_Admin_Edit
{
    function edit()
    {
        if ( UID <> SUID and !sessionWithAdministration() ) { return true; }

        $User   = loadUser(UID);
        $User->delete('pass');
        $User_  = $this->Post->getChild('user');
        $User->overload($User_);

        //---------
        // default
        if ( $User->isNull() ) {
            $User->set('status', 'open');
            $User->set('auth', 'contributor');
            $User->set('indexing', 'on');
            $User->set('mail_magazine', 'on');
            $User->set('mail_mobile_magazine', 'on');
            $User->set('login_anywhere', 'off');
            $User->set('login_expire', '9999-12-31');
        }

        if ( !!UID ) {
            $User->set('oldPass', $User->get('oldPass'));
        }

        if ( GETTEXT_TYPE !== 'user' ) {
            $User->delete('locale');
        }

        if ( !sessionWithAdministration() ) {
            $User->delete('status');
            $User->delete('auth');
            $User->delete('indexing');
            $User->delete('login_anywhere');
            $User->delete('login_expire');
            $User->delete('login_terminal_restriction');

            if ( SUID !== UID ) {
                $User->delete('locale');
            }
        } else {
            if ( SUID == UID ) {
                $User->delete('status');
                $User->delete('auth');
                $User->delete('login_expire');
            }

            if ( RBID <> SBID ) {
                $User->delete('login_anywhere');
            }

            if ( ACMS_RAM::userAuth(UID) === 'administrator' ) {
                $User->delete('login_terminal_restriction');
            }
        }

        $this->Post->addChild('user', $User);

        $Field  =& $this->Post->getChild('field');
        if ( $Field->isNull() and !!UID ) {
            $Field->overload(loadUserField(UID));
        }

        return true;
    }
}
