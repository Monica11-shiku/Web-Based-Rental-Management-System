<?php require 'config.php';$u=need();$m='';
if($_POST){$r=q('SELECT password_hash FROM users WHERE user_id=?',[$u['id']])->fetch();$n=$_POST['new'];
 if(!password_verify($_POST['cur'],$r['password_hash']))$m='<span class=err>Current password is wrong</span>';
 elseif(strlen($n)<8||!preg_match('/[A-Za-z]/',$n)||!preg_match('/\d/',$n)||$n!==$_POST['new2'])$m='<span class=err>New password: 8+ characters, letters and numbers, and both entries must match</span>';
 else{q('UPDATE users SET password_hash=? WHERE user_id=?',[password_hash($n,PASSWORD_DEFAULT),$u['id']]);session_regenerate_id(true);$m='<span class=ok>Password changed</span>';}}
head('Change password');echo "<p>$m</p>";?>
<form method=post><?=csrf()?>Current password<input name=cur type=password required>New password<input name=new type=password required>Confirm new password<input name=new2 type=password required><button>Change password</button></form>
