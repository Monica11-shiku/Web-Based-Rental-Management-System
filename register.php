<?php require 'config.php';$m='';
if($_POST){$f=array_map('trim',$_POST);$er=[];
 foreach(['full_name','national_id','phone','email','pw'] as $k)if(!($f[$k]??''))$er[]="$k required";
 if(!preg_match('/^\d{6,10}$/',$f['national_id']??''))$er[]='ID must be digits';
 if(!filter_var($f['email']??'',FILTER_VALIDATE_EMAIL))$er[]='Invalid email';
 if(!preg_match('/^(\+?254|0)[17]\d{8}$/',$f['phone']??''))$er[]='Invalid Kenyan phone';
 if(strlen($f['pw']??'')<8||!preg_match('/[A-Za-z]/',$f['pw'])||!preg_match('/\d/',$f['pw'])||$f['pw']!==$f['pw2'])$er[]='Password: 8+ chars, letters and numbers, must match';
 if(!$er&&q('SELECT 1 FROM users WHERE national_id=? OR phone=? OR email=?',[$f['national_id'],$f['phone'],$f['email']])->fetch())$er[]='An account with these details already exists';
 if(!$er){q("INSERT INTO users(full_name,national_id,phone,email,password_hash,role) VALUES(?,?,?,?,?,'landlord')",[$f['full_name'],$f['national_id'],$f['phone'],$f['email'],password_hash($f['pw'],PASSWORD_DEFAULT)]);
  q('UPDATE users SET landlord_id=user_id WHERE user_id=?',[$pdo->lastInsertId()]);header('Location: login.php');exit;}
 $m=implode('<br>',array_map('e',$er));}
head('Create landlord account');?>
<p class=err><?=$m?></p><form method=post>Full name<input name=full_name>National ID<input name=national_id>Phone<input name=phone>Email<input name=email>Password<input name=pw type=password>Confirm<input name=pw2 type=password><button>Register</button></form>
