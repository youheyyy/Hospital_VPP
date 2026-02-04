<?php

if (!function_exists('convertNumberToWords')) {
    function convertNumberToWords($number)
    {
        $number = (int) $number;
        
        if ($number == 0) {
            return 'không';
        }

        $units = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tens = ['', 'mười', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
        $levels = ['', 'nghìn', 'triệu', 'tỷ'];

        $result = '';
        $level = 0;

        while ($number > 0) {
            $group = $number % 1000;
            if ($group > 0) {
                $groupText = convertGroupToWords($group, $units, $tens);
                $result = $groupText . ' ' . $levels[$level] . ' ' . $result;
            }
            $number = (int)($number / 1000);
            $level++;
        }

        return trim($result);
    }
}

if (!function_exists('convertGroupToWords')) {
    function convertGroupToWords($number, $units, $tens)
    {
        $result = '';

        $hundred = (int)($number / 100);
        $remainder = $number % 100;
        $ten = (int)($remainder / 10);
        $unit = $remainder % 10;

        if ($hundred > 0) {
            $result .= $units[$hundred] . ' trăm';
            if ($remainder > 0 && $remainder < 10) {
                $result .= ' lẻ';
            }
        }

        if ($ten > 1) {
            $result .= ' ' . $tens[$ten];
            if ($unit == 1) {
                $result .= ' mốt';
            } elseif ($unit == 5 && $ten >= 1) {
                $result .= ' lăm';
            } elseif ($unit > 0) {
                $result .= ' ' . $units[$unit];
            }
        } elseif ($ten == 1) {
            $result .= ' mười';
            if ($unit == 5) {
                $result .= ' lăm';
            } elseif ($unit > 0) {
                $result .= ' ' . $units[$unit];
            }
        } elseif ($unit > 0) {
            $result .= ' ' . $units[$unit];
        }

        return trim($result);
    }
}
