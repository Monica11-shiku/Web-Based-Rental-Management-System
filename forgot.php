<?php require 'config.php';$m='';
if($_POST){if(!hash_equals($_SESSION['c']??'',$_POST['c']??''))$m='Please try again';
 else{$i=trim($_POST['id']);$r=q('SELECT user_id,role,full_name FROM users WHERE (email=? OR phone=?) AND active=1',[$i,$i])->fetch();
  if($r&&$r['role']!='landlord'&&!q("SELECT 1 FROM password_resets WHERE user_id=? AND status IN ('pending','approved') AND requested_at>DATE_SUB(NOW(),INTERVAL 1 DAY)",[$r['user_id']])->fetch()){
   q("INSERT INTO password_resets(user_id) VALUES(?)",[$r['user_id']]);
   q("INSERT INTO notifications(user_id,message,type,date_sent) SELECT landlord_id,?,'reset',NOW() FROM users WHERE user_id=?",['Password reset requested by '.$r['full_name'].'. Approve it on the People page.',$r['user_id']]);}
  $m='ok';}}
head('Forgot password');
if($m=='ok')echo "<p class=ok>Your request has been recorded. Ask your landlord or caretaker to approve it and give you a reset code. Landlords should contact the system administrator.</p><p><a href=resetpw.php>I have a code</a></p>";
else echo "<p class=err>".e($m)."</p><form method=post>".csrf()."Email or phone<input name=id required><button>Request password reset</button></form><p><a href=resetpw.php>I already have a code</a></p>";
