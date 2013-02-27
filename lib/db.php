<?php

class APDL_DBO {

    protected $__dto, $__query = array(), $count = FALSE, $queried = FALSE;

    public function __construct() {
        switch (func_num_args()) {
            case 0:
                $this->__query['noquery'] = TRUE;
                break;
            case 1:
                $arg = func_get_arg(0);
                if (is_object($arg) && get_class($arg) == 'APDL_DBO') {
                    $this->__query['noquery'] = TRUE;
                    $this->__dto = $arg;
                }
                if (is_array($arg)) {
                    $this->__query = $arg;
                } else {
                    $this->__query['val'] = $arg;
                }
                break;
            case 2:
                $this->__query['val'] = func_get_arg(0);
                $this->__query['field'] = func_get_arg(1);
                break;
            case 3:
                $this->__query['val'] = func_get_arg(0);
                $this->__query['field'] = func_get_arg(1);
                $this->__query['rel'] = func_get_arg(2);
                break;
            case 4:
                $this->__dto = func_get_arg(3);
                break;
        }
    }

    public function &__GET($var) {
        return $this->__dto->$var;
    }

    public function &__SET($var, $val) {
        $this->__dto->$var = $val;
    }

    static function querybuilder($query, $mode = 'select') {
        switch ($mode) {
            case 'select':
                $q = 'SELECT ';
                if (isset($query['gfields'])) {
                    $q.=implode(',', self::enc_fields($query['gfields']));
                } else {
                    $q .= '*';
                }
                $q.=' FROM ' . self::enc_fields($query['table']);
                if (isset($query['filters'])) {
                    $q.=self::filterbuilder($query['filters']);
                }
                if (isset($query['limit'])) {
                    $q.=' LIMIT ' . $query['limit'];
                }
                if (isset($query['order'])) {
                    $q.=' ORDER BY ' . $query['order'];
                }
                return $q;
                break;
            case 'insert':
                $q='INSERT INTO '.self::enc_fields($query['table']).' () VALUES()';                
                break;
        }
    }

    static protected function enc_fields($fields) {
        if (is_array($fields)) {
            foreach ($fields as $k => $f) {
                $fields[$k] = "`" . $f . "`";
            }
        } else {
            $fields = "`" . $fields . "`";
        }
    }

}

?>