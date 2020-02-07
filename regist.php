#!/usr/bin/php
<?php

// set environment
include(__DIR__.'/accounts.conf');
date_default_timezone_set(@date_default_timezone_get());
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// set certbot env
$CERTBOT_ENV['CERTBOT_DOMAIN']     = getenv('CERTBOT_DOMAIN');
$CERTBOT_ENV['CERTBOT_VALIDATION'] = getenv('CERTBOT_VALIDATION');

// txt record 
$CERTBOT_ENV['EDIT_CMD'] = 'REGIST';

// mydns account
$MYDNS_ACCOUNT=$MYDNS_ID[$CERTBOT_ENV['CERTBOT_DOMAIN']].':'.$MYDNS_PWD[$CERTBOT_ENV['CERTBOT_DOMAIN']];

$MYDNS_HEADERS = array('Content-Type: application/x-www-form-urlencoded',
                       'Authorization: Basic '. base64_encode($MYDNS_ACCOUNT),);


// コンテクストリソースを設定
$POST_OPTIONS = array( 'http' => array('method' => 'POST',
                                       'header' => implode("\r\n", $MYDNS_HEADERS),
                                       'content' => http_build_query($CERTBOT_ENV)));

// get contents
$MYDNS_CONTENTS = file_get_contents($MYDNS_URL, false, stream_context_create($POST_OPTIONS));

sleep(2);

?>
