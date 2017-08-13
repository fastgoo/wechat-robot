<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/8/13
 * Time: 上午11:00
 */
$key = !empty($_GET['app_id']) ? $_GET['app_id'] : 123456;

exec('php run.php start ' . $key);

