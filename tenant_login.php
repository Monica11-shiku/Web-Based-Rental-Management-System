<?php require 'config.php';$m='';
if($_POST){
 if(!hash_equals($_SESSION['c']??'',$_POST['c']??''))$m='Please try again';
 else{$i=trim($_POST['id']);$r=q("SELECT * FROM users WHERE (email=? OR phone=?) AND active=1 AND role='tenant'",[$i,$i])->fetch();
  if($r&&$r['locked_until']&&strtotime($r['locked_until'])>time())$m='Account locked. Try again in 15 minutes.';
  elseif($r&&password_verify($_POST['pw'],$r['password_hash'])){q('UPDATE users SET failed_attempts=0,locked_until=NULL WHERE user_id=?',[$r['user_id']]);session_regenerate_id(true);
   $_SESSION['u']=['id'=>$r['user_id'],'role'=>$r['role'],'name'=>$r['full_name'],'landlord_id'=>$r['landlord_id']?:$r['user_id'],'t'=>time()];header('Location: dashboard.php');exit;}
  else{$m='Phone number or password is incorrect';if($r)q('UPDATE users SET failed_attempts=failed_attempts+1,locked_until=IF(failed_attempts>=5,DATE_ADD(NOW(),INTERVAL 15 MINUTE),NULL) WHERE user_id=?',[$r['user_id']]);}}}
