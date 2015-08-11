<?php
/**
 * Template
 *
 * This file is part of the a-blog cms package.
 * Please see LICENSE. Complete license information is there.
 *
 * (c) appleple inc. <info@appleple.com>
 */
class Template
{
    var $_tokens        = array();

    var $_blockIdLabel  = array();
    var $_blockLabelId  = array();
    var $_blockIdTokenBegin = array();
    var $_blockIdTokenEnd   = array();
    var $_blockTokenIdBegin = array();
    var $_blockTokenIdEnd   = array();
    var $_blockIdTxt    = array(0=>null);

    var $_blockEmptyToken   = array();
    var $_blockIdEmptyId    = array();

    var $_varIdLabel    = array();
    var $_varLabelId    = array();
    var $_varIdOption   = array();
    var $_varIdToken    = array();
    var $_varTokenId    = array();


    var $_Corrector     = null;

    /**
     * @param string $txt
     * @param null $Corrector
     * @return bool
     */
    function Template($txt, $Corrector=null)
    {
        if ( is_object($Corrector) and method_exists($Corrector, 'correct') ) {
            $this->_Corrector =& $Corrector;
        }

        $txt    = preg_replace(array(
            '@<!--[\t 　]*[BEGIN]{3,6}+[\t 　]+([^\t 　]+)[\t 　]*-->@',
            '@<!--[\t 　]*[END]{2,4}+[\t 　]+([^\t 　]+)[\t 　]*-->@',
            '@(?<!\\\)\{([^}\n]+)(?<!\\\)\}\[([^\]\n]+)\]@',
            '@(?<!\\\)\{([^}\n]+)(?<!\\\)\}@',
        ), array(
            '<!-- BEGIN $1 -->',
            '<!-- END $1 -->',
            '<!--%$1%-->$2 -->',
            '<!--%$1 -->',
        ), $txt);
        $txt    = str_replace(array('\{','\}'), array('{','}'), $txt);
        $tokens = preg_split('@(<!-- BEGIN |<!-- END | -->|<!--%|%-->)@'
            , $txt, -1, PREG_SPLIT_DELIM_CAPTURE
        );

        //----------
        // validate
        $labels = array();
        $cnt    = count($tokens);
        for ( $i=0; $i<$cnt; $i++ ) {
            $token  = $tokens[$i];
            if ( '<!-- BEGIN ' == $token ) {
                $label  = $tokens[$i+1];
                if ( isset($labels[$label]) ) {
                    unset($tokens[$i]);
                    unset($tokens[$i+1]);
                    unset($tokens[$i+2]);
                } else {
                    $labels[$label] = $i;
                }
                $i  += 2;
            } else if ( '<!-- END ' == $token ) {
                $label  = $tokens[$i+1];
                if ( !isset($labels[$label]) ) {
                    unset($tokens[$i]);
                    unset($tokens[$i+1]);
                    unset($tokens[$i+2]);
                } else {
                    $from   = $labels[$label];
                    $to     = $i;
                    unset($labels[$label]);
                    foreach ( $labels as $_label => $pos ) {
                        if ( $from < $pos and $pos < $to ) {
                            unset($tokens[$pos]);
                            unset($tokens[$pos+1]);
                            unset($tokens[$pos+2]);
                            unset($labels[$_label]);
                        }
                    }
                    unset($labels[$label]);
                }
                $i  += 2;
            }
        }

        $i          = 1;
        $blockId    = 1;
        $varId      = 0;
        $this->_tokens[0]           = '';
        $this->_blockIdTokenBegin[0]= 0;
        while ( null !== ($token = array_shift($tokens)) ) {
            if ( '<!-- BEGIN ' == $token ) {
                $label  = array_shift($tokens);
                array_shift($tokens);
                if ( substr($label, -6) === ':empty' ) {
                    $this->_blockEmptyToken[$i]   = $label;
                }
                $this->_blockIdTxt[$blockId]        = null;
                $this->_blockIdLabel[$blockId]      = $label;
                $this->_blockLabelId[$label][]      = $blockId;
                $this->_blockIdTokenBegin[$blockId] = $i;
                $blockId++;
                continue;
            } else if ( '<!-- END ' == $token )  {
                $label  = array_shift($tokens);
                array_shift($tokens);

                $ids    = $this->_blockLabelId[$label];
                $this->_blockIdTokenEnd[end($ids)]  = ($i-1);
                continue;
            } else if ( '<!--%' == $token ) {
                $label  = array_shift($tokens);

                $this->_varIdToken[$varId]      = $i;
                $this->_varIdLabel[$varId]      = $label;
                $this->_varLabelId[$label][]    = $varId;

                if ( '%-->' == array_shift($tokens) ) {
                    $this->_varIdOption[$varId] = array_shift($tokens);
                    array_shift($tokens);
                }
                $token  = null;

                $varId++;
            }
            $this->_tokens[$i++]    = $token;
        }
        $this->_tokens[$i]  = '';
        $this->_blockIdTokenEnd[0]  = $i;

        $this->_blockTokenIdBegin   = array_flip($this->_blockIdTokenBegin);
        $this->_blockTokenIdEnd     = array_flip($this->_blockIdTokenEnd);
        $this->_varTokenId          = array_flip($this->_varIdToken);

        foreach ( $this->_blockIdTokenEnd as $j => $_end ) {
            $_begin = $this->_blockIdTokenBegin[$j];
            foreach ( $this->_blockEmptyToken as $k => $v ) {
                if ( $_begin < $k && $k < $_end ) {
                    $this->_blockIdEmptyId[$k] = array($_begin, $_end);
                    unset($this->_blockEmptyToken[$k]);
                }
            }
        }
        return true;
    }

