<?php require 'config.php';$m='';
if($_POST){if(!hash_equals($_SESSION['c']??'',$_POST['c']??''))$m='Please try again';
 else{$i=trim($_POST['id']);$code=trim($_POST['code']);$n=$_POST['new'];
  $u=q('SELECT user_id FROM users WHERE (email=? OR phone=?) AND active=1',[$i,$i])->fetch();
  $r=$u?q("SELECT * FROM password_resets WHERE user_id=? AND status='approved' AND expires_at>NOW() ORDER BY reset_id DESC LIMIT 1",[$u['user_id']])->fetch():false;
  if(!$r)$m='Invalid or expired code';
  elseif(!hash_equals($r['code_hash'],hash('sha256',$code))){q("UPDATE password_resets SET attempts=attempts+1,status=IF(attempts>=5,'expired',status) WHERE reset_id=?",[$r['reset_id']]);$m='Invalid or expired code';}
  elseif(strlen($n)<8||!preg_match('/[A-Za-z]/',$n)||!preg_match('/\d/',$n)||$n!==$_POST['new2'])$m='Password: 8+ characters, letters and numbers, and both entries must match';
  else{q('UPDATE users SET password_hash=?,failed_attempts=0,locked_until=NULL WHERE user_id=?',[password_hash($n,PASSWORD_DEFAULT),$u['user_id']]);q("UPDATE password_resets SET status='used' WHERE reset_id=?",[$r['reset_id']]);$m='done';}}}
head('Reset password');
if($m=='done')echo "<p class=ok>Password changed. You can now <a href=login.php>log in</a>.</p>";
else echo "<p class=err>".e($m)."</p><form method=post>".csrf()."Email or phone<input name=id required>Reset code<input name=code required>New password<input name=new type=password required>Confirm new password<input name=new2 type=password required><button>Set new password</button></form>";
