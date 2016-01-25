<?php
/**
 * ACMS_GET_Admin_Entry_Edit
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Admin_Entry_Edit extends ACMS_GET_Admin_Entry
{
    /**
     * @var array
     */
    var $fieldNames  = array ();

    /**
     * @see ACMS_User_GET_EntryExtendSample_Edit
     *
     * @param string $fieldName
     * @param int    $eid
     * @return Field
     */
    function loadCustomField($fieldName, $eid)
    {
        $Field = new Field_Validation();
        return $Field;
    }

    function get ()
    {
        if ( !sessionWithContribution(BID, false) ) return false;
        if ( 'entry-edit' <> ADMIN && 'entry_editor' <> ADMIN && 'entry-field' <> ADMIN ) return false;

        $CustomFieldCollection = array();

        if ( 1
            && !$this->Post->isNull() 
            && ( !$this->Post->get('backend') || !$this->Post->isValidAll() )
        ) {
            $step   = $this->Post->get('step');
            $action = $this->Post->get('action');
            $Column = acmsUnserialize($this->Post->get('column'));
            $Entry  =& $this->Post->getChild('entry');
            $Field  =& $this->Post->getChild('field');

            $Column = alignColumn($Column);
        } else {
            $Entry  = new Field_Validation();
            $Field  = new Field_Validation();

            $DB = DB::singleton(dsn());

            $Column = array();
            if ( EID ) {
                $step   = 'reapply';
                $action = 'update';

                if ( RVID ) {
                    $SQL    = SQL::newSelect('entry_rev');
                    $SQL->addWhereOpr('entry_id', EID);
                    $SQL->addWhereOpr('entry_blog_id', BID);
                    $SQL->addWhereOpr('entry_rev_id', RVID);
                    $row    = $DB->query($SQL->get(dsn()), 'row');
                } else {
                    $row    = ACMS_RAM::entry(EID);
                }
                $RVID_      = RVID;
                if ( !RVID && $row['entry_approval'] === 'pre_approval' ) {
                    $RVID_  = 1;
                }

                //--------------
                // custom field
                $Field  = loadEntryField(EID, $RVID_, true);
                foreach ( $this->fieldNames as $fieldName ) {
                    $CustomFieldCollection[$fieldName]   = $this->loadCustomField($fieldName, EID);
                }

                $Entry->setField('status', $row['entry_status']);
                $Entry->setField('title', $row['entry_title']);
                $Entry->setField('code', $row['entry_code']);
                $Entry->setField('link', $row['entry_link']);
                $Entry->setField('indexing', $row['entry_indexing']);
                $Entry->setField('summary_range', $row['entry_summary_range']);
                $Entry->setField('category_id', $row['entry_category_id']);
                $Entry->setField('primary_image', $row['entry_primary_image']);

                list($date, $time)  = explode(' ', $row['entry_datetime']);
                $Entry->setField('date', $date);
                $Entry->setField('time', $time);

                list($date, $time)  = explode(' ', $row['entry_start_datetime']);
                $Entry->setField('start_date', $date);
                $Entry->setField('start_time', $time);

                list($date, $time)  = explode(' ', $row['entry_end_datetime']);
                $Entry->setField('end_date', $date);
                $Entry->setField('end_time', $time);

                //-----
                // tag
                $tag    = '';
                if ( RVID ) {
                    $SQL    = SQL::newSelect('tag_rev');
                    $SQL->addWhereOpr('tag_rev_id', RVID);
                } else {
                    $SQL    = SQL::newSelect('tag');
                }
                $SQL->setSelect('tag_name');
                $SQL->addWhereOpr('tag_entry_id', EID);
                $q  = $SQL->get(dsn());
                if ( $DB->query($q, 'fetch') and ($row = $DB->fetch($q)) ) { 
                    do {
                        $tag    .= !empty($tag) ? ', ' : '';
                        $tag    .= $row['tag_name'];
                    } while ( $row = $DB->fetch($q) ); 
                    $Entry->setField('tag', $tag);
                }

                //--------
                // column
                if ( $Column = loadColumn(EID, null, $RVID_) ) {
                    $cnt    = count($Column);
                    for ( $i=0; $i<$cnt; $i++ ) {
                        $Column[$i]['id']   = uniqueString();
                        $Column[$i]['sort'] = $i + 1;
                    }
                }

                //---------------
                // related entry
                if ( $relatedEids = loadRelatedEntries(EID, $RVID_) ) {
                    foreach ( $relatedEids as $reid ) {
                        $Entry->addField('related', $reid);
                    }
                }

            } else {
                $step   = 'apply';
                $action = 'insert';
                $aryType    = configArray('column_def_insert_type');
                $Column     = array();
                foreach ( $aryType as $i => $type ) {
                    if ( !$data = $this->getColumnDefinition('insert', $type, $i) ) continue;
                    $Column[]   = $data + array(
                        'id'    => uniqueString(),
                        'type'  => $type,
                        'sort'  => $i + 1,
                        'align' => config('column_def_insert_align', 'auto', $i),
                        'group' => config('column_def_insert_group', '', $i),
                        'class' => config('column_def_insert_class', '', $i),
                        'attr'  => config('column_def_insert_class', '', $i),
                        'size'  => config('column_def_insert_size', '', $i),
                        'edit'  => config('column_def_insert_edit', '', $i),
                    );
                }
            }
        }

        $vars       = array();
        $rootBlock  = 'step#'.$step;
        $pattern    = '/<!--[\t 　]*BEGIN[\t 　]+'.$rootBlock.'[^>]*?-->(.*)<!--[\t 　]*END[\t 　]+'.$rootBlock.'[^>]*?-->/s';
        if ( preg_match($pattern, $this->tpl, $matches) ) {
            $this->tpl = $matches[0];
        }
        $Tpl    = new Template($this->tpl, new ACMS_Corrector());

        //--------
        // column
        $aryTypeLabel    = array();
        foreach ( configArray('column_add_type') as $i => $type ) {
            $aryTypeLabel[$type]    = config('column_add_type_label', '', $i);
        }

        if ( $cnt = count($Column) ) { foreach ( $Column as $data ) {

            $id     = $data['id'];
            $clid   = intval(ite($data, 'clid'));
            $type   = $data['type'];
            $align  = $data['align'];
            $group  = $data['group'];
            $attr   = $data['attr'];
            $sort   = $data['sort'];

            // 特定指定子を含むユニットタイプ
            $actualType = $type;
            // 特定指定子を除外した、一般名のユニット種別
            $type = detectUnitTypeSpecifier($type);

            $data['primaryImage']   = $Entry->get('primary_image');

            //--------------
            // build column
            if ( !$this->buildColumn($data, $Tpl, $rootBlock) ) continue;

            //------
            // sort
            for ( $i=1; $i<=$cnt; $i++ ) {
                $_vars  = array(
                    'value' => $i,
                    'label' => $i,
                );
                if ( $sort == $i ) $_vars['selected']   = config('attr_selected');
                $Tpl->add(array('sort:loop', $rootBlock), $_vars);
            }

            //-------
            // align
            $Tpl->add(array('align#'.(in_array($type, array('text', 'custom', 'module')) ? 'liquid' : 'solid'), $rootBlock), array(
                'align:selected#'.$align    => config('attr_selected')
            ));

            //-------
            // group
            if ( 'on' === config('unit_group') ) {
                $labels  = configArray('unit_group_label');
                foreach ( $labels as $i => $label ) {
                    $class = config('unit_group_class', '', $i);
                    $Tpl->add(array('group:loop', $rootBlock), array(
                         'value' => $class,
                         'label' => $label,
                         'selected' => ($class === $group) ? config('attr_selected') : '',
                    ));
                }
            }

            //------
            // attr
            if ( $aryAttr = configArray('column_'.$type.'_attr') ) {
                foreach ( $aryAttr as $i => $_attr ) {
                    $label  = config('column_'.$type.'_attr_label', '', $i);
                    $_vars  = array(
                        'value' => $_attr,
                        'label' => $label,
                    );
                    if ( $attr == $_attr ) $_vars['selected'] = config('attr_selected');
                    $Tpl->add(array('clattr:loop', $rootBlock), $_vars);
                }
            } else {
                $Tpl->add(array('clattr#none', $rootBlock));
            }

            $Tpl->add(array('column:loop', $rootBlock), array(
                'uniqid'    => $id,
                'clid'      => $clid,
                'cltype'    => $actualType,
                'clattr'    => $attr,
                'clname'    => ite($aryTypeLabel, $actualType),
            ));
        } } else {

            //-----------
            // [CMS-608]
            $Tpl->add(array('adminEntryColumn', $rootBlock));
        }

        //---------------
        // related entry
        if ( $relatedEids = $Entry->getArray('related') ) {
            $Entry->delete('related');
            $this->buildRelatedEntries($Tpl, $relatedEids, $rootBlock);
        }

        //---------------
        // summary range
        $summaryRange   = $Entry->get('summary_range');
        $columnAmount   = count($Column);
        if ( $columnAmount < $summaryRange ) $summaryRange = $columnAmount;
        for ( $i=1; $i<=$columnAmount; $i++ ) {
            $_vars  = array('value' => $i);
            if ( $summaryRange == $i ) $_vars['selected']   = config('attr_selected');
            $Tpl->add(array('range:loop', $rootBlock), $_vars);
        }
        if ( '0' === $summaryRange ) {
            $vars['range:selected#none']    = config('attr_selected');
        } else if ( empty($summaryRange) ) {
            $vars['range:selected#all']     = config('attr_selected');
        }
        $vars['summaryRange']   = $summaryRange;

        //----------
        // next eid
        if ( $action == 'insert' ) {
            $DB = DB::singleton(dsn());
            $vars['next_eid'] = intval($DB->query(SQL::currval('entry_id', dsn()), 'seq')) + 1;
        }

        //--------------
        // entry , field
        $vars   += $this->buildField($Entry, $Tpl, $rootBlock, 'entry');
        $vars   += $this->buildField($Field, $Tpl, $rootBlock, 'field');
        $vars['column:takeover']  = base64_encode(gzdeflate(serialize($Column)));

        //--------------
        // custom field
        foreach ( $CustomFieldCollection as $fieldName => $customField ) {
            $vars   += $this->buildField($customField, $Tpl, $rootBlock, $fieldName);
        }

        //--------
        // action
        if ( IS_LICENSED ) {
            if ( 0
                || ( !roleAvailableUser() && sessionWithCompilation() )
                || ( roleAvailableUser() && roleAuthorization('category_create', BID) )
            ) {
                $Tpl->add(array('action#categoryInsert', $rootBlock));
            }

            $Tpl->add(array('action#confirm', $rootBlock));
            $Tpl->add(array('action#'.$action, $rootBlock));

            if ( 'entry-edit' == ADMIN ) {
                $Tpl->add(array('view#frontend', $rootBlock));
            } else if ( 'entry_editor' == ADMIN ) {
                $Tpl->add(array('view#backend', $rootBlock));
                $Tpl->add(array('backend', $rootBlock));
            } else if ( 'entry-field' == ADMIN && $this->Post->get('backend') ) {
                $Tpl->add(array('message', $rootBlock));
            }
        }
        if ( 'update' == $action ) $Tpl->add(array('action#delete', $rootBlock));

        $Tpl->add($rootBlock, $vars);
        return $Tpl->get();
    }
}
