<?php

$dom = new DomDocument();

date_default_timezone_set("America/Toronto");

if ((!strlen($_SERVER['QUERY_STRING']) == 0) and (!is_null($_SERVER['QUERY_STRING'])) and (!empty($_SERVER['QUERY_STRING']))) {
  $url = $_SERVER['QUERY_STRING'];
}

$plen = strlen($_SERVER['QUERY_STRING']);

$file = 'trackurls';
$current = file_get_contents($file);
$current .= date("Y-m-d_H:i:s");
$current .= "\n";
$current .= "remote_addr_";
$current .= $_SERVER['REMOTE_ADDR'];
$current .= "\n";
$current .= "__pre_SQS";
$current .= $_SERVER['QUERY_STRING'];
$current .= "\n";
$current .= "prelen= "
$current .= $plen;
$current .= "\n";
$current .= "__begin_url__";
$current .= $url;
$current .= "__";
$current .= "\n";

if (strlen($_SERVER['QUERY_STRING']) == 0) {
  $current .= "slen_SQS=0";
  $current .= "\n";
  $url = 'r=https://www.apnews.com';
}

if (is_null($_SERVER['QUERY_STRING'])) {
  $current .= "isnull_sqs";
  $current .= "\n";
  $url = 'r=https://www.apnews.com';
}

if (empty($_SERVER['QUERY_STRING'])) {
  $current .= "empty_SQS";
  $current .= "\n";
  $url = 'r=https://www.apnews.com';
}

$url = rawurldecode($url);
$current .= "after rawdecode=";
$current .= $url;
$current .= "__\n";

$posr = strpos($url, "r=");
if ($posr === false) {
  $current .= "r= is false";
  $current .= "\n";
  $url = 'r=https://www.apnews.com';
}

$posw = strpos($url, "http");
if ($posw === false) {
  $current .= "http is false";
  $current .= "\n";
  $url = 'r=https://www.apnews.com';
}

if ((strlen($url) == 0) or (is_null($url)) or (empty($url))) {
  $current .= "slenurl=0 or url_is_null or url_empty";
  $current .= "\n";
  $url = "r=https://www.apnews.com";
}

$posc = strpos($url, "lang=en&fbclid=");

if ($posc !== false) {
  $current .= "spos posc fbclid is true";
  $current .= "\n";
  $url = "r=https://www.apnews.com";
}

$pos = strpos($url, "http");

if ($pos === false) {
  $current .= "lower pos http is false";
  $current .= "\n";
  $url = "r=https://www.apnews.com";
}

$posd = strpos($url, "?action=");

if ($posd !== false) {
  $current .= "posd pos action is true";
  $current .= "\n";
  $url = "r=https://www.apnews.com";
}

$posr = strpos($url, "r=");
if ($posr === false) {
  $current .= "posr r= is false";
  $current .= "\n";
  $url = "r=" . $url;
}

$str2 = substr($url, 2);
$url = $str2;

$current .= "\n";
$current .= "posr=";
$current .= $posr;
$current .= "__\n";
$current .= "posw=";
$current .= $posw;
$current := "__\n";
$current .= "posc=";
$current .= $posc;
$current .= "__\n";
$current .= "pos=";
$current .= $pos;
$current .= "__\n";
$current .= "__post_url__";
$current .= $url;
$current .= "\n";
file_put_contents($file, $current);

//$fake_user_agent = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11";
$fake_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0";

//ini_set('user_agent', $fake_user_agent);

$options = array('http' => array('user_agent'    => $fake_user_agent,
				 'ignore_errors' => true),
                 'ssl'  => array(
		        	 'verify_peer' => false, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
	 		         'cafile'      => '/etc/ssl/certs/ca-certificates.crt',
		       	 	 'ciphers'     => 'HIGH:!SSLv2:!SSLv3',
		       	     	 'disable_compression' => true)
		 );

$context = stream_context_create($options);
$page    = file_get_contents($url, false, $context);

libxml_use_internal_errors(true);

if (! $dom->loadHTML($page) )  { echo "failed to load page $page\n"; }; 

libxml_use_internal_errors(false);

$xpath = new DOMXpath($dom);
$heading=parseToArray($xpath,'title','h1');

$tags = @get_meta_tags($url);

// libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings

$query = '//*/meta[starts-with(@property, \'og:\')]';
$metas = $xpath->query($query);
foreach ($metas as $meta) {
    $property = $meta->getAttribute('property');
    $content = $meta->getAttribute('content');
    $rmetas[$property] = $content;
}
//var_dump($rmetas);

$domain    = explode("/", $url,4);
//echo "::: domain: ". $domain[2] . "\n";

$domain[2] = strtoupper(preg_replace("/^www\./","", $domain[2]));

//echo "::: domain: ". $domain[2] . "\n";
//echo "\n\n";

$rmetas["og:title"] = $domain[2] ." // ". $rmetas["og:title"];

$heading[1] = $rmetas["og:title"];

echo "<!DOCTYPE html>\n <html lang='en'>\n  <head>\n\n";

// echo "<!-- ". $url . "-->\n\n";

header($url);

echo "   <title>". $heading[1] ."</title>\n";
echo '   <meta data-rh="true" name="description" content="'   . $tags["description"].   '">' ."\n";
echo '   <meta data-rh="true" name="twitter:image" content="' . $tags["twitter:image"]. '">' ."\n";

echo '   <meta data-hr="true" property="og:title"       content="' . $rmetas["og:title"]       . '">' ."\n";
echo '   <meta data-hr="true" property="og:description" content="' . $rmetas["og:description"] . '">' ."\n";
echo '   <meta data-hr="true" property="og:image"       content="' . $rmetas["og:image"]       . '">' ."\n";

if ($url == "" or !preg_match('/^http/',$url) ) { 
  echo "</head><body>\nError - bad url: '<b>$url</b>'<br>\nuse <b>". $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0] . "?r=URL</b> please with http or https in url\n\n</body></html>\n";
  exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////
function parseToArray($xpath,$elem1,$elem2)
{
    $xpathquery="//$elem1 | //$elem2";
    $elements = $xpath->query($xpathquery);

    if (!is_null($elements)) {  
        $resultarray=array();
        foreach ($elements as $element) {
            $nodes = $element->childNodes;
            foreach ($nodes as $node) {
              $resultarray[] = $node->nodeValue;
            }
        }
        return $resultarray;
}   }

?>

  <script type="text/javascript" language="javascript">
    window.location.href = "<?php echo $url; ?>";
  </script>
  </body></html>