    /**
     * ブロックに変数を追加する
     *
     * @param array|null $blocks
     * @param array $vars
     * @return bool
     */
    function add($blocks=array(), $vars=array())
    {
        if ( null != $this->_blockIdTxt[0] ) {
            trigger_error('root is already touched.', E_USER_NOTICE);
            return false;
        }

        if ( !is_array($blocks) ) {
            $blocks = is_string($blocks) ? array($blocks) : array();
        }
        $blocks = array_reverse($blocks);
        if ( !is_array($vars) ) $vars = array();

        $pointAry   = array();
        $endBlock   = end($blocks);
        $parentAry  = array();

        foreach ( $blocks as $block ) {
            if ( !isset($this->_blockLabelId[$block]) ) return false;
            $tempParent = array();
            $ids        = $this->_blockLabelId[$block];

            foreach ( $ids as $id ) {
                $layered = false;
                foreach ( $parentAry as $ppt ) {
                    if ( 1
                        && $this->_blockIdTokenEnd[$ppt] >= $this->_blockIdTokenEnd[$id]
                        && $this->_blockIdTokenBegin[$ppt] < $this->_blockIdTokenBegin[$id]
                    ) {
                        $layered        = true;
                        $tempParent[]   = $id;
                        continue;
                    }
                }
                if ( (count($blocks) === 1 || $layered) && $block === $endBlock ) {
                    $pointAry[] = $id;
                }
            }
            $parentAry = empty($parentAry) ? $ids : $tempParent;
        }
        if ( empty($blocks) && empty($pointAry) ) $pointAry = array(0);

        foreach ( $pointAry as $pt ) {
            $this->arrange($pt, $blocks, $vars);
        }
    }

    function arrange($pt, $blocks, $vars)
    {
        $begin  = $this->_blockIdTokenBegin[$pt];
        $end    = $this->_blockIdTokenEnd[$pt];

        //----------
        // variable
        foreach ( $vars as $key => $value ) {
            if ( empty($this->_varLabelId[$key]) ) continue;
            $ids    = $this->_varLabelId[$key];
            foreach ( $ids as $id ) {
                $token  = $this->_varIdToken[$id];
                if ( $begin < $token and $token < $end ) {
                    $val = $value;
                    if ( isset($this->_Corrector) ) {
                        $val  = $this->_Corrector->correct($value
                            , isset($this->_varIdOption[$id]) ? $this->_varIdOption[$id] : ''
                        , $key);
                    }
                    $this->_tokens[$token]  = strval($val);
                }
            }
        }

        //-------
        // touch
        $active     = array($pt => true);
        $ids        = array();
        $buf        = array();
        for ( $i=$begin; $i<=$end; $i++ ) {

            if ( isset($this->_blockTokenIdBegin[$i]) ) {
                array_unshift($ids, $this->_blockTokenIdBegin[$i]);
            }
            $id = $ids[0];

            if ( !empty($active[$id]) ) {
                $this->_blockIdTxt[$pt] .= $this->_tokens[$i];
            } else {
                if ( null !== $this->_blockIdTxt[$id] ) {
                    $txt    = '';
                    foreach ( $buf as $tokenId => $token ) {
                        if ( isset($this->_blockTokenIdBegin[$tokenId]) ) {
                            $active[$this->_blockTokenIdBegin[$tokenId]]    = true;
                        }
                        $txt    .= $token;
                    }

                    $this->_blockIdTxt[$pt] .= $txt;
                    $buf    = array();

                    $this->_blockIdTxt[$pt] .= $this->_blockIdTxt[$id];
                    $this->_blockIdTxt[$id] = null;
                    $i      = $this->_blockIdTokenEnd[$id];

                    array_shift($ids);
                    continue;
                } else if ( isset($this->_blockTokenIdEnd[$i]) ) {
                    $blockL = $this->_blockIdLabel[$id];
                    if ( 1
                        && substr($blockL, -6) === ':empty'
                        && !isset($vars[substr($blockL, 0, -6)])
                        && isset($this->_blockIdEmptyId[$i])
                        && $this->_blockIdEmptyId[$i][0] === $begin
                        && $this->_blockIdEmptyId[$i][1] === $end
                    ) {
                        $this->_blockIdTxt[$pt] .= $this->_tokens[$i];
                    } else {
                        for ( $j=$this->_blockIdTokenBegin[$id]; $j<$i; $j++ ) unset($buf[$j]);
                    }
                    array_shift($ids);
                    continue;
                }
                $buf[$i]    = $this->_tokens[$i];
            }
            if ( isset($this->_varTokenId[$i]) ) {
                if ( null !== $this->_tokens[$i] ) {
                    $txt    = '';
                    foreach ( $buf as $tokenId => $token ) {
                        if ( isset($this->_blockTokenIdBegin[$tokenId]) ) {
                            $active[$this->_blockTokenIdBegin[$tokenId]]    = true;
                        }
                        $txt    .= $token;
                    }
                    $this->_blockIdTxt[$pt] .= $txt;
                    $buf    = array();
                }
            }
            if ( isset($this->_blockTokenIdEnd[$i]) ) array_shift($ids);
        }
        // init tokens
        for ( $i=$begin; $i<=$end; $i++ ) {
            if ( isset($this->_varTokenId[$i]) ) {
                $this->_tokens[$i]  = null;
            }
        }
    }

    /**
     * テンプレートを文字列で取得する
     *
     * @return string
     */
    function get()
    {
        if ( is_null($this->_blockIdTxt[0]) ) $this->add();
        return str_replace(array('<!-- BEGIN\\','<!-- END\\'), array('<!-- BEGIN','<!-- END'), $this->_blockIdTxt[0]);
    }
}
