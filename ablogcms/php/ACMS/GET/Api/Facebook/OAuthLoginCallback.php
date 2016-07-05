<?php
/**
 * ACMS_GET_Api_Facebook_OAuthLoginCallback
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Api_Facebook_OAuthLoginCallback extends ACMS_GET_Api_Facebook
{
    private $user;
    private $fbid;

    function get()
    {
        $this->getAuthSession('fb_request', 'fb_blog_id', 'fb_user_id');

        $Session    = ACMS_Session::singleton();
        $Config     = loadBlogConfig(BID);

        $state      = $this->Get->get('state');
        $code       = $this->Get->get('code');

        // check
        if ( empty($state) || empty($code) ) {
            $this->loginFailed('login=failed');
            return false;
        }

        // get account info
        $Fb = new Facebook(array(
            'appId'  => $Config->get('facebook_app_id'),
            'secret' => $Config->get('facebook_app_secret'),
        ));
        $this->user  = $Fb->api('/me');
        $this->fbid  = $this->user['id'];

        // clear session
        $Session->clear();

        if ( $this->auth_type === 'login' ) {
            $url = $this->login();

        } else if ( $this->auth_type === 'signup' ) {
            $url = $this->signup();

        } else if ( $this->auth_type === 'addition' ) {
            $url = $this->addition();
        }

        ACMS_POST::redirect($url, null, true);
    }

    /**
     * google ログイン処理を実行する
     *
     */
    function login()
    {
        $user = loginAuthentication($this->fbid, 'user_facebook_id');
        if ( $user === false ) {
            $this->loginFailed('login=failed');
            return false;
        }

        $sid        = generateSession($user);  // generate session id
        $bid        = intval($user['user_blog_id']);
        $login_bid  = BID;

        if ( 1
            and ( 'on' == $user['user_login_anywhere'] || roleAvailableUser() )
            and !isBlogAncestor(BID, $bid, true)
        ) {
            $login_bid   = $bid;
        }

        return acmsLink(array(
            'protocol'      => (SSL_ENABLE and ('on' == config('login_ssl'))) ? 'https' : 'http',
            'bid'           => $login_bid,
            'sid'           => $sid,
            'query'         => array(),
        ));
    }

    /**
     * googleアカウントでサインアップ処理を行う
     *
     */
    function signup()
    {
        // sns auth check
        if ( loadBlogConfig($this->auth_bid)->get('snslogin') !== 'on' ) { 
            $this->loginFailed('auth=failed');
            return false;
        }

        // duplicate check
        $all = getUser($this->fbid, 'user_facebook_id');
        if ( 0 < count($all) ) {
            $this->loginFailed('auth=double');
            return false;
        }

        // create account
        $image_uri = "https://graph.facebook.com/$this->fbid/picture?type=large";
        $account = $this->extractAccountData($this->user);
        $account['icon'] = $this->userIconFromUri($image_uri);
        $this->addUserFromOauth($account);

        // get user data
        $all = getUser($this->fbid, 'user_facebook_id');
        if ( empty($all) || 1 < count($all) ) {
            $this->loginFailed('auth=double');
            return false;
        }

        // generate session id
        $sid = generateSession($all[0]);

        return acmsLink(array(
            'protocol'      => (SSL_ENABLE and ('on' == config('login_ssl'))) ? 'https' : 'http',
            'bid'           => $this->auth_bid,
            'sid'           => $sid,
            'query'         => array(),
        ), false);
    }

    /**
     * 既存のユーザーにFacebookアカウントを結びつける
     *
     */
    function addition()
    {
        $DB     = DB::singleton(dsn());
        $query  = array('edit' => 'update');

        // access restricted
        if ( !SUID ) {
            $query['auth'] = 'failed';
        }

        // sns auth check
        if ( !snsLoginAuth($this->auth_uid, $this->auth_bid) ) { 
            $this->loginFailed('auth=failed');
            return false;
        }

        // authentication
        $SQL    = SQL::newSelect('user');
        $SQL->addSelect('user_id');
        $SQL->addWhereOpr('user_facebook_id', $this->fbid);
        $all    = $DB->query($SQL->get(dsn()), 'all');

        // double
        if ( 0 < count($all) ) {
            $query['auth'] = 'double';
        }

        if ( !isset($query['auth']) ) {
            $SQL    = SQL::newUpdate('user');
            $SQL->addUpdate('user_facebook_id', $this->fbid);
            $SQL->addWhereOpr('user_id', $this->auth_uid);
            $DB->query($SQL->get(dsn()), 'exec');
        }

        return acmsLink(array(
            'protocol'  => (SSL_ENABLE and ('on' == config('login_ssl'))) ? 'https' : 'http',
            'bid'       => $this->auth_bid,
            'uid'       => $this->auth_uid,
            'admin'     => 'user_edit',
            'query'     => $query,
        ), false);
    }
}
