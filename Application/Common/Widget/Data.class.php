<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 2018/1/1
 * Time: 22:13
 */
namespace Common\Widget;
class Data
{
    public static $type_data = array(
        "pk10" => array("time" => 300, "title" => "北京赛车", "check" => "check_format_pk10", 'fun' => 'getPK10', 'robot_say' => 'pk10','db'=>'number'),
        "fei" => array("time" => 300, "title" => "幸运飞艇", "check" => "check_format_fei", 'fun' => 'getfei', 'robot_say' => 'pk10','db'=>'number'),
        "ssc" => array("time" => 600, "title" => "重庆时时彩", "check" => "check_format_ssc", 'fun' => 'getssc', 'robot_say' => 'ssc','db'=>'sscnumber'),
    "jnd28" => array("time" => 300, "title" => "加拿大28", "check" => "check_format_jnd28", 'fun' => 'getJnd28', 'robot_say' => 'jnd28','db'=>'dannumber'),
    "jsssc" => array("time" => 115, "title" => "极速时时彩", "check" => "check_format_jsssc", 'fun' => 'getjsssc', 'robot_say' => 'ssc','db'=>'sscnumber'),
    "jscar" => array("time" => 115, "title" => "极速赛车", "check" => "check_format_jscar", 'fun' => 'getjscar', 'robot_say' => 'pk10','db'=>'number'),
    "bj28" => array("time" => 300, "title" => "北京28", "check" => "check_format_bj28", 'fun' => 'getBj28', 'robot_say' => 'jnd28','db'=>'dannumber'),
    "kuai3" => array("time" => 600, "title" => "江苏快3", "check" => "check_format_kuai3", 'fun' => 'getkuai3', 'robot_say' => 'kuai3','db'=>'kuainumber'),
    "lhc" => array("time" => 259200, "title" => "香港六合彩", "check" => "check_format_lhc", 'fun' => 'getlhc', 'robot_say' => 'lhc','db'=>'lhcnumber'),
    "chat" => array("title" => "聊天室"),
);

    public static $game_data=array();

}