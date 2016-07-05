<?php
/**
 * ACMS_GET_Form
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class ACMS_GET_Form extends ACMS_GET
{
    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());

        $this->step   = $this->Post->get('error');
        if ( empty($this->step) ) {
            $this->step   = $this->Get->get('step');
        }
        if ( $this->Post->isValidAll() ) {
            $this->step   = $this->Post->get('step', $this->step);
        } else {
            $this->error($Tpl);
        }

        $this->build_tpl($Tpl);

        // CSRF Token の埋め込み
        $tpl = $Tpl->get();
        if ( $this->step == 'confirm' && config('form_csrf_enable', 'on') !== 'off' ) {
            $this->csrf_token($tpl);
        }

        return $tpl;
    }

    /**
     * CSRF Token埋め込み
     * 
     * @param string & $tpl
     */
    function csrf_token(& $tpl)
    {
        $Session = ACMS_Session::singleton();

        if ( $Session->get('formToken') ) {
            $token  = $Session->get('formToken');
        } else {
            $token  = sha1(uniqueString().'acms'.session_id());
            $Session->set('formToken', $token);
            $Session->save();
        }

        // token の埋め込み
        $tpl = preg_replace('@(?=<\s*/\s*form[^\w]*>)@i', '<input type="hidden" name="formToken" value="'.$token.'" />'."\n", $tpl);
    }

    /**
     * テンプレートの組み立て
     * 
     * @param Template & $Tpl
     */
    function build_tpl(& $Tpl)
    {
        $Block  = !(empty($this->step) or is_bool($this->step)) ? 'step#'.$this->step : 'step';
        $this->Post->delete('step');
        if ( EID ) {
            $entry  = ACMS_RAM::entry(EID);
            $fmid   = $entry['entry_form_id'];

            $DB     = DB::singleton(dsn());
            $SQL    = SQL::newSelect('form');
            $SQL->addSelect('form_code');
            $SQL->addWhereOpr('form_id', $fmid);
            $Where  = SQL::newWhere();
            $Where->addWhereOpr('form_blog_id', BID, '=', 'OR');
            $Where->addWhereOpr('form_scope', 'global', '=', 'OR');
            $SQL->addWhere($Where);
            $fcode  = $DB->query($SQL->get(dsn()), 'one');

            $this->Post->add('form_id', $fcode);
        }
        $Tpl->add($Block, $this->buildField($this->Post, $Tpl, $Block, ''));
    }

    /**
     * エラー処理
     * 
     * @param Template & $Tpl
     */
    function error(& $Tpl)
    {
        $Errors = array();
        if ( isset($this->Post->_aryChild['field']) ) {
            $Field  = $this->Post->_aryChild['field'];
            foreach ( $Field->_aryV as $key => $val ) {
                foreach ( $val as $valid ) {
                    if ( 1
                        and isset($valid[0])
                        and $valid[0] === false
                    ) {
                        $Errors[]   = $key;
                    } 
                }
            }
        }
        if ( !empty($Errors) ) {
            $Tpl->add('error', array(
                'formID'    => $this->Post->get('id'),
                'errorKey'  => implode(',', $Errors),
            ));
        }
    }
}
