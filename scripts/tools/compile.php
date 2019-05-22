<?php
/**
 * Created by PhpStorm.
 * User: lucy
 * Date: 2016/11/4
 * Time: 9:34
 * file_put_contents('D:/phpStudy/www/commile_js.php',CompileFile::html('D:/phpStudy/www/birthDate.js'));
 */
class CompileFile{

    const ORD_LF            = 10;
    const ORD_SPACE         = 32;
    const ACTION_KEEP_A     = 1;
    const ACTION_DELETE_A   = 2;
    const ACTION_DELETE_A_B = 3;

    protected $a           = '';
    protected $b           = '';
    protected $input       = '';
    protected $inputIndex  = 0;
    protected $inputLength = 0;
    protected $lookAhead   = null;
    protected $output      = '';

    protected function getjs() {
        $c = $this->lookAhead;
        $this->lookAhead = null;

        if ($c === null) {
            if ($this->inputIndex < $this->inputLength) {
                $c = substr($this->input, $this->inputIndex, 1);
                $this->inputIndex += 1;
            } else {
                $c = null;
            }
        }

        if ($c === "\r") {
            return "\n";
        }

        if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
            return $c;
        }

        return ' ';
    }

    protected function isAlphaNum($c) {
        return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
    }

    protected function action($command) {
        switch($command) {
            case self::ACTION_KEEP_A:
                $this->output .= $this->a;

            case self::ACTION_DELETE_A:
                $this->a = $this->b;

                if ($this->a === "'" || $this->a === '"') {
                    for (;;) {
                        $this->output .= $this->a;
                        $this->a       = $this->getjs();

                        if ($this->a === $this->b) {
                            break;
                        }

                        if (ord($this->a) <= self::ORD_LF) {
                            break;
                        }

                        if ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a       = $this->getjs();
                        }
                    }
                }

            case self::ACTION_DELETE_A_B:
                $this->b = $this->next();

                if ($this->b === '/' && (
                        $this->a === '(' || $this->a === ',' || $this->a === '=' ||
                        $this->a === ':' || $this->a === '[' || $this->a === '!' ||
                        $this->a === '&' || $this->a === '|' || $this->a === '?' ||
                        $this->a === '{' || $this->a === '}' || $this->a === ';' ||
                        $this->a === "\n" )) {

                    $this->output .= $this->a . $this->b;

                    for (;;) {
                        $this->a = $this->getjs();

                        if ($this->a === '[') {
                            for (;;) {
                                $this->output .= $this->a;
                                $this->a = $this->getjs();

                                if ($this->a === ']') {
                                    break;
                                } elseif ($this->a === '\\') {
                                    $this->output .= $this->a;
                                    $this->a       = $this->getjs();
                                } elseif (ord($this->a) <= self::ORD_LF) {
                                    break 2;
                                }
                            }
                        } elseif ($this->a === '/') {
                            break;
                        } elseif ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a       = $this->getjs();
                        } elseif (ord($this->a) <= self::ORD_LF) {
                            break 2;
                        }

                        $this->output .= $this->a;
                    }

                    $this->b = $this->next();
                }
        }
    }

    protected function min() {
        if (0 == strncmp($this->peek(), "\xef", 1)) {
            $this->getjs();
            $this->getjs();
            $this->getjs();
        }

        $this->a = "\n";
        $this->action(self::ACTION_DELETE_A_B);

        while ($this->a !== null) {
            switch ($this->a) {
                case ' ':
                    if ($this->isAlphaNum($this->b)) {
                        $this->action(self::ACTION_KEEP_A);
                    } else {
                        $this->action(self::ACTION_DELETE_A);
                    }
                    break;

                case "\n":
                    switch ($this->b) {
                        case '{':
                        case '[':
                        case '(':
                        case '+':
                        case '-':
                        case '!':
                        case '~':
                            $this->action(self::ACTION_KEEP_A);
                            break;

                        case ' ':
                            $this->action(self::ACTION_DELETE_A_B);
                            break;

                        default:
                            if ($this->isAlphaNum($this->b)) {
                                $this->action(self::ACTION_KEEP_A);
                            }
                            else {
                                $this->action(self::ACTION_DELETE_A);
                            }
                    }
                    break;

                default:
                    switch ($this->b) {
                        case ' ':
                            if ($this->isAlphaNum($this->a)) {
                                $this->action(self::ACTION_KEEP_A);
                                break;
                            }

                            $this->action(self::ACTION_DELETE_A_B);
                            break;

                        case "\n":
                            switch ($this->a) {
                                case '}':
                                case ']':
                                case ')':
                                case '+':
                                case '-':
                                case '"':
                                case "'":
                                    $this->action(self::ACTION_KEEP_A);
                                    break;

                                default:
                                    if ($this->isAlphaNum($this->a)) {
                                        $this->action(self::ACTION_KEEP_A);
                                    }
                                    else {
                                        $this->action(self::ACTION_DELETE_A_B);
                                    }
                            }
                            break;

                        default:
                            $this->action(self::ACTION_KEEP_A);
                            break;
                    }
            }
        }

        return $this->output;
    }

    protected function next() {
        $c = $this->getjs();

        if ($c === '/') {
            switch($this->peek()) {
                case '/':
                    for (;;) {
                        $c = $this->getjs();

                        if (ord($c) <= self::ORD_LF) {
                            return $c;
                        }
                    }

                case '*':
                    $this->getjs();

                    for (;;) {
                        switch($this->getjs()) {
                            case '*':
                                if ($this->peek() === '/') {
                                    $this->getjs();
                                    return ' ';
                                }
                                break;

                            case null:
                                break;
                        }
                    }

                default:
                    return $c;
            }
        }

        return $c;
    }

    protected function peek() {
        $this->lookAhead = $this->getjs();
        return $this->lookAhead;
    }

    public static function get($file){
        if(is_file($file)){
            return file_get_contents($file);
        }else{
            return '';
        }
    }
    /**
     * 压缩PHP文件
     * @param $file
     * @return string
     */
    public static function php($file){
        $content = CompileFile::get($file);
        $result = token_get_all ($content );
        $string = '';
        $space = false;
        $array = array (
            T_CONCAT_EQUAL, // .=
            T_DOUBLE_ARROW, // =>
            T_BOOLEAN_AND, // &&
            T_BOOLEAN_OR, // ||
            T_IS_EQUAL, // ==
            T_IS_NOT_EQUAL, // != or <>
            T_IS_SMALLER_OR_EQUAL, // <=
            T_IS_GREATER_OR_EQUAL, // >=
            T_INC, // ++
            T_DEC, // --
            T_PLUS_EQUAL, // +=
            T_MINUS_EQUAL, // -=
            T_MUL_EQUAL, // *=
            T_DIV_EQUAL, // /=
            T_IS_IDENTICAL, // ===
            T_IS_NOT_IDENTICAL, // !==
            T_DOUBLE_COLON, // ::
            T_PAAMAYIM_NEKUDOTAYIM, // ::
            T_OBJECT_OPERATOR, // ->
            T_DOLLAR_OPEN_CURLY_BRACES, // ${
            T_AND_EQUAL, // &=
            T_MOD_EQUAL, // %=
            T_XOR_EQUAL, // ^=
            T_OR_EQUAL, // |=
            T_SL, // <<
            T_SR, // >>
            T_SL_EQUAL, // <<=
            T_SR_EQUAL  // >>=
        );
        while ( current ( $result ) ) {
            $value = current ( $result );
            if (is_string ( $value )) {
                // 去掉字符左侧的 空白
                if ($space) {
                    $string = rtrim ( $string ) . $value;
                } else {
                    $string .= $value;
                }
                $space = true;
            } else {
                switch ($value [0]) {
                    // 去掉php开始标记中的空格
                    case T_OPEN_TAG :
                        $string .= trim ( $value [1] ) . ' ';
                        $space = true;
                        break;
                    // 把空白字符全部转换为 空格
                    case T_WHITESPACE :
                        if ($space == false) {
                            $string .= ' ';
                            $space = true;
                        }
                        break;
                    // 去掉注释
                    case T_DOC_COMMENT :
                        $space = true;
                        break;
                    // 去掉注释
                    case T_COMMENT :
                        $space = true;
                        break;
                    // 判断定界符开始
                    case T_START_HEREDOC :
                        $space = false;
                        $string .= "<<<S\n";
                        break;
                    // 判断定界符结束
                    case T_END_HEREDOC :
                        $space = true;
                        $string .= "S;\n";
                        next ( $result );
                        break;
                    default :
                        if (in_array ( $value [0], $array )) {
                            $string = rtrim ( $string ) . $value [1];
                        } else {
                            $string .= $value [1];
                        }
                        $space = in_array ( $value [0], $array );
                        break;
                }
            }
            next ( $result );
        }
        unset($content,$result);
        return $string;
    }

    public static function html($file){
        $html_source = CompileFile::get($file);
        $chunks = preg_split( '/(<pre.*?\/pre>)/ms', $html_source, -1, PREG_SPLIT_DELIM_CAPTURE );
        $html_source = '';
        foreach ( $chunks as $c ){
            if ( strpos( $c, '<pre' ) !== 0 ){
                $c = preg_replace( '/[\\n\\r\\t]+/', ' ', $c );
                $c = preg_replace( '/\\s{2,}/', '', $c );
                $c = preg_replace( '/>\\s</', '><', $c );
                $c = preg_replace( '/\\/\\*.*?\\*\\//i', '', $c );
                $c = preg_replace( '/\\s{2,}/', '', $c );
            }
            $html_source .= $c;
        }
        unset($chunks,$c);
        return $html_source;
    }

    public static function css($file){
        $css_source = CompileFile::get($file);
        $css_source = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_source);
        $css_source = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css_source);
        return $css_source;
    }

    public function js($file) {
        $js_source = CompileFile::get($file);
        $this->setJSContent($js_source);
        $string = $this->min();
        return $string;
    }

    public function setJSContent($input) {
        $this->input       = str_replace("\r\n", "\n", $input);
        $this->inputLength = strlen($this->input);
    }
}
//file_put_contents('D:/phpStudy/www/commile_js.php',CompileFile::html('D:/phpStudy/www/birthDate.js'));