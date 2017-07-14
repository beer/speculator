<?php
class Helper
{
    // 抓每月結算日時間
    public static function http_post_fields($url, $fields)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, "");
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
        return curl_exec($curl);
    }
}
