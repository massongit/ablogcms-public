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
    function failed($query='')
    {
        if ( !empty($query) ) $query = '?'.$query;
        die(header('Location: '.BASE_URL.LOGIN_SEGMENT.'/'.$query));
    }

    function get()
    {
        $DB             = DB::singleton(dsn());
        $Session        = ACMS_Session::singleton();
        $Config         = loadBlogConfig(BID);

        $state          = $this->Get->get('state');
        $code           = $this->Get->get('code');
        $bid            = $Session->get('fb_blog_id');
        $requestType    = $Session->get('fb_request');

        //------
        // check
        if ( 0
            || empty($state)
            || empty($code)
            || empty($bid)
            || empty($requestType)
            || !in_array($requestType, array('login', 'signup', 'addition'))
        ) {
            $Session->clear();
            $this->failed('auth=failed');
        }

        $Fb = new Facebook(array(
            'appId'  => $Config->get('facebook_app_id'),
            'secret' => $Config->get('facebook_app_secret'),
        ));

        //--------------
        // account info
        $account    = $Fb->api('/me');
        $fbid       = $account['id'];
        
        //----------------
        // login process
        if ( $requestType === 'login' ) {

            //-------------------
            // access restricted
            if ( SUID || !accessRestricted() ) {
                $this->failed('login=failed');
            }

            //----------------
            // authentication
            $SQL    = SQL::newSelect('user');
            $SQL->addWhereOpr('user_facebook_id', $fbid);
            $SQL->addWhereOpr('user_status', 'open');
            $SQL->addWhereOpr('user_login_expire', date('Y-m-d', REQUEST_TIME), '>=');
            $all    = $DB->query($SQL->get(dsn()), 'all');

            //--------
            // double
            if ( empty($all) or 1 < count($all) ) {
                $this->failed('login=failed');
            }

            $row    = $all[0];
            $uid    = intval($row['user_id']);
            $bid    = intval($row['user_blog_id']);
            $l      = ACMS_RAM::blogLeft($row['user_blog_id']);
            $r      = ACMS_RAM::blogRight($row['user_blog_id']);

            //----------------------
            // terminal restriction
            if ( 1
                && $row['user_auth'] !== 'administrator'
                && isset($row['user_login_terminal_restriction'])
                && $row['user_login_terminal_restriction'] === 'on'
            ) {
                $Cookie =& Field::singleton('cookie');
                if ( $Cookie->get('acms_config_login_terminal_restriction') !== sha1('permission'.UA) ) {
                    $this->failed('login=restriction');
                }
            }

            //----------------
            // sns auth check
            if ( !snsLoginAuth($uid, $bid) ) { 
                $Session->clear();
                $this->failed('login=failed');
            }

            //------------
            // role check
            if ( roleAvailableUser($uid) && !roleUserLoginAuth($row) ) {
                $this->failed('login=failed');
            }

            //---------------------
            // generate session id
            $sid = generateSession($row);

            $loginBid   = BID;
            if ( 1
                and ( 'on' == $row['user_login_anywhere'] || roleAvailableUser() )
                and !isBlogAncestor(BID, $bid, true)
            ) {
                $loginBid   = $bid;
            }

            $url    = acmsLink(array(
                'protocol'      => (SSL_ENABLE and ('on' == config('login_ssl'))) ? 'https' : 'http',
                'bid'           => $loginBid,
                'sid'           => $sid,
                'query'         => array(),
            ));

            $Session->clear();
            ACMS_POST::redirect($url, null, true);
        //----------------
        // signup process
        } else if ( $requestType === 'signup' ) {

            //----------------
            // sns auth check
            $BlogConfig = loadBlogConfig($bid);
            if ( $BlogConfig->get('snslogin') !== 'on' ) { 
                $Session->clear();
                $this->failed('auth=failed');
            }

            //------------
            // user check
            $SQL    = SQL::newSelect('user');
            $SQL->addWhereOpr('user_facebook_id', $fbid);
            $SQL->addWhereOpr('user_status', 'open');
            $SQL->addWhereOpr('user_login_expire', date('Y-m-d', REQUEST_TIME), '>=');
            $all    = $DB->query($SQL->get(dsn()), 'all');

            //--------
            // double
            if ( 0 < count($all) ) {
                $this->failed('auth=double');
            }

            //---------------
            // profile image
            $imgUri = "https://graph.facebook.com/$fbid/picture?type=normal";
            if ( $rsrc = file_get_contents($imgUri) ) {
                $POST       = new ACMS_POST();

                $extension  = substr(strrchr($imgUri, '.'), 1);
                $imgPath    = $POST->archivesDir().uniqueString().'.jpg';
                $POST->setupDir(dirname(ARCHIVES_DIR.$imgPath), intval(config('permission_dir')));
                @file_put_contents(ARCHIVES_DIR.$imgPath, $rsrc);

                $resizePath = $POST->archivesDir().'square64-'.uniqueString().'.jpg';
                $POST->copyImage(ARCHIVES_DIR.$imgPath, ARCHIVES_DIR.$resizePath, 64, 64, 64);
                @unlink(ARCHIVES_DIR.$imgPath);
                $imgPath = $resizePath;
            }

            //----------
            // add user
            $SQL    = SQL::newSelect('user');
            $SQL->setSelect('user_sort');
            $SQL->setOrder('user_sort', 'DESC');
            $SQL->addWhereOpr('user_blog_id', $bid);
            $sort   = intval($DB->query($SQL->get(dsn()), 'one')) + 1;
            $uid    = $DB->query(SQL::nextval('user_id', dsn()), 'seq');
            $username = isset($account['username']) ? $account['username'] : $account['id'];

            $SQL    = SQL::newInsert('user');
            $SQL->addInsert('user_id', $uid);
            $SQL->addInsert('user_sort', $sort);
            $SQL->addInsert('user_generated_datetime', date('Y-m-d H:i:s', REQUEST_TIME));
            $SQL->addInsert('user_blog_id', $bid);
            $SQL->addInsert('user_code', $username);
            $SQL->addInsert('user_status', 'open');
            $SQL->addInsert('user_name', $account['name']);
            $SQL->addInsert('user_pass', ACMS_POST::genPass(8));
            $SQL->addInsert('user_facebook_id', $fbid);
            $SQL->addInsert('user_mail', $username.'@example.com');
            $SQL->addInsert('user_mail_magazine', 'off');
            $SQL->addInsert('user_mail_mobile_magazine', 'off');
            $SQL->addInsert('user_icon', $imgPath);
            $SQL->addInsert('user_auth', config('subscribe_auth', 'subscriber'));
            $SQL->addInsert('user_indexing', 'on');
            $SQL->addInsert('user_login_anywhere', 'off');
            $SQL->addInsert('user_login_expire', '9999-12-31');
            $SQL->addInsert('user_updated_datetime', date('Y-m-d H:i:s', REQUEST_TIME));

            $DB->query($SQL->get(dsn()), 'exec');

            //---------------------
            // generate session id
            $SQL    = SQL::newSelect('user');
            $SQL->addWhereOpr('user_facebook_id', $fbid);
            $SQL->addWhereOpr('user_status', 'open');
            $SQL->addWhereOpr('user_login_expire', date('Y-m-d', REQUEST_TIME), '>=');
            $all    = $DB->query($SQL->get(dsn()), 'all');

            if ( 1 < count($all) ) {
                $this->failed('auth=failed');
            }
            $sid = generateSession($all[0]);

            $url    = acmsLink(array(
                'protocol'      => (SSL_ENABLE and ('on' == config('login_ssl'))) ? 'https' : 'http',
                'bid'           => $bid,
                'sid'           => $sid,
                'query'         => array(),
            ), false);

            $Session->clear();

            ACMS_POST::redirect($url, null, true);
        } else if ( $requestType === 'addition' ) {
            $query  = array('edit' => 'update');
            $bid    = $Session->get('fb_blog_id');
            $uid    = $Session->get('fb_user_id');

            //-------------------
            // access restricted
            if ( !SUID ) {
                $query['auth'] = 'failed';
            }

            //----------------
            // sns auth check
            if ( !snsLoginAuth($uid, $bid) ) { 
                $Session->clear();
                $this->failed('auth=failed');
            }

            //----------------
            // authentication
            $SQL    = SQL::newSelect('user');
            $SQL->addSelect('user_id');
            $SQL->addWhereOpr('user_facebook_id', $fbid);
            $all    = $DB->query($SQL->get(dsn()), 'all');

            //--------
            // double
            if ( 0 < count($all) ) {
                $query['auth'] = 'double';
            }

            if ( !isset($query['auth']) ) {
                $SQL    = SQL::newUpdate('user');
                $SQL->addUpdate('user_facebook_id', $fbid);
                $SQL->addWhereOpr('user_id', $uid);
                $DB->query($SQL->get(dsn()), 'exec');
            }

            $url    = acmsLink(array(
                'bid'           => $bid,
                'uid'           => $uid,
                'admin'         => 'user_edit',
                'query'         => $query,
            ), false);

            $Session->clear();

            ACMS_POST::redirect($url);
        }
    }
}
