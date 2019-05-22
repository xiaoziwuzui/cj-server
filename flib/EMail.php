<?php

class EMail {

    public $user_smtp = true;

    public function send($recs, $title, $contents, $from = array()) {
        if ($this->user_smtp) {
            $mail = new Mail_SMTP;
            $mail->send($recs, $title, $contents);
        } else {
            $this->_send($recs, $title, $contents);
        }
    }

    public function _send($recs, $title, $contents, $from = array('name' => '游本 OA', 'mail' => 'ferris@upnb.com')) {

        $recs = explode(',', $recs);

        //var_dump($recs);exit;

        $mailtos = array();

        foreach ($recs as $item) {
            if (Strpos($item, '@') === false) {
                continue;
            }

            if (strpos($item, '<')) {
                $name = trim(substr($item, 0, strpos($item, '<')));
                $name = base64_encode($name);

                $email = substr($item, strpos($item, '<') + 1);
                $email = trim(str_replace('>', '', $email));

                $mailtos[] .= "=?UTF-8?B?{$name}?= <{$email}>";
            } else {

                $name = trim(substr($item, 0, strpos($item, '@')));
                $email = trim($item);

                $mailtos[] .= "{$name} <{$email}>";
            }
        }

        $mailtos = join(',', $mailtos);

        $title = "=?UTF-8?B?" . base64_encode($title) . "?="; //防止标题变乱码

        $headers = "From: {$from['name']} <{$from['mail']}> \n";
        $headers .= "X-Sender: \n";
        $headers .= "X-Mailer: PHP\n";
        $headers .= "X-Priority: 1\n";
        $headers .= "Return-Path: \n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        mail($mailtos, $title, $contents, $headers);
    }
}
