<?php
function mp(){static $c;return $c??=require __DIR__.'/mpesa_config.php';}
function mp_call($url,$body=null,$hdr=[],$auth=null){$ch=curl_init($url);
 curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>$hdr,CURLOPT_SSL_VERIFYPEER=>mp()['verify_ssl']]);
 if($auth)curl_setopt($ch,CURLOPT_USERPWD,$auth);
 if($body!==null){curl_setopt($ch,CURLOPT_POST,1);curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));}
 $r=curl_exec($ch);if($r===false)error_log('mpesa curl: '.curl_error($ch));curl_close($ch);return json_decode((string)$r,true);}
function mp_stk($phone,$amount,$ref){$c=mp();
 $t=mp_call($c['base'].'/oauth/v1/generate?grant_type=client_credentials',null,[],$c['key'].':'.$c['secret'])['access_token']??null;
 if(!$t)return null;$ts=date('YmdHis');
 return mp_call($c['base'].'/mpesa/stkpush/v1/processrequest',['BusinessShortCode'=>$c['shortcode'],'Password'=>base64_encode($c['shortcode'].$c['passkey'].$ts),'Timestamp'=>$ts,'TransactionType'=>'CustomerPayBillOnline','Amount'=>(int)$amount,'PartyA'=>$phone,'PartyB'=>$c['shortcode'],'PhoneNumber'=>$phone,'CallBackURL'=>$c['callback'],'AccountReference'=>$ref,'TransactionDesc'=>'Rent'],['Content-Type: application/json','Authorization: Bearer '.$t]);}
