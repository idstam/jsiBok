<?php
if (function_exists('get_month_name')) {
    function get_month_name($month)
    {
        $months = array(
            1 => 'January',
            2 => 'Februari',
            3 => 'Mars',
            4 => 'April',
            5 => 'Maj',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Augusti',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'December'
        );

        return $months[$month];
    }
}
if (function_exists('format_bc_number')) {
    function format_bc_number($number): string
    {
        $tokens = str_split(strrev($number), 3);
        $ret = join(' ', $tokens);
        $ret = strrev($ret);
        $ret = str_replace(' .', ',', $ret);


        return $ret;
    }
}
if (!function_exists('ensure_date')) {
    /**
     * Return a date without time from the parameter that can de a DateTime ocr a string: Y-m-d / Ymd
     * Return format is Ymd
     *
     * @param DateTime|string $date
     * @return DateTime
     */
    function ensure_date(DateTime|string $date, string $format = 'Ymd'): DateTime
    {

        if ($date instanceof DateTime) {
            $date->settime(0,0);
            return $date;
        }

        $ret = date_create_from_format($format, $date);
        if (!$ret) {
            $ret = date_create_from_format('Y-m-d',substr($date, 0, 10));
        }

        if (!$ret) {
            $ret = date_create_from_format('Ymd',substr($date, 0, 8));
        }
        $ret->settime(0,0);
        return $ret;
    }

    function ensure_date_string(DateTime|string $date, string $format = 'Ymd'): string
    {
        $date = ensure_date($date, $format);
        return $date->format($format);
    }
}
if (!function_exists('random_str')) {
    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int $length How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    function random_str(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    )
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
}

if (!function_exists('slackIt')) {
    function slackIt($logLevel, $text, $correlationID, $sendToLog = true)
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            // is not PHPUnit run
            return 0;
        }

        if($sendToLog) {
            log_message($logLevel, $text . "  " . $correlationID);
        }

        $emoji = ":ok: ";

        if ($logLevel === "debug") {
            return 0;
        }

        if ($logLevel === "warning" || $logLevel === "warn") {
            $emoji = ":ghost: ";
        }


        if ($logLevel === "error") {
            $emoji = ":fire: ";
            //mailgun('error@huvudboken.se', 'Murphy', 'Errorhandler', 'error@huvudboken.se', 'ERROR ' . $correlationID, '', $text . ' ' . $correlationID, 'error', 'error@huvudboken.se');
        }


        $data = array("text" => $emoji . $text . "  " . $correlationID);
        $data_string = json_encode($data);

        $ch = curl_init(getenv('app.slackUrl'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );

        $result = curl_exec($ch);
        return $result;
    }
}

if (!function_exists('mailgun')) {
    function mailgun($to, $toname, $mailfromnane, $mailfrom, $subject, $html, $text, $tag, $replyto)
    {
        if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! defined('__PHPUNIT_PHAR__')) {
            // is not PHPUnit run
            return;
        }

        $mgurl = 'https://api.eu.mailgun.net/v3/mg.huvudboken.se';
        $mgkey = env('mailgun.key');

        $array_data = array(
            'from' => $mailfromnane . '<' . $mailfrom . '>',
            'to' => $toname . '<' . $to . '>',
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
            'o:tracking' => 'yes',
            'o:tracking-clicks' => 'yes',
            'o:tracking-opens' => 'yes',
            'o:tag' => $tag,
            'h:Reply-To' => $replyto
        );

        $session = curl_init($mgurl . '/messages');
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_USERPWD, 'api:' . $mgkey);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);
        curl_close($session);

        $results = json_decode($response, true);

        if (is_array($results) && array_key_exists('message', $results) && str_contains($results['message'], 'Queued')) {
            return true;
        }

        slackIt('error', 'Failed to send email ' . $subject . ' to ' . $to . ' - ' . $response, '');


        return false;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return substr($haystack, 0, strlen($needle)) == $needle;
    }
}

