<?php

function ddev(...$vars)
{
    if (defined('IS_DEV')) {
        if (IS_DEV) {
            dd($vars);
        }
    }
}

if (!function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param array|string|null $key
     * @param mixed $default
     *
     * @return \Illuminate\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        $value = app('request')->__get($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param string $path
     *
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool $secure
     *
     * @return string
     */
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param string $path
     *
     * @return string
     */
    function public_path($path = '')
    {
        return rtrim(app()->basePath('public/' . $path), '/');

//        return app()->make('path.public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (!function_exists('files_path')) {
    /**
     * Get the path to the files folder.
     *
     * @param string $path
     *
     * @return string
     */
    function files_path($path = '')
    {
        return env('VOLUME_FILES', '/mnt/volume_sgp1_01/files') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

function qs_url($path = null, $qs = [], $secure = null)
{
    $url = app('url')->to($path, $secure);

    if (count($qs)) {
        foreach ($qs as $key => $value) {
            $qs[$key] = sprintf('%s=%s', $key, urlencode($value));
        }

        $url = sprintf('%s?%s', $url, implode('&', $qs));
    }

    return $url;
}

function time_ago($start, $end)
{
    $diff = strtotime($end) - strtotime($start);

    // Time difference in seconds
    $sec = $diff;

    // Convert time difference in minutes
    $min = round($diff / 60, 2);

    // Convert time difference in hours
    $hrs = round($diff / 3600, 2);

    // Convert time difference in days
    $days = round($diff / 86400, 2);

    // Convert time difference in weeks
    $weeks = round($diff / 604800, 2);

    // Convert time difference in months
    $mnths = round($diff / 2600640, 2);

    // Convert time difference in years
    $yrs = round($diff / 31207680, 2);

    // Check for seconds
    if ($sec <= 60) {
        return "$sec seconds";
    } else if ($min <= 60) {
        if ($min == 1) {
            return "1 minute";
        } else {
            return "$min minutes";
        }
    } else if ($hrs <= 24) {
        if ($hrs == 1) {
            return "1 hour";
        } else {
            return "$hrs hours";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "Yesterday";
        } else {
            return "$days days";
        }
    } else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return "1 week";
        } else {
            return "$weeks weeks";
        }
    } else if ($mnths <= 12) {
        if ($mnths == 1) {
            return "1 month";
        } else {
            return "$mnths months";
        }
    } else {
        if ($yrs == 1) {
            return "1 year";
        } else {
            return "$yrs years";
        }
    }
}

function hed($string, $quote_style = ENT_QUOTES, $charset = 'utf-8')
{
    return html_entity_decode($string, $quote_style, $charset);
}

function preUrlFilter(&$url, $list, $custom = [])
{
    foreach ($list as $key) {
        if (request()->query->has($key)) {
            if (isset($custom[$key])) {
                $url[$key] = $custom[$key];
            } else {
                $url[$key] = request()->query($key);
            }
        }
    }

    return $url;
}

function format_byte($num, $precision = 1)
{
    $num = (int)$num;
    $i = 0;
    $suffix = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    while (($num / 1024) > 1) {
        $num = $num / 1024;
        $i++;
    }

    return round(mb_substr($num, 0, mb_strpos($num, '.') + 4), $precision) . $suffix[$i];
}




function vn_filter_mark($str, $charSpace = '-')
{
    $str = utf8_strtolower($str);

    $str = str_replace(['à', 'À', 'á', 'Á', 'ạ', 'Ạ', 'ả', 'Ả', 'ã', 'Ã', 'â', 'Â', 'ầ', 'ấ', 'Ấ', 'ậ', 'Ậ', 'ẩ', 'Ẩ', 'ẫ', 'Ẫ', 'ă', 'Ă', 'ằ', 'Ằ', 'ắ', 'Ắ', 'ặ', 'Ặ', 'ẳ', 'Ẳ', 'ẵ', 'Ẵ'], "a", $str);
    $str = str_replace(['è', 'È', 'é', 'É', 'ẹ', 'Ẹ', 'ẻ', 'Ẻ', 'ẽ', 'Ẽ', 'ê', 'Ê', 'ề', 'Ề', 'ế', 'Ế', 'ệ', 'Ệ', 'ể', 'Ể', 'ễ', 'Ễ'], "e", $str);
    $str = str_replace(['ì', 'Ì', 'í', 'Í', 'ị', 'Ị', 'ỉ', 'Ỉ', 'ĩ', 'Ĩ'], "i", $str);
    $str = str_replace(['ò', 'Ò', 'ó', 'Ó', 'ọ', 'Ọ', 'ỏ', 'Ỏ', 'õ', 'Õ', 'ô', 'Ô', 'ồ', 'Ồ', 'ố', 'Ố', 'ộ', 'Ộ', 'ổ', 'Ổ', 'ỗ', 'Ỗ', 'ơ', 'Ơ', 'ờ', 'Ờ', 'ớ', 'Ớ', 'ợ', 'Ợ', 'ở', 'Ở', 'ỡ', 'Ỡ'], "o", $str);
    $str = str_replace(['ù', 'Ù', 'ú', 'Ú', 'ụ', 'Ụ', 'ủ', 'Ủ', 'ũ', 'Ũ', 'ư', 'Ư', 'ừ', 'Ừ', 'ứ', 'Ứ', 'ự', 'Ự', 'ử', 'Ử', 'ữ', 'Ữ', 'ù'], "u", $str);
    $str = str_replace(['ỳ', 'Ỳ', 'ý', 'Ý', 'ỵ', 'Ỵ', 'ỷ', 'Ỷ', 'ỹ', 'Ỹ', 'ý'], "y", $str);
    $str = str_replace(['đ', 'Đ'], "d", $str);
    $str = str_replace(['!', '@', '%', '\^', '*', '\(', '\)', '\+', '\=', '<', '>', '?', '/', '\\', ',', '\.', ':', '\;', '\'', ' ', '"', '\&', '\#', '\[', '\]', '~', '$', '&', ';', '_', '|'], $charSpace, $str);
    $str = str_replace(['-+-'], $charSpace, $str);
    $str = str_replace(['^\-+', '\-+$'], "", $str);
    $str = str_replace(['(', ')'], "", $str);

    return $str;
}

function unmark($str)
{
    $charSpace = ' ';

    $str = utf8_strtolower($str);

    $str = str_replace(['à', 'À', 'á', 'Á', 'ạ', 'Ạ', 'ả', 'Ả', 'ã', 'Ã', 'â', 'Â', 'ầ', 'ấ', 'Ấ', 'ậ', 'Ậ', 'ẩ', 'Ẩ', 'ẫ', 'Ẫ', 'ă', 'Ă', 'ằ', 'Ằ', 'ắ', 'Ắ', 'ặ', 'Ặ', 'ẳ', 'Ẳ', 'ẵ', 'Ẵ'], "a", $str);
    $str = str_replace(['è', 'È', 'é', 'É', 'ẹ', 'Ẹ', 'ẻ', 'Ẻ', 'ẽ', 'Ẽ', 'ê', 'Ê', 'ề', 'Ề', 'ế', 'Ế', 'ệ', 'Ệ', 'ể', 'Ể', 'ễ', 'Ễ'], "e", $str);
    $str = str_replace(['ì', 'Ì', 'í', 'Í', 'ị', 'Ị', 'ỉ', 'Ỉ', 'ĩ', 'Ĩ'], "i", $str);
    $str = str_replace(['ò', 'Ò', 'ó', 'Ó', 'ọ', 'Ọ', 'ỏ', 'Ỏ', 'õ', 'Õ', 'ô', 'Ô', 'ồ', 'Ồ', 'ố', 'Ố', 'ộ', 'Ộ', 'ổ', 'Ổ', 'ỗ', 'Ỗ', 'ơ', 'Ơ', 'ờ', 'Ờ', 'ớ', 'Ớ', 'ợ', 'Ợ', 'ở', 'Ở', 'ỡ', 'Ỡ'], "o", $str);
    $str = str_replace(['ù', 'Ù', 'ú', 'Ú', 'ụ', 'Ụ', 'ủ', 'Ủ', 'ũ', 'Ũ', 'ư', 'Ư', 'ừ', 'Ừ', 'ứ', 'Ứ', 'ự', 'Ự', 'ử', 'Ử', 'ữ', 'Ữ'], "u", $str);
    $str = str_replace(['ỳ', 'Ỳ', 'ý', 'Ý', 'ỵ', 'Ỵ', 'ỷ', 'Ỷ', 'ỹ', 'Ỹ'], "y", $str);
    $str = str_replace(['đ', 'Đ'], "d", $str);
    $str = str_replace(['!', '@', '%', '\^', '*', '(', ')', '\(', '\)', '\+', '\=', '<', '>', '?', '/', '\\', ',', '\.', ':', '\;', '\'', ' ', '"', '\&', '\#', '\[', '\]', '~', '$', '&', ';', '_', '|'], $charSpace, $str);
    $str = str_replace(['-+-'], $charSpace, $str);
    $str = str_replace(['^\-+', '\-+$'], "", $str);
    $str = preg_replace('/\s+/', ' ', $str);

    return $str;
}

function w1250_to_utf8($text)
{
    // map based on:
    // http://konfiguracja.c0.pl/iso02vscp1250en.html
    // http://konfiguracja.c0.pl/webpl/index_en.html#examp
    // http://www.htmlentities.com/html/entities/
    $map = [
        chr(0x8A) => chr(0xA9),
        chr(0x8C) => chr(0xA6),
        chr(0x8D) => chr(0xAB),
        chr(0x8E) => chr(0xAE),
        chr(0x8F) => chr(0xAC),
        chr(0x9C) => chr(0xB6),
        chr(0x9D) => chr(0xBB),
        chr(0xA1) => chr(0xB7),
        chr(0xA5) => chr(0xA1),
        chr(0xBC) => chr(0xA5),
        chr(0x9F) => chr(0xBC),
        chr(0xB9) => chr(0xB1),
        chr(0x9A) => chr(0xB9),
        chr(0xBE) => chr(0xB5),
        chr(0x9E) => chr(0xBE),
        chr(0x80) => '&euro;',
        chr(0x82) => '&sbquo;',
        chr(0x84) => '&bdquo;',
        chr(0x85) => '&hellip;',
        chr(0x86) => '&dagger;',
        chr(0x87) => '&Dagger;',
        chr(0x89) => '&permil;',
        chr(0x8B) => '&lsaquo;',
        chr(0x91) => '&lsquo;',
        chr(0x92) => '&rsquo;',
        chr(0x93) => '&ldquo;',
        chr(0x94) => '&rdquo;',
        chr(0x95) => '&bull;',
        chr(0x96) => '&ndash;',
        chr(0x97) => '&mdash;',
        chr(0x99) => '&trade;',
        chr(0x9B) => '&rsquo;',
        chr(0xA6) => '&brvbar;',
        chr(0xA9) => '&copy;',
        chr(0xAB) => '&laquo;',
        chr(0xAE) => '&reg;',
        chr(0xB1) => '&plusmn;',
        chr(0xB5) => '&micro;',
        chr(0xB6) => '&para;',
        chr(0xB7) => '&middot;',
        chr(0xBB) => '&raquo;',
    ];

    return html_entity_decode(mb_convert_encoding(strtr($text, $map), 'UTF-8', 'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
}

function get_first_character($str, $slug = ' ')
{
    $words = explode($slug, $str);

    $text = '';

    foreach ($words as $w) {
        if ($w) {
            $text .= $w[0];
        }
    }

    return $text;
}

