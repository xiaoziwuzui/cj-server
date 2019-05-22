<?php

class FException extends Exception {
    /**
     * @var FView|null
     */
    public $view = null;

    public function __construct() {
        parent::__construct();
        $this->view = new FView;
    }

    /**
     * @param array|string|mixed $e Exception
     */
    public function traceError($e) {
        global $_F;
        if (!is_array($e)) {
            $error_code = $e->getCode();
        }

        if (is_array($e)) {
            $error_message = $e['message'];
            $error_file = $e['file'];
            $error_line = $e['line'];
        } else {
            $error_message = $e->getMessage();
            $error_file = $e->getFile();
            $error_line = $e->getLine();
            $exception_trace = nl2br($e->__toString());
        }


        $exception_message = $error_message
            . '<br /> 异常出现在：' . $error_file . ' 第 ' . $error_line . ' 行';

	$log_text = "\n--------------------------------------------------------\n";

	if ($_F['current_sql']) {
		$log_text .= "SQL: " . $_F['current_sql'] . "\n";
		$log_text .= "--------------------------------------------------------\n";
	}

	if (is_object($e)) {
		$log_text .= $e->__toString() . "\n";
		$log_text .= "--------------------------------------------------------\n";
	} else {
		$log_text .= $error_message . "\n"; 
		$log_text .= "--------------------------------------------------------\n";
	}

        if ($_F['run_in'] == 'shell') {
            $log_text_header = "\n\n          ==========================================\n                         ERROR FOUND\n          ========================================== \n";
            die($log_text_header . $log_text);
        }

        FLogger::write($log_text, 'error');

        if (!$_F['debug']) {
            if ($error_code == 404) {
                FResponse::sendStatusHeader(404);
                $this->view->displaySysPage('404.tpl');
//                echo "<strong>404 NOT FOUND</strong>";
            } else {
                FResponse::sendStatusHeader(500);
                $this->view->displaySysPage('500.tpl');
            }
            exit;
        }

        if ($_F['in_ajax']) {
            if ($_F['debug']) {
                FResponse::output(array('result' => 'exception', 'content' => preg_replace('/<br.+?>/i', "\n", $exception_message)));
                exit;
            } else {
                if ($error_code == 404) {
                    FResponse::sendStatusHeader(404);
                } else {
                    FResponse::sendStatusHeader(500);
                }
                exit;
            }
        }

        header('HTTP/1.1 500 WLib Error');
        header('status: 500 WLib Error');
        $exception_message = str_replace(APP_ROOT, '', $exception_message);
        $exception_trace = str_replace(APP_ROOT, '', $exception_trace);

        $this->view->set('exception_message', str_replace(APP_ROOT, '', $exception_message));
        $this->view->set('exception_trace', preg_replace('#[\w\d \#]+?/f.php.+?$#si', ' 引导入口', $exception_trace));

        $this->view->displaySysPage('exception.tpl');
    }
}
