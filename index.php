<?php
//error_reporting(0);
//@ini_set('display_errors', 0);

if(!isset($_GET['image_url']) || empty($_GET['image_url'])){
exit("GET IMAGE URL IS EMPTY");
}

if(!isset($_GET['user_id']) || empty($_GET['user_id'])){
exit("GET USER_ID URL IS EMPTY");
}

if(!isset($_GET['album_id']) || empty($_GET['album_id'])){
exit("GET ALBUM_ID URL IS EMPTY");
}



function grab_image($URL){
$nameok= uniqid().'-'.basename($URL);
	$ibasname= preg_replace('/([^a-z0-9-_\.]+)/i', '-', $nameok);

	$filesname= trim(preg_replace('/\.(jpe?g|png|gif|webp|wmp)(.*)/i', '', $ibasname)).'.jpg';

		if(file_exists($filesname)){
			@unlink($filesname);
		}
preg_match('/([a-zA-Z0-9-_.]+)\/(.*)/i', $URL, $outdomain);

		//blacklist_image
	if(preg_match('/(c.shld.net|xhamster.com)/i', $outdomain[1])){
		return 'error';
	}

		if(preg_match('/(blogspot.com|imgur.com|ytimg.com|youtube.com|i([0-9]+).wp.com)/i', $outdomain[1])){
			$IMAGE_URL= 'http://'.$URL;
		}else{
					$IMAGE_URL= 'http://i0.wp.com/'.preg_replace('/https?:\/\//i', '', $URL);
			
		}
	//$IMAGE_URL=$URL;
		
	$data = curl_init();
	$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
	$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language: en-us,en;q=0.5";
	$header[] = "Pragma: "; // browsers keep this blank.
     curl_setopt($data, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($data, CURLOPT_SSL_VERIFYPEER, FALSE);
     curl_setopt($data, CURLOPT_URL, $IMAGE_URL);
	 curl_setopt($data, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
	 curl_setopt($data, CURLOPT_HTTPHEADER, $header);
	 curl_setopt($data, CURLOPT_REFERER, 'http://'.$outdomain[1]);
	 curl_setopt($data, CURLOPT_ENCODING, 'gzip,deflate');
	 curl_setopt($data, CURLOPT_AUTOREFERER, true);
	 curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($data, CURLOPT_CONNECTTIMEOUT, 10);
	 curl_setopt($data, CURLOPT_TIMEOUT, 10);
	 curl_setopt($data, CURLOPT_MAXREDIRS, 3);
	 curl_setopt($data, CURLOPT_FOLLOWLOCATION, true);
     $hasil = curl_exec($data);
     curl_close($data);
if(strlen($hasil) < 150){
	return 'error';
}	
	$fff= fopen($filesname,"w");
	fwrite($fff, $hasil);
	fclose($fff);

return $filesname;	 
}



function IS_CURL($URL){
$data = curl_init();
	$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
	$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language: en-us,en;q=0.5";
	$header[] = "Pragma: "; // browsers keep this blank.

     curl_setopt($data, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($data, CURLOPT_SSL_VERIFYPEER, FALSE);
     curl_setopt($data, CURLOPT_URL, $URL);
     curl_setopt($data, CURLOPT_USERAGENT, 'Googlebot');
     curl_setopt($data, CURLOPT_HTTPHEADER, $header);
     curl_setopt($data, CURLOPT_REFERER, 'http://fipkas.com');
	 curl_setopt($data, CURLOPT_ENCODING, 'gzip,deflate');
	 curl_setopt($data, CURLOPT_AUTOREFERER, true);
	 curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($data, CURLOPT_CONNECTTIMEOUT, 10);
	 curl_setopt($data, CURLOPT_TIMEOUT, 10);
	 curl_setopt($data, CURLOPT_MAXREDIRS, 3);
	 curl_setopt($data, CURLOPT_FOLLOWLOCATION, true);

     $hasil = curl_exec($data);
     curl_close($data);	
return $hasil;
}


function check_album($userId, $albumid, $access_token){
$curl = curl_init();
$url = 'https://picasaweb.google.com/data/entry/api/user/'.$userId.'/albumid/'.$albumid;
curl_setopt_array( $curl, 
                 array( CURLOPT_CUSTOMREQUEST => 'GET'
                       , CURLOPT_URL => $url
                       , CURLOPT_HTTPHEADER => array( 'GData-Version: 2'
                                                     , 'Authorization: Bearer '.$access_token )
                       , CURLOPT_REFERER => 'https://photos.google.com/'
                       , CURLOPT_RETURNTRANSFER => 1 
                 ) );
$response = curl_exec($curl);
$http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
curl_close($curl);

  if($http_code == 200){
return $response;
  }
return 'Error';
}


function create_album($nama, $access_token, $userId){
$rawXml = "<entry xmlns='http://www.w3.org/2005/Atom'
                xmlns:media='http://search.yahoo.com/mrss/'
                xmlns:gphoto='http://schemas.google.com/photos/2007'>
              <title type='text'>".$nama."</title>
              <summary type='text'>Description ".$nama."</summary>
              <gphoto:location>Louisville</gphoto:location>
              <gphoto:access>public</gphoto:access>
              <gphoto:timestamp>".time()."</gphoto:timestamp>
              <category scheme='http://schemas.google.com/g/2005#kind'
                term='http://schemas.google.com/photos/2007#album'></category>
            </entry>";

$curl = curl_init();
$url = 'https://picasaweb.google.com/data/feed/api/user/'.$userId;
    curl_setopt_array( $curl, array(
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_SSL_VERIFYPEER=> false,
CURLOPT_URL => $url,
CURLOPT_POST=> true,
CURLOPT_FOLLOWLOCATION=> true,
CURLOPT_POSTFIELDS=> $rawXml,
CURLOPT_HTTPHEADER => array( 
'GData-Version: 2',
'Content-Type:  application/atom+xml',
'Authorization: Bearer '.$access_token),
CURLOPT_REFERER => 'http://fipkas.com/',
CURLOPT_RETURNTRANSFER => 1 
                 ) );
$response = curl_exec($curl);
$http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
curl_close($curl);

return $http_code.' = '.$response;
}


function upload_photo($userId, $albumId, $access_token, $fileimagesq){
$imgName= $fileimagesq;

$fileSize = filesize($imgName);
$fh = fopen($imgName, 'rb');
$imgData = fread($fh, $fileSize);
fclose($fh);
$CLOUD_FILENAMES= $fileimagesq;

$header = array(
'GData-Version:  2', 
'Authorization: Bearer '.$access_token, 
'Content-Type: image/jpeg',
'Content-Length: '.$fileSize,
'Slug: '.$CLOUD_FILENAMES);

$data = $imgData;

$curl = curl_init();
$url = 'https://picasaweb.google.com/data/feed/api/user/'.$userId.'/albumid/'.$albumId;
    curl_setopt_array( $curl, array(
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_SSL_VERIFYPEER=> false,
CURLOPT_URL => $url,
CURLOPT_POST=> true,
CURLOPT_FOLLOWLOCATION=> true,
CURLOPT_POSTFIELDS=> $data,
CURLOPT_HTTPHEADER => $header,
CURLOPT_REFERER => 'http://fipkas.com/',
CURLOPT_RETURNTRANSFER => 1 
                 ) );
$response = curl_exec($curl);
//$http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
curl_close($curl);

return $response;

}




function GetPicasaToken(){
 $old_token= file_get_contents("access_token.txt");
$uri_check_token= IS_CURL('https://www.googleapis.com/oauth2/v1/tokeninfo?access_token='.$old_token);
$doldtoken= json_decode($uri_check_token,1);

if(!isset($doldtoken['error']) && !isset($doldtoken['error_description'])){
return $old_token;
}

$refresh_token= file_get_contents("refresh_token.txt");
$postBody = 'client_id='.urlencode('736109756646-8vd8v536fpmt10uje7oh8j85448mvj67.apps.googleusercontent.com').'&client_secret='.urlencode('Qy-qIiZkyhz6twvEyE5QsEO9').'&refresh_token='.urlencode($refresh_token).'&grant_type=refresh_token';
          
    $curl = curl_init();
    curl_setopt_array( $curl,
                     array( CURLOPT_CUSTOMREQUEST => 'POST'
                           , CURLOPT_URL => 'https://www.googleapis.com/oauth2/v3/token'
                           , CURLOPT_HTTPHEADER => array( 'Content-Type: application/x-www-form-urlencoded'
                                                         , 'Content-Length: '.strlen($postBody)
                                                         , 'User-Agent: DafaMedia'
                                                         )
                           , CURLOPT_POSTFIELDS => $postBody                              
                           , CURLOPT_REFERER => 'https://photos.google.com/'
                           , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
                           , CURLOPT_TIMEOUT => 12 // max seconds to wait
                           , CURLOPT_FOLLOWLOCATION => 0 // don't follow any Location headers, use only the CURLOPT_URL, this is for security
                           , CURLOPT_FAILONERROR => 0 // do not fail verbosely fi the http_code is an error, this is for security
                           , CURLOPT_SSL_VERIFYPEER => 1 // do verify the SSL of CURLOPT_URL, this is for security
                           , CURLOPT_VERBOSE => 0 // don't output verbosely to stderr, this is for security
                     ) );
    $response = curl_exec($curl);
    //$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl); 
$array_dataq= json_decode($response,1);
        $cache = fopen("access_token.txt", "wb");
        fwrite($cache, $array_dataq['access_token']);
        fclose($cache);
return $array_dataq['access_token'];
}



$IMAGE_FILES= grab_image($_GET['image_url']);

if($IMAGE_FILES == "error"){
exit("IMAGE NOT VALID");
}

$THE_TOKEN= GetPicasaToken();
$userId = $_GET['user_id'];
$albumId= $_GET['album_id'];


$RESULT_UPLOAD= upload_photo($userId, $albumId, $THE_TOKEN, $IMAGE_FILES);

preg_match_all("~<content type='image/jpeg' src='\K.*(?=')~Uis", $RESULT_UPLOAD, $testarr);

$IMAGE_RESULT= preg_replace('/https?:\/\/lh(\d+).googleusercontent.com\//i', 'https://1.bp.blogspot.com/', $testarr[0][0]);

$FULL_BLOGGER_IMG= preg_replace('/(https?:\/\/(.*)\/)/i', '\\1s1600/', $IMAGE_RESULT);
@unlink($IMAGE_FILES);

echo $FULL_BLOGGER_IMG;
