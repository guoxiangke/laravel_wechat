<?php

if (! function_exists('fast_headers')) {
    /**
     * [fast_headers faster get_headers with curl.].
     * @param  [string] $url [description]
     * @return [array or false]      [description]
     */
    function fast_headers($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // https://stackoverflow.com/questions/28858351/php-ssl-certificate-error-unable-to-get-local-issuer-certificate
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // https://cheapsslsecurity.com/blog/ssl-certificate-problem-unable-to-get-local-issuer-certificate/
        $certificate_location = '/var/www/html/docker/cacert.pem';
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
        $res = curl_exec($ch);
        if ($res === false) {
            Log::error(__FUNCTION__, [__LINE__, $url, curl_error($ch)]);
        } else {
            $res = explode("\r\n", $res);
        }

        curl_close($ch);

        return $res;
    }
}

if (! function_exists('semiangle_texts')) {
    // 全角半角转．
    function semiangle_texts($str)
    {
        // $search =  array('[',']',"'",'"', "收听",'','.','○','o','〇',',',':','|',' ','一',);
        // $replace =  array('','','','','', '', '', '0', '0','0','', '','', '', '',);
        // $keyword = str_replace($search, $replace, $keyword);
        //  '○' =>  '0','〇' =>  '0', '`' =>  '',
        //  ':' =>  '', ' ' => '', '井' =>  '#',
        $arr = [
        '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
        '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
        'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
        'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
        'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
        'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
        'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
        'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
        'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
        'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
        'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
        'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
        'ｙ' => 'y', 'ｚ' => 'z',
        '（' => '', '）' => '', '〔' => '', '〕' => '', '【' => '[',
        '】' => ']', '〖' => '[', '〗' => ']', '“' => '', '”' => '"',
        '‘' => '"', '’' => '"', '｛' => '{', '｝' => '}', '《' => '<',
        '》' => '>',
        '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
        // '：' => ':', '。' => '.', '、' => ',', '，' => ',', '、' => '.',
        // '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '||',
        '”' => '"', '’' => '', '‘' => '', '｜' => '', '〃' => '',
        '　' => ' ', '＄' => '$', '＠' => '@', '＃' => '#', '＾' => '^', '＆' => '&', '＊' => '*',
        '＂' => '"',
    ];

        return strtr($str, $arr);
    }
}

if (! function_exists('get_html')) {
    /* gets the data from a URL */
    function get_html($url)
    {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        if (! $data) {
            Log::error(__FUNCTION__, [__LINE__, $url, curl_error($ch)]);
            $data = file_get_contents($url) or die('curl die $url');
        }
        curl_close($ch);

        return $data;
    }
}
