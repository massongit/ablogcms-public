<?php
/**
 * ACMS_GET_Admin_Menu
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Menu extends ACMS_GET_Admin
{
    function linkCheck($type)
    {
        if ( strpos($type, '_') === false && $type !== 'checklist' ) $type = $type.'_';
        $reg    = '/^'.$type.'/';
        $stay   = ' class="stay"';
        if ( $type == 'top_' && ADMIN == 'top' ) {
            return $stay;
        }
        if ( preg_match($reg, ADMIN) ) {
            return $stay;
        } else {
            return '';
        }
    }

    function roleAuth(& $Tpl)
    {
        $Tpl->add('dashboard', array(
            'url'   => acmsLink(array('admin' => 'top', 'bid' => BID)),
            'stay'  => $this->linkCheck('top'),
        ));

        if ( approvalAvailableUser(SUID) ) {
            $approval = array(
                'url'   => acmsLink(array('admin' => 'approval_notification', 'bid' => BID)),
                'stay'  => $this->linkCheck('approval_notification'),
            );
            if ( $badge = ACMS_GET_Approval_Notification::notificationCount() ) {
                $approval['badge'] = $badge;
            }
            $Tpl->add('approval#notification', $approval);
        }

        if ( roleAuthorization('entry_edit', BID, EID) ) {
            $Tpl->add('entry#index', array(
                'url'   => acmsLink(array('admin' => 'entry_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('entry_index'),
            ));
            $Tpl->add('entry#trash', array(
                'url'   => acmsLink(array('admin' => 'entry_trash', 'bid' => BID)),
                'stay'  => $this->linkCheck('entry_trash'),
            ));
            if ( IS_LICENSED ) $Tpl->add('entry#insert');
        }

        if ( roleAuthorization('category_edit', BID) ) {
            $Tpl->add('category#index', array(
                'url'   => acmsLink(array('admin' => 'category_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('category'),
            ));
            if ( IS_LICENSED ) {
                $Tpl->add('category#insert', array(
                    'url'   => acmsLink(array('admin' => 'category_edit', 'bid' => BID)),
                    'stay'  => $this->linkCheck('category'),
                ));
            }
        }

        if ( roleAuthorization('tag_edit', BID) ) {
            $Tpl->add('tag', array(
                'url'   => acmsLink(array('admin' => 'tag_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('tag'),
            ));
        }

        if ( roleAuthorization('media_upload', BID) || roleAuthorization('media_edit', BID) ) {
            if ( config('media_library') === 'on' ) {
                $Tpl->add('media#index', array(
                    'url'   => acmsLink(array('bid' => BID, 'admin' => 'media_index')),
                    'stay'  => $this->linkCheck('media'),
                ));
            }
        }

        if ( roleAuthorization('rule_edit', BID) ) {
            $Tpl->add('rule#index', array(
                'url'   => acmsLink(array('admin' => 'rule_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('rule'),
            ));
            if ( IS_LICENSED ) {
                $Tpl->add('rule#insert', array(
                    'url'   => acmsLink(array('admin' => 'rule_edit', 'bid' => BID)),
                    'stay'  => $this->linkCheck('rule'),
                ));
            }
        }

        if ( roleAuthorization('publish_edit', BID) || roleAuthorization('publish_exec', BID) ) {
            $Tpl->add('publish#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'publish_index')),
                'stay'  => $this->linkCheck('publish'),
            ));

        }

        if ( roleAuthorization('config_edit', BID) ) {
            if ( IS_LICENSED ) {
                $Tpl->add('config#index', array(
                    'url'   => acmsLink(array('admin' => 'config_index', 'bid' => BID)),
                    'stay'  => $this->linkCheck('config'),
                ));
            }
        }

        if ( roleAuthorization('module_edit', BID) ) {
            $Tpl->add('module#index', array(
                'url'   => acmsLink(array('admin' => 'module_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('module'),
            ));
            if ( IS_LICENSED ) {
                $Tpl->add('module#insert', array(
                    'url'   => acmsLink(array('admin' => 'module_edit', 'bid' => BID)),
                    'stay'  => $this->linkCheck('module'),
                ));
            }
        }

        if ( roleAuthorization('backup_export', BID) || roleAuthorization('backup_import', BID) ) {
            $Tpl->add('backup#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'backup_index')),
                'stay'  => $this->linkCheck('backup'),
            ));
        }

        if ( roleAuthorization('form_view', BID) || roleAuthorization('form_edit', BID) ) {
            $Tpl->add('form#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'form_index')),
                'stay'  => $this->linkCheck('form'),
            ));
        }

        if ( roleAuthorization('admin_etc', BID) ) {
            $Tpl->add('comment', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'comment_index')),
                'stay'  => $this->linkCheck('comment'),
            ));
            $Tpl->add('trackback', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'trackback_index')),
                'stay'  => $this->linkCheck('trackback'),
            ));
            $Tpl->add('blog#index', array(
                'url'   => acmsLink(array('admin' => 'blog_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('blog_index'),
            ));
            $Tpl->add('blog#edit', array(
                'url'   => acmsLink(array('admin' => 'blog_edit', 'bid' => BID)),
                'stay'  => $this->linkCheck('blog_edit'),
            ));
            $Tpl->add('alias#index', array(
                'url'   => acmsLink(array('admin' => 'alias_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('alias'),
            ));
            $Tpl->add('user#index', array(
                'url'   => acmsLink(array('admin' => 'user_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('user'),
            ));
            $Tpl->add('shortcut#index', array(
                'url'   => acmsLink(array('admin' => 'shortcut_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('shortcut'),
            ));
            $Tpl->add('schedule#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'schedule_index')),
                'stay'  => $this->linkCheck('schedule'),
            ));
            $Tpl->add('moblog#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'moblog_index')),
                'stay'  => $this->linkCheck('moblog'),
            ));
            $Tpl->add('import#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'import_index')),
                'stay'  => $this->linkCheck('import'),
            ));
            $Tpl->add('app#index', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'app_index')),
                'stay'  => $this->linkCheck('app_index'),
            ));
            $Tpl->add('checklist', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'checklist')),
                'stay'  => $this->linkCheck('checklist'),
            ));
            $Tpl->add('cart#menu', array(
                'url'   => acmsLink(array('bid' => BID, 'admin' => 'cart_menu')),
                'stay'  => $this->linkCheck('cart_menu'),
            ));
            if ( defined('LICENSE_PLUGIN_SHOP_PRO') and LICENSE_PLUGIN_SHOP_PRO ) {
                $Tpl->add('shop#menu', array(
                    'url'   => acmsLink(array('bid' => BID, 'admin' => 'shop_menu')),
                    'stay'  => $this->linkCheck('shop'),
                ));
            }
            if ( IS_LICENSED ) {
                $Tpl->add('user#insert', array(
                    'url'   => acmsLink(array('admin' => 'user_edit', 'bid' => BID)),
                    'stay'  => $this->linkCheck('user#insert'),
                ));
                if ( isBlogGlobal(SBID) ) {
                    $Tpl->add('blog#insert', array(
                        'url'   => acmsLink(array(
                            'admin' => 'blog_edit',
                            'alt'   => 'insert',
                        )),
                        'stay'  => $this->linkCheck('blog'),
                    ));
                }
            }
        }
    }

    function normalAuth(& $Tpl)
    {
        $Tpl->add('dashboard', array(
            'url'   => acmsLink(array('admin' => 'top', 'bid' => BID)),
            'stay'  => $this->linkCheck('top'),
        ));

        if ( approvalAvailableUser(SUID) ) {
            $approval = array(
                'url'   => acmsLink(array('admin' => 'approval_notification', 'bid' => BID)),
                'stay'  => $this->linkCheck('approval_notification'),
            );
            if ( $badge = ACMS_GET_Approval_Notification::notificationCount() ) {
                $approval['badge'] = $badge;
            }
            $Tpl->add('approval#notification', $approval);
        }

        //--------------
        // contribution
        if ( sessionWithContribution() ) {
            $Tpl->add('entry#index', array(
                'url'   => acmsLink(array('admin' => 'entry_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('entry_index'),
            ));
            $Tpl->add('entry#trash', array(
                'url'   => acmsLink(array('admin' => 'entry_trash', 'bid' => BID)),
                'stay'  => $this->linkCheck('entry_trash'),
            ));
            if ( IS_LICENSED ) $Tpl->add('entry#insert');

            //--------------
            // compilation
            if ( sessionWithCompilation() ) {
                $Tpl->add('category#index', array(
                    'url'   => acmsLink(array('admin' => 'category_index', 'bid' => BID)),
                    'stay'  => $this->linkCheck('category'),
                ));
                $Tpl->add('tag', array(
                    'url'   => acmsLink(array('admin' => 'tag_index', 'bid' => BID)),
                    'stay'  => $this->linkCheck('tag'),
                ));
                $Tpl->add('comment', array(
                    'url'   => acmsLink(array('bid' => BID, 'admin' => 'comment_index')),
                    'stay'  => $this->linkCheck('comment'),
                ));
                $Tpl->add('trackback', array(
                    'url'   => acmsLink(array('bid' => BID, 'admin' => 'trackback_index')),
                    'stay'  => $this->linkCheck('trackback'),
                ));
                if ( IS_LICENSED ) {
                    $Tpl->add('category#insert', array(
                        'url'   => acmsLink(array('admin' => 'category_edit', 'bid' => BID)),
                        'stay'  => $this->linkCheck('category'),
                    ));
                }

                //----------------
                // administration
                if ( sessionWithAdministration() ) {
                    $Tpl->add('blog#index', array(
                        'url'   => acmsLink(array('admin' => 'blog_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('blog_index'),
                    ));
                    $Tpl->add('blog#edit', array(
                        'url'   => acmsLink(array('admin' => 'blog_edit', 'bid' => BID)),
                        'stay'  => $this->linkCheck('blog_edit'),
                    ));
                    $Tpl->add('alias#index', array(
                        'url'   => acmsLink(array('admin' => 'alias_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('alias'),
                    ));
                    $Tpl->add('user#index', array(
                        'url'   => acmsLink(array('admin' => 'user_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('user'),
                    ));
                    $Tpl->add('rule#index', array(
                        'url'   => acmsLink(array('admin' => 'rule_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('rule'),
                    ));
                    $Tpl->add('module#index', array(
                        'url'   => acmsLink(array('admin' => 'module_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('module'),
                    ));
                    $Tpl->add('shortcut#index', array(
                        'url'   => acmsLink(array('admin' => 'shortcut_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('shortcut'),
                    ));
                    $Tpl->add('form#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'form_index')),
                        'stay'  => $this->linkCheck('form'),
                    ));
                    $Tpl->add('schedule#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'schedule_index')),
                        'stay'  => $this->linkCheck('schedule'),
                    ));
                    $Tpl->add('moblog#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'moblog_index')),
                        'stay'  => $this->linkCheck('moblog'),
                    ));
                    $Tpl->add('publish#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'publish_index')),
                        'stay'  => $this->linkCheck('publish'),
                    ));
                    $Tpl->add('backup#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'backup_index')),
                        'stay'  => $this->linkCheck('backup'),
                    ));
                    $Tpl->add('import#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'import_index')),
                        'stay'  => $this->linkCheck('import'),
                    ));
                    $Tpl->add('app#index', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'app_index')),
                        'stay'  => $this->linkCheck('app_index'),
                    ));
                    $Tpl->add('checklist', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'checklist')),
                        'stay'  => $this->linkCheck('checklist'),
                    ));
                    $Tpl->add('cart#menu', array(
                        'url'   => acmsLink(array('bid' => BID, 'admin' => 'cart_menu')),
                        'stay'  => $this->linkCheck('cart_menu'),
                    ));
                    $Tpl->add('fix#index', array(
                        'url'   => acmsLink(array('admin' => 'fix_index', 'bid' => BID)),
                        'stay'  => $this->linkCheck('fix'),
                    ));

                    if ( defined('LICENSE_PLUGIN_SHOP_PRO') and LICENSE_PLUGIN_SHOP_PRO ) {
                        $Tpl->add('shop#menu', array(
                            'url'   => acmsLink(array('bid' => BID, 'admin' => 'shop_menu')),
                            'stay'  => $this->linkCheck('shop'),
                        ));
                    }

                    if ( config('media_library') === 'on' ) {
                        $Tpl->add('media#index', array(
                            'url'   => acmsLink(array('bid' => BID, 'admin' => 'media_index')),
                            'stay'  => $this->linkCheck('media'),
                        ));
                    }

                    if ( IS_LICENSED ) {
                        $Tpl->add('user#insert', array(
                            'url'   => acmsLink(array('admin' => 'user_edit', 'bid' => BID)),
                            'stay'  => $this->linkCheck('user#insert'),
                        ));
                        $Tpl->add('config#index', array(
                            'url'   => acmsLink(array('admin' => 'config_index', 'bid' => BID)),
                            'stay'  => $this->linkCheck('config'),
                        ));
                        $Tpl->add('rule#insert', array(
                            'url'   => acmsLink(array('admin' => 'rule_edit', 'bid' => BID)),
                            'stay'  => $this->linkCheck('rule'),
                        ));
                        $Tpl->add('module#insert', array(
                            'url'   => acmsLink(array('admin' => 'module_edit', 'bid' => BID)),
                            'stay'  => $this->linkCheck('module'),
                        ));
                    }
                    if ( IS_LICENSED ) {
                        if ( isBlogGlobal(SBID) ) {
                            $Tpl->add('blog#insert', array(
                                'url'   => acmsLink(array(
                                    'admin' => 'blog_edit',
                                    'alt'   => 'insert',
                                )),
                                'stay'  => $this->linkCheck('blog'),
                            ));
                        }
                    }
                }
            }
        }
    }

    function get()
    {
        if ( !sessionWithSubscription() ) return false;
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        //--------------
        // subscription
        if ( IS_LICENSED ) {
            $Tpl->add('user#profile', array(
                'url'   => acmsLink(array(
                    'bid'   => SBID,
                    'uid'   => SUID,
                    'admin' => 'user_edit',
                )),
                'icon'  => loadUserIcon(SUID),
            ));
        }

        //------------
        // enterprise
        if ( sessionWithEnterpriseAdministration() ) {
            if ( BID == 1 ) {
                $Tpl->add('role#index', array(
                    'url'   => acmsLink(array('admin' => 'role_index', 'bid' => 1)),
                    'stay'  => $this->linkCheck('role'),
                ));
                $Tpl->add('usergroup#index', array(
                    'url'   => acmsLink(array('admin' => 'usergroup_index', 'bid' => 1)),
                    'stay'  => $this->linkCheck('usergroup'),
                ));
                $Tpl->add('approval#index', array(
                    'url'   => acmsLink(array('admin' => 'approval_index', 'bid' => 1)),
                    'stay'  => $this->linkCheck('approval_index'),
                ));
            }

            $Tpl->add('workflow#index', array(
                'url'   => acmsLink(array('admin' => 'workflow_index', 'bid' => BID)),
                'stay'  => $this->linkCheck('workflow'),
            ));
        }

        //--------------
        // professional
        if ( 1
            && !sessionWithEnterpriseAdministration()
            && sessionWithProfessionalAdministration()
            && BID == 1
        ) {
            $Tpl->add('approval#index', array(
                'url'   => acmsLink(array('admin' => 'approval_index', 'bid' => 1)),
                'stay'  => $this->linkCheck('approval_index'),
            ));
        }

        if ( roleAvailableUser() ) {
            $this->roleAuth($Tpl);
        } else {
            $this->normalAuth($Tpl);
        }
        
        return $Tpl->get();
    }
}