$vid=is_file(__DIR__.'/media/login.mp4');$sl=$vid?[]:array_slice(glob(__DIR__.'/media/slides/*.{jpg,jpeg,png,webp}',GLOB_BRACE)?:[],0,6);$lq=$_GET;$lq['lang']=(($_SESSION['lang']??'en')=='sw')?'en':'sw';
?><!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><title>Tenant login</title><link href='https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap' rel=stylesheet>
<style>:root{--bg:#0b0b0c;--card:#161618;--tx:#f5f1e8;--mu:#a8a193;--bd:#2a2a2d;--g:#0b0b0c;--g2:#d9a441}

*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;grid-template-columns:1.1fr 1fr;font:14px/1.55 Montserrat,system-ui,Segoe UI,sans-serif;background:var(--bg);color:var(--tx)}
.hero{background:linear-gradient(145deg,#0b0b0c,#2b220f);color:#fff;padding:48px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden}
.hero:before,.hero:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.08)}.hero:before{width:380px;height:380px;right:-120px;top:-100px}.hero:after{width:240px;height:240px;left:-80px;bottom:-60px}
.hero>*{position:relative}.hero svg{width:100%;max-width:380px;margin-bottom:24px;animation:rise .8s ease both}
.hero h1{background:linear-gradient(90deg,#f3d58a,#d9a441);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;font-size:34px;line-height:1.2;margin:0 0 14px}.hero li{margin:8px 0;list-style:none}.hero li:before{content:"\2713 ";font-weight:700;color:#f3d58a}.hero ul{padding:0;margin:0}
.side{display:flex;align-items:center;justify-content:center;padding:32px}.box{width:100%;max-width:380px;background:var(--card);border:1px solid var(--bd);border-radius:16px;padding:30px;animation:rise .6s ease both}
h2{margin:0 0 4px}.mu{color:var(--mu);margin:0 0 18px}label{display:block;font-size:13px;color:var(--mu);margin-top:12px}
input{width:100%;padding:11px 12px;margin-top:4px;border:1px solid var(--bd);border-radius:10px;background:var(--bg);color:var(--tx);font:inherit}
button{width:100%;margin-top:18px;padding:12px;border:0;border-radius:10px;background:var(--g2);color:#111;font:inherit;font-weight:600;cursor:pointer}button:hover{filter:brightness(.92)}
.err{color:#dc2626;margin:0 0 8px}.links{margin-top:18px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;font-size:14px}a{color:var(--g2)}
.hero{background:linear-gradient(135deg,#0b0b0c,#3a2d12,#171614,#0b0b0c);background-size:300% 300%;animation:flow 14s ease infinite}
@keyframes flow{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}
.hero:before{animation:float 9s ease-in-out infinite}.hero:after{animation:float 11s ease-in-out infinite reverse}@keyframes float{50%{transform:translate(30px,-24px) scale(1.08)}}
.hero .cl{position:absolute;height:22px;border-radius:99px;background:rgba(255,255,255,.18);left:0;animation:drift linear infinite}@keyframes drift{from{transform:translateX(-160px)}to{transform:translateX(900px)}}
.hero rect.w{animation:tw 3s ease-in-out infinite}@keyframes tw{50%{opacity:.3}}
video.bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.hero .vo{position:absolute;inset:0;background:rgba(11,11,12,.6)}
@media(prefers-reduced-motion:reduce){*{animation:none!important}}
.hero .slides{position:absolute;inset:0;overflow:hidden}.slides i{position:absolute;inset:0;background-size:cover;background-position:center;opacity:0;animation-name:sl;animation-iteration-count:infinite;animation-timing-function:ease-in-out}.has svg{display:none}
@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(max-width:800px){video.bg,.hero .vo,.hero .slides{display:none}.has svg{display:block}body{grid-template-columns:1fr}.hero{padding:28px}.hero h1{font-size:26px}.hero svg{max-width:220px}.hero ul{display:none}}</style>
<div class="hero<?=($vid||$sl)?' has':''?>">
<?php if($sl){$n=count($sl);$p=100/$n;$e=min(99,$p+5);echo "<style>@keyframes sl{0%{opacity:0;transform:scale(1)}5%{opacity:1}{$p}%{opacity:1}{$e}%{opacity:0;transform:scale(1.08)}100%{opacity:0}}</style><div class=slides>";foreach($sl as $k=>$f)echo "<i style=\"background-image:url('media/slides/".rawurlencode(basename($f))."');animation-delay:".($k*6)."s;animation-duration:".($n*6)."s\"></i>";echo "</div><div class=vo></div>";}
if($vid)echo '<video class=bg autoplay muted loop playsinline><source src="media/login.mp4" type="video/mp4"></video><div class=vo></div>';?>
<div class=cl style="top:10%;width:130px;animation-duration:28s"></div><div class=cl style="top:24%;width:90px;animation-duration:19s;animation-delay:-8s"></div><div class=cl style="top:70%;width:150px;animation-duration:34s;animation-delay:-15s"></div>
<svg viewBox="0 0 320 210" aria-hidden=true><rect x=30 y=40 width=150 height=150 rx=8 fill=#fff /><rect x=170 y=90 width=120 height=100 rx=8 fill=#f5ecd6 />
<?php for($r=0;$r<3;$r++)for($c=0;$c<3;$c++)echo "<rect class=w style=animation-delay:".(($r*3+$c)*0.45)."s x=".(48+$c*44)." y=".(58+$r*38)." width=28 height=24 rx=4 fill=#d9a441 />";
for($c=0;$c<2;$c++)echo "<rect class=w style=animation-delay:".(1.2+$c*0.7)."s x=".(188+$c*50)." y=108 width=34 height=26 rx=4 fill=#d9a441 />";?>
<rect x=95 y=150 width=34 height=40 rx=4 fill=#0b0b0c /><rect x=0 y=190 width=320 height=6 rx=3 fill=#f3d58a /></svg>
<h1>Your home, your rent, in one place</h1><ul><li>See your balance and rent due<li>Pay rent with M-Pesa<li>Download receipts and report repairs</ul></div>
<div class=side><div class=box><h2>Tenant portal</h2><p class=mu>Sign in to see your rent, receipts and repairs</p>
<?php if($m)echo "<p class=err>".e($m)."</p>";?>
<form method=post><?=csrf()?><label>Email or phone</label><input name=id required autofocus><label>Password</label><input name=pw type=password required><button>Log In</button></form>
<div class=links><a href=forgot.php>Forgot password?</a><a href=login.php>Landlord or caretaker? Staff login</a></div>
<div class=links><a href="?<?=e(http_build_query($lq))?>"><?=$lq['lang']=='en'?'English':'Kiswahili'?></a></div></div></div>
