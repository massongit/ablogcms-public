<?php
/**
 * ACMS_GET_Admin_Media_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Media_Edit extends ACMS_GET_Admin_Edit
{
    function edit()
    {
        $Media  =& $this->Post->getChild('media');
        $mid    = $this->Get->get('_mid');

        if ( $Media->isNull() ) {
            if ( $mid ) {
                $Media->overload(loadMedia($mid));
            }
        }
        return true;
    }

    function get()
    {
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());
        $vars   = array();
        $edit   = 'update';
        $edit_  = $this->Get->get('edit');
        if ( !empty($edit_) ) {
            $edit   = $edit_;
        }

        if ( $this->Post->isValidAll() && $this->Post->isExists('edit') ) {
            $edit = $this->Post->get('edit');
            $this->Post->set('notice_mess', 'show');
            $Tpl->add('msg#'.$edit);
            $Tpl->add('msg:other');
        }

        $this->edit = $edit;
        if ( !$this->edit($Tpl) ) return false;

        $Media  =& $this->Post->getChild('media');

        if ( $thumbnail = $Media->get('thumbnail') ) {
            $Media->set('thumbnail@path', $thumbnail);
        }

        $vars   += $this->buildField($this->Post, $Tpl);
        $this->Post->reset(true);
        $this->Post->deleteField('edit');
        $vars   += $this->buildEdit($this->edit, $Tpl);

        $ext            = ite(pathinfo($Media->get('path')), 'extension');
        $vars['icon']   = pathIcon($ext);

        $size = $Media->get('file_size');
        foreach ( configArray('column_image_size') as $i => $_size ) {
            $sizeAry  = array(
                'value'     => $_size,
                'label'     => config('column_image_size_label', '', $i),
            );
            if ( $size == $_size ) {
                $sizeAry['selected']  = config('attr_selected');
            }
            $Tpl->add(array('size:loop'), $sizeAry);
        }

        $mid    = $this->Get->get('_mid');
        $DB     = DB::singleton(dsn());
        $SQL    = SQL::newSelect('media_tag');
        $SQL->addSelect('media_tag_name');
        $SQL->addWhereOpr('media_tag_media_id', $mid);
        $SQL->addOrder('media_tag_sort');
        $tags   = array();
        if ( $all = $DB->query($SQL->get(dsn()), 'all') ) {
            foreach ( $all as $tag ) {
                $tags[] = $tag['media_tag_name'];
            }
        }
        if ( !empty($tags) ) $vars['media_label'] = implode(', ', $tags);

        $Tpl->add(null, $vars);

        return $Tpl->get();
    }
}
