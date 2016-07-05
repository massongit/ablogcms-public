<?php
/**
 * ACMS_GET_Admin_Config_Index
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Config_Index extends ACMS_GET_Admin
{
    function get()
    {
        if ( 'config_index' <> ADMIN ) return false;
//        if ( !sessionWithAdministration() ) return false;
        $rid    = intval(ite($_GET, 'rid'));

        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        $aryAdmin   = array(
            'config_function', 'config_output', 'config_property',
            'config_mail', 'config_access',
            'config_theme',
            'config_edit', 'config_unit', 'config_authorize',
            'config_import', 'config_export', 'config_reset',
            'config_common', 'styleguide_acms', 'styleguide_acms-admin',
            'customfield_maker', 'i18n_index',
        );

        //--------
        // module
        $aryModule  = array(
            'Entry_Body',
            'Entry_List',
            'Entry_Photo',
            'Entry_Headline',
            'Entry_Summary',
            'Entry_ArchiveList',
            'Entry_TagRelational',
            'Entry_Continue',
            'Entry_Calendar',
            'Category_List',
            'Category_EntryList',
            'Category_EntrySummary',
            'Unit_List',
            'Tag_Cloud',
            'Tag_Filter',
            'Calendar_Month',
            'Calendar_Year',
            'Topicpath',
            'Comment_Body',
            'Comment_List',
            'Trackback_Body',
            'Trackback_List',
            'User_Profile',
            'User_Search',
            'Blog_ChildList',
            'Feed_Rss2',
            'Feed_ExList',
            'Sitemap',
            'Shop_Cart_List',
            'Links',
            'Banner',
            'Navigation',
            'Plugin_Schedule',
            'Alias_List',
            'Field_ValueList',
            'Api_GoogleAnalytics_Ranking',
            'Api_Twitter_Statuses_HomeTimeline',
            'Api_Twitter_Statuses_UserTimeline',
            'Api_Twitter_Search',
            'Api_Twitter_List_Statuses',
            'Api_Twitter_List_Members',
            'Api_Instagram_Users_Media_Recent',
            'Api_Instagram_Users_Media_Liked',
            'Api_Bing_WebSearch',
            'Api_Bing_ImageSearch',
        );

        foreach ( $aryModule as $module ) {
            $aryAdmin[]  = 'config_'.strtolower(preg_replace('@(?<=[a-zA-Z0-9])([A-Z])@', '-$1', $module));
        }

        foreach ( $aryAdmin as $admin ) {
            $AP     = array(
                'bid'   => BID,
                'admin' => $admin,
            );
            if ( !empty($rid) ) $AP['query'] = array('rid' => $rid);
            $Tpl->add($admin, array(
                'url'   => acmsLink($AP),
            ));
        }

        return $Tpl->get();
    }
}
