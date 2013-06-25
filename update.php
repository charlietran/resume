<?php

$target_dir = '~/charlietran.com/resume/';

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);

$github_ips = array('207.97.227.253', '50.57.128.197', '108.171.174.178', '50.57.231.61');
$github_cidrs = array('204.232.175.64/27', '192.30.252.0/22');

function cidr_match($ip, $ranges)
{
  $ranges = (array)$ranges;
  foreach($ranges as $range) {
    list($subnet, $mask) = explode('/', $range);
    if((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
      return true;
    }
  }
  return false;
}

try {
  $payload = json_decode($_POST['payload']);
}
catch(Exception $e) {
  file_put_contents($target_dir.'logs/github.txt', $e . ' ' . $payload, FILE_APPEND);
  exit(0);
}

if ( $_POST['payload'] && (in_array($_SERVER['REMOTE_ADDR'], $github_ips) || cidr_match($_SERVER['REMOTE_ADDR'], $github_cidrs)) ) {
  $output = shell_exec( "cd $target_dir && git reset --hard HEAD 2>&1 && git pull 2>&1" );
}

else {
  header('HTTP/1.1 404 Not Found');
  echo '404 Not Found.';
  exit;
}
