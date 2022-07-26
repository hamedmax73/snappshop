<?php

if (! function_exists('en_to_fa')) {
    /**
     * Convert english digits to farsi.
     *
     * @param string $text
     * @return string
     */
    function en_to_fa(string $text): string
    {
        $en_num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa_num = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($en_num, $fa_num, $text);
    }
}

if (! function_exists('fa_to_en')) {
    /**
     * Convert farsi/arabic digits to english.
     *
     * @param string $text
     * @return string
     */
    function fa_to_en(string $text): string
    {
        $fa_num = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $en_num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($fa_num, $en_num, $text);
    }
}
