<?php
 // wrapped in a class so as to avoid polluting global namespace too much.
 class PowerHash
 {
   static $privkey = "--!!Generate Your Own Key When Deploying!!--";
   static function setprivkey($s){ self::$privkey = $s; }

   static $hashalgo = "sha256";
   static $accept = 60; // by default POW must be completed in 1 minute.
   static $recept = 100; // by default "POST" is allowed some extra time.
   static $window = 300; // by default cookie is renewed every 5 minutes.
   static $duration = 3600; // by default 1 POW is valid for one hour.

   static function workfactor()
   {
     // Site developers may customize this value, for example:
     // increasing or decreasing it
     // across the board, or
     // set it differently for
     // different request methods and
     // different path and/or query parameters.
     return 32;
   }

   static function btrunc($s, $bits)
   {
     $len = $bits >> 3;
     $ret = substr($s, 0, $len);
     $t = $bits & 7;
     if( $t ) $ret .= chr( ord($s[$len]) & (~0 << (8 - $t)) );
     return $ret;
   }

   static function resp_success($debug = null)
   {
     return true;
   }

   static function resp_failure_reinitiate($debug = null)
   {
     include("skeleton-reinitiate.php");
     exit();
     return false;
   }

   static function resp_failure_post_reject($debug = null)
   {
     include("skeleton-post-rejected.php");
     exit();
     return false;
   }

   static function resp_renew($newcookie)
   {
     include("skeleton-renew.php");
     exit();
     return false;
   }

   static function resp_powerhash_js($debug = null)
   {
     header("Content-Type: application/javascript");
     include("powerhash.js");
     exit();
     return false;
   }

   static function reqverify_get()
   {
     $cookie = $_COOKIE["powerhash"] ?? "";
     $args = explode(":", $cookie);
     $ua = trim($_SERVER["HTTP_USER_AGENT"] ?? "\xff");

     switch( $args[0] )
     {
       case "pow":
       $time = intval($args[1]);
       $delta = time() - $time;
       if( $delta < 0 || $delta > self::$accept )
         return self::resp_failure_reinitiate("pow:1");

       $k1 = base64_decode($args[2] ?? "");
       $k2 = base64_decode($args[3] ?? "");
       if( !is_string($k1) || !is_string($k2) || !strcmp($k1, $k2) )
         return self::resp_failure_reinitiate("pow:2");

       $msg = "$time:$ua";
       $t1 = hash_hmac(self::$hashalgo, $msg, $k1, true);
       $t2 = hash_hmac(self::$hashalgo, $msg, $k2, true);
       $t1 = self::btrunc($t1, self::workfactor());
       $t2 = self::btrunc($t2, self::workfactor());
       if( $t1 !== $t2 )
         return self::resp_failure_reinitiate("pow:3");

       $time = strval(time());
       $now = $time;
       $msg = "$time:$now:$ua";
       $tag = hash_hmac(self::$hashalgo, $msg, self::$privkey, true);
       $tag = base64_encode($tag);
       setcookie("powerhash", "sess:$time:$now:$tag", ["secure" => true]);
       return self::resp_success("pow");
       break;


       case "sess":
       $time = intval($args[1]);
       $now = intval($args[2]);
       $new = time();
       $delta = $new - $time;
       if( $delta < 0 || $delta > self::$duration )
         return self::resp_failure_reinitiate("sess:1");

       $msg = "$time:$now:$ua";
       $mac = hash_hmac(self::$hashalgo, $msg, self::$privkey, true);
       $tag = base64_decode($args[3]);
       if( $mac !== $tag )
         return self::resp_failure_reinitiate("sess:2");

       $delta = $new - $now;
       if( $delta > self::$window )
       {
         $now = strval(time());
         $msg = "$time:$now:$ua";
         $tag = hash_hmac(self::$hashalgo, $msg, self::$privkey, true);
         $tag = base64_encode($tag);
         return self::resp_renew("sess:$time:$now:$tag");
       }
       return self::resp_success("sess");
       break;


       default:
       return self::resp_failure_reinitiate("default");
       break;
     }
   }

   static function reqverify_post()
   {
     $cookie = $_COOKIE["powerhash-post"] ?? "";
     $args = explode(":", $cookie);
     $ua = trim($_SERVER["HTTP_USER_AGENT"] ?? "\xff");

     // This does nothing.
     // It just synchronizes positional arguments with ``reqverify_get''.
     $cmd = $args[0];
     if( $cmd !== "pow-post" );

     $time = $args[1];
     $delta = time() - $time;
     if( $delta < 0 || $delta > self::$recept )
       return self::resp_failure_post_reject("post:1");

     $k1 = base64_decode($args[2] ?? "");
     $k2 = base64_decode($args[3] ?? "");
     if( !is_string($k1) || !is_string($k2) || !strcmp($k1, $k2) )
       return self::resp_failure_post_reject("post:2");

     $bodysum = hash_file(self::$hashalgo, "php://input", true);
     $bodysum = base64_encode($bodysum);

     $msg = "$time:$bodysum:$ua";
     $t1 = hash_hmac(self::$hashalgo, $msg, $k1, true);
     $t2 = hash_hmac(self::$hashalgo, $msg, $k2, true);
     $t1 = self::btrunc($t1, self::workfactor());
     $t2 = self::btrunc($t2, self::workfactor());
     if( $t1 !== $t2 )
       return self::resp_failure_post_reject("post:3");

     return self::resp_success("post");
   }

   static function main()
   {
     switch( $_SERVER["REQUEST_METHOD"] )
     {
       case "GET": case "HEAD":
       self::reqverify_get();
       break;

       case "POST":
       self::reqverify_post();
       break;

       default:
       http_response_code(400);
       die("Unsupported HTTP Method!");
     }
   }
 }

 PowerHash::main();
