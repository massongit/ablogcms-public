<?php
/**
 * ACMS_GET_Admin_CheckList
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_CheckList extends ACMS_GET
{
    function get()
    {
        if ( !sessionWithSubscription() ) return '';

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $DB     = DB::singleton(dsn());
        $keyword = $this->Get->get('keyword');

        if ( !empty($keyword) ) {
            $SQL = SQL::newSelect('config');
            $SQL->addLeftJoin('module', 'config_module_id', 'module_id');
            $SQL->addLeftJoin('rule', 'config_rule_id', 'rule_id');
            $SQL->addWhereOpr('config_key', '%'.$keyword.'%', 'LIKE', 'OR');
            $SQL->addWhereOpr('config_value', '%'.$keyword.'%', 'LIKE', 'OR');
            $SQL->setLimit(300);
            $configAll = $DB->query($SQL->get(dsn()), 'all');
            if ( is_array($configAll) && count($configAll) > 0 ) {
                foreach ( $configAll as $config ) {
                    $bid = $config['config_blog_id'];
                    $rid = $config['config_rule_id'];
                    $mid = $config['config_module_id'];
                    $configVars = array(
                        'bid'   => $bid,
                        'rid'   => $rid,
                        'mid'   => $mid,
                        'rcode' => $config['rule_name'],
                        'mcode' => $config['module_identifier'],
                        'key'   => $config['config_key'],
                        'value' => $config['config_value'],
                    );
                    $configVars['blogUrl']  = acmsLink(array(
                        'bid'       => $bid,
                        'admin'     => 'config_index',
                    ));
                    $configVars['ruleUrl']  = acmsLink(array(
                        'bid'       => $bid,
                        'query'     => array(
                            'rid'   => $rid,
                        ),
                        'admin'     => 'config_index',
                    ));
                    $configVars['moduleUrl']  = acmsLink(array(
                        'bid'       => $bid,
                        'query'     => array(
                            'rid'   => $rid,
                            'mid'   => $mid,
                        ),
                        'admin'     => 'module_edit',
                    ));
                    $Tpl->add(array('config:loop', 'config'), $configVars);
                }
                $Tpl->add('config');
            }
        }

        if ( LICENSE_BLOG_LIMIT == 2147483647 ) {
            $Tpl->add(array('userUnlimited', 'license'));
        } else {
             $Tpl->add(array('userLimited', 'license'), array(
                'limit' => LICENSE_BLOG_LIMIT,
            ));
        }

        $license = array();
        $license['domain']  = LICENSE_DOMAIN;
        $license['version'] = VERSION;
        switch ( LICENSE_EDITION ) {
            case 'enterprise':
                $license['edition'] = 'Enterprise';
                break;
            case 'professional':
                $license['edition'] = 'Professional';
                break;
            default:
                $license['edition'] = 'Standard';
                break;
        }

        $offset = strlen(DOMAIN) - strlen(LICENSE_DOMAIN);
        if ( 1
            and !((0 <= $offset) and LICENSE_DOMAIN == substr(DOMAIN, $offset))
            and !is_private_ip(DOMAIN)
        ) {
            $license['matchDomain'] = 'ドメインが一致していません';
            $license['caution']     = 'caution';
        }

        //--------------
        // license type
        $type = array();
        if ( defined('LICENSE_OPTION_SUBDOMAIN') && !!LICENSE_OPTION_SUBDOMAIN ) {
            $type[] = 'サブドメイン';
        }
        if ( defined('LICENSE_OPTION_OWNDOMAIN') && !!LICENSE_OPTION_OWNDOMAIN ) {
            $type[] = '独自ドメイン';
        }
        if ( defined('LICENSE_OPTION_PLUSDOMAIN') && intval(LICENSE_OPTION_PLUSDOMAIN) > 0 ) {
            $type[]   = 'プラス独自ドメイン('.LICENSE_OPTION_PLUSDOMAIN.')';
        }
        if ( defined('LICENSE_OPTION_OEM') && !!LICENSE_OPTION_OEM ) {
            $type[] = 'OEMライセンス';
        }
        if ( LICENSE_BLOG_LIMIT == 2147483647 ) {
            $type[] = '無制限ユーザーライセンス';
        }
        if ( LICENSE_BLOG_LIMIT == 1 ) {
             $type[] = 'ライト版ライセンス（フリー）';
        }
        if ( empty($type) ) {
            $type[] = 'スタンダードライセンス';
        }

        foreach ( $type as $i => $val ) {
            if ( $i > 0 ) {
                $Tpl->add(array('licenseType:glue', 'licenseType:loop', 'license'));
            }
            $Tpl->add(array('licenseType:loop', 'license'), array(
                'type'  => $val,
            ));
        }

        //----------------
        // license option
        $option = array();
        if ( defined('LICENSE_PLUGIN_POPMOBLOG') && !!LICENSE_PLUGIN_POPMOBLOG ) {
            $option[] = 'モグログ(Cron版)';
        }
        if ( defined('LICENSE_PLUGIN_SHOP_PRO') && !!LICENSE_PLUGIN_SHOP_PRO ) {
            $option[] = 'ショップ';
        }
        if ( defined('LICENSE_PLUGIN_MAILMAGAZINE') && !!LICENSE_PLUGIN_MAILMAGAZINE ) {
            $option[] = 'メールマガジン';
        }
        foreach ( $option as $i => $val ) {
            if ( $i > 0 ) {
                $Tpl->add(array('option:glue', 'option:loop', 'license'));
            }
            $Tpl->add(array('option:loop', 'license'), array(
                'option'  => $val,
            ));
        }

        $Tpl->add('license', $license);

        //-------------
        // debug mode
        $debugMode['mode'] = ( defined('DEBUG_MODE') && DEBUG_MODE ) ? 'ON' : 'OFF';
        if ( defined('DEBUG_MODE') && DEBUG_MODE ) {
            $debugMode['caution'] = 'caution';
        }
        $Tpl->add('debugMode', $debugMode);

        //-------------
        // debug mode
        $benchmarkMode['mode'] = ( defined('BENCHMARK_MODE') && BENCHMARK_MODE ) ? 'ON' : 'OFF';
        if (defined('BENCHMARK_MODE') && BENCHMARK_MODE ) {
            $benchmarkMode['caution'] = 'caution';
        }
        $Tpl->add('benchmarkMode', $benchmarkMode);

        //------------
        // 画像エンジン
        if ( class_exists('Imagick') && config('image_magick') == 'on' ) {
            $Tpl->add('imgLibrary', array(
                'mode'  => 'ImageMagick',
            ));
        } else {
            $Tpl->add('imgLibrary', array(
                'mode'  => 'GD',
            ));
        }

        //-------
        // cache
        $SQL = SQL::newSelect('blog');
        $SQL->setOrder('blog_id');

        foreach ( $DB->query($SQL->get(dsn()), 'all') as $blog ) {
            $bid = $blog['blog_id'];
            $this->addBlogInfo($Tpl, $bid);

            $SQL = SQL::newSelect('rule');
            $SQL->addSelect('rule_id');
            $SQL->addWhereOpr('rule_blog_id', $bid);
            foreach ( $DB->query($SQL->get(dsn()), 'all') as $rule ) {
                $rid = $rule['rule_id'];
                $this->addBlogInfo($Tpl, $bid, $rid);
            }
        }

        //------
        // form
        $SQL = SQL::newSelect('form');
        $SQL->addOrder('form_blog_id');
        $SQL->addOrder('form_id');
        $formAll = $DB->query($SQL->get(dsn()), 'all');

        if ( is_array($formAll) ) {
            foreach ( $formAll as $form ) {
                $formField = unserialize($form['form_data']);
                $formVars = $this->buildField($formField, $Tpl, array('formGeneral:loop'));
                $formVars['bid']    = $form['form_blog_id'];
                $formVars['fmid']   = $form['form_id'];
                $formVars['editUrl']  = acmsLink(array(
                    'bid'       => $form['form_blog_id'],
                    'query'     => array(
                        'fmid'   => $form['form_id'],
                    ),
                    'admin'     => 'form_edit',
                ));

                $Tpl->add('formGeneral:loop', $formVars);

                $formVars = $this->buildField($formField, $Tpl, array('formAdmin:loop'));
                $formVars['bid']    = $form['form_blog_id'];
                $formVars['fmid']   = $form['form_id'];
                $formVars['editUrl']  = acmsLink(array(
                    'bid'       => $form['form_blog_id'],
                    'query'     => array(
                        'fmid'   => $form['form_id'],
                    ),
                    'admin'     => 'form_edit',
                ));
                $Tpl->add('formAdmin:loop', $formVars);
            }
        }
        if ( !empty($keyword) ) {
            $Tpl->add(null, array(
                'keyword'   => $keyword,
            ));
        }

        return $Tpl->get();
    }

    function addBlogInfo(& $Tpl, $bid=0, $rid=null)
    {
        $blogConfig = array(
            'bid'               => $bid,
            'rid'               => $rid,
            'cache'             => $this->config('cache', $bid, $rid),
            'cacheSensitivity'  => $this->config('cache_sensitivity', $bid, $rid),
            'logAccess'         => $this->config('log_access', $bid, $rid),
        );
        $blogConfig['editUrl']  = acmsLink(array(
            'bid'       => $bid,
            'query'     => array(
                'rid'   => $rid,
            ),
            'admin'     => 'config_function',
        ));
        $Tpl->add(array('cacheCaution', 'blog:loop'), array(
            'caution'   => ( $blogConfig['cache'] == 'off' ) ? 'caution' : '',
        ));
        $Tpl->add(array('cacheSensitivityCaution', 'blog:loop'), array(
            'caution'   => ( $blogConfig['cacheSensitivity'] == 'low' ) ? 'caution' : '',
        ));

        $Tpl->add('blog:loop', $blogConfig);
    }

    function config($key=null, $bid=1, $rid=null)
    {
        $DB = DB::singleton(dsn());
        $SQL = SQL::newSelect('config');
        $SQL->addSelect('config_value');
        $SQL->addWhereOpr('config_blog_id', $bid);
        $SQL->addWhereOpr('config_key', $key);
        $SQL->addWhereOpr('config_rule_id', $rid);

        if ( $config = $DB->query($SQL->get(dsn()), 'one') ) {
            return $config;
        } else {
            $config = loadDefaultConfig();
            return $config[$key];
        }
    }
}