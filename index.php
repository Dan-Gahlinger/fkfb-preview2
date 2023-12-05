<?php

// Version 27 Dec 5/2023 10:24AM EST/EDT - DG/transnet
//

// reputable news organizations for someone who randomly stumbled on this document
$urls = array(
                'https://www.reuters.com/',
                'https://apnews.com/',
                'https://www.bbc.com/',
                'https://www.cbc.ca/',
                'https://www.aljazeera.com/',
                'https://www.dw.com/',
                'https://www.afp.com/',
                'https://www3.nhk.or.jp/nhkworld/',
                'https://www.abc.net.au/',
                'https://www.npr.org/',
                'https://www.pbs.org/',
              );

$array_key = array_rand($urls, 1);

//

$dom = new DomDocument();

date_default_timezone_set("America/Toronto");

$url = $urls[$array_key];

// $url = 'r=https://www.apnews.com';

// $r = 'r=https://www.apnews.com';
$r = "r=" . $urls[$array_key];

$file = 'trackurls';

if ($_SERVER['REQUEST_METHOD'] != "GET") {
    // $url = 'r=https://www.apnews.com';
    $url = $urls[$array_key];
    $current = file_get_contents($file);
    $current .= date("Y-m-d_H:i:s");
    $current .= "\n";
    $current .= "remote_addr_";
    $current .= $_SERVER['REMOTE_ADDR'];
    $current .= "\n";
    file_put_contents($file, $current);  
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($_SERVER['QUERY_STRING']) {
	// requests are sometimes sent as urlencoded-strings with a stupid FB
	// client tracker ID tacked on as a query string, so decode as needed and
	// discard the tracker ID
	$posb = strpos($url, "http");
        $current = file_get_contents($file);
	$current .= "\n";
        $current .= date("Y-m-d_H:i:s");
        $current .= "\n";
	$current .= "remote_addr_";
	$current .= $_SERVER['REMOTE_ADDR'];
	$current .= "\n";
        $current .= "_pre_process_";
        $current .= $_SERVER['QUERY_STRING'];
        $current .= "__";
	$current .= "\n";
        file_put_contents($file, $current);	
	if ((!strlen($_SERVER['QUERY_STRING']) == 0) and (!is_null($_SERVER['QUERY_STRING'])) and (!empty($_SERVER['QUERY_STRING'])) and ($posb !== false)) {
	  $url = $_SERVER['QUERY_STRING'];
	}
	if ((strlen($_SERVER['QUERY_STRING']) == 0) or (is_null($_SERVER['QUERY_STRING'])) or (empty($_SERVER['QUERY_STRING'])) or ($posb === false)) {
	  // $url = 'r=https://www.apnews.com';
          $url = "r=" . $urls[$array_key];
	}
    }
}

$url = rawurldecode($url);
$current = file_get_contents($file);
$current .= "\n";
$current .= "_rawdecode_";
$current .= $url;
$current .= "__\n";
file_put_contents($file, $current);

if ((strlen($url) == 0) or (is_null($url)) or (empty($url))) {
    // $url = 'r=https://www.apnews.com';
    $url = "r=" . $urls[$array_key];
}

$posc = strpos($url, "lang=en&fbclid=");

if ($posc !== false) {
    // $url = 'r=https://www.apnews.com';
    $url = "r=" . $urls[$array_key];
}

$pos = strpos($url, "http");

if ($pos === false) {
    $url = "r=" . $urls[$array_key];
    // $url = 'r=https://www.apnews.com';
}

$posd = strpos($url, "?action=");

if ($posd !== false) {
    $url = "r=" . $urls[$array_key];
    // $url = 'r=https://www.apnews.com';
}

$posr = strpos($url, "r=");
if ($posr === false) {
   $url = "r=" . $url;
}

$str2 = substr($url, 2);
$url = $str2;

$current = file_get_contents($file);
$current .= "\n";
$current .= "remote_addr_";
$current .= $_SERVER['REMOTE_ADDR'];
$current .= "\n";
file_put_contents($file, $current);


$url = "Location: " . $url;
header($url);
exit();
