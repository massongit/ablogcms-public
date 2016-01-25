<?php
/**
 * ACMS_GET_Admin_App_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_App_Index extends ACMS_GET_Admin
{
    function get()
    {
        if ( 'app_index' <> ADMIN ) return '';
        if ( !sessionWithAdministration() ) return '';

        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $DB  = DB::singleton(dsn());

        $list = scandir(AAPP_LIB_DIR);
        $isNotFound = true;
        foreach ( $list as $fd ) {

            if (is_file(AAPP_LIB_DIR.$fd)) {
                $className = 'AAPP_'.str_replace('.php', '', $fd);
                if ( !class_exists($className) ) {
                    continue;
                }
                $App  = new $className();
                if ( !$App instanceof ACMS_App ) {
                    continue;
                }
                $vars = array(
                    'name'      => $App->name,
                    'desc'      => $App->desc,
                    'author'    => $App->author,
                    'className' => $className,
                );

                $SQL = SQL::newSelect('app');
                $SQL->addWhereOpr('app_name', $className);

                // DBになければインストール前として扱う
                $status = 'init';

                if ( !!($all = $DB->query($SQL->get(dsn()), 'all')) ) {

                    $existsOnThisBlog = false;
                    foreach ( $all as $row ) {
                        if ( intval($row['app_blog_id']) === BID ) {
                            $existsOnThisBlog = $row;
                        }
                    }

                    if ( $existsOnThisBlog ) {
                        $status = $existsOnThisBlog['app_status'];
                    } else {
                        $status = 'off';
                    }

                    if ( version_compare($row['app_version'], $App->version)   ) {
                        $status = 'update';
                    }
                }

                // ルートブログ以外では、インストールされていないアプリは表示しない
                if ( $status === 'init' && RBID !== BID ) {
                    continue;
                }

                $isNotFound = false;
                $Tpl->add('status:touch#'.$status);
                $Tpl->add('action:touch#'.$status);
                $Tpl->add('app:loop', $vars);
            }
        }

        if ( $isNotFound ) {
            $Tpl->add('notFound');
        }
        if ( !$this->Post->isNull() ) {
            $this->Post->set('notice_mess', 'show');
        }

        $Tpl->add(null, $this->buildField($this->Post, $Tpl));

        return $Tpl->get();
    }
}
