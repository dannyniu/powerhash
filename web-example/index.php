<?php
 require_once("../web-include/powerhash-main.php");

 // ... Other application-specific codes.

 if( $_SERVER["REQUEST_METHOD"] === "POST" )
 {
   $ret = "-";
   $a = floatval($_POST["a"]);
   $b = floatval($_POST["b"]);
   switch( $_POST["operation"] )
   {
     case "add": $ret = strval($a + $b); break;
     case "sub": $ret = strval($a - $b); break;
     case "mul": $ret = strval($a * $b); break;
     case "div": $ret = strval($a / $b); break;
   }
 }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <meta name=viewport content="width=device-width, height=device-height"/>
    <meta name=color-scheme content="light dark"/>
    <title>PowerHash Example Page</title>

    <script src="./powerhash.js.php"></script>
    <script>
     var workfactor = <?= PowerHash::workfactor(); ?>;

     async function submit4calc()
     {

       var form = document.getElementById("form-arithmetic");
       var srch = new URLSearchParams();
       var keys = String("operation a b").split(" ");
       var dat = "";
       var ctx = {};
       var xhr = new XMLHttpRequest();

       function POW_OnComplete(k1, k2)
       {
         document.cookie =
           "powerhash-post=pow-post:" +
           this.time + ":" +
           btoa(buffer2str(k1)) + ":" +
           btoa(buffer2str(k2)) + "; Secure";

         xhr.open("POST", window.location.toString());
         xhr.onload = function()
         {
           document.open();
           document.write(xhr.responseText);
         }
         xhr.setRequestHeader(
           "Content-Type",
           "application/x-www-form-urlencoded");
         xhr.send(dat);
       }

       keys.forEach(function(k){ srch.append(k, form.elements[k].value); });
       dat = srch.toString();
       ctx.time = String(Math.floor(Date.now() / 1000));
       ctx.ua = String(navigator.userAgent).trim();
       ctx.digest = await crypto.subtle.digest("SHA-256", str2bytes(dat));
       ctx.bodysum = btoa(buffer2str(ctx.digest))
       ctx.msg = str2bytes(ctx.time + ":" + ctx.bodysum + ":" + ctx.ua);

       PowerHash_POW(ctx.msg, workfactor, POW_OnComplete, ctx);
     }
    </script>
  </head>
  <body>

    <form id=form-arithmetic action="javascript:submit4calc();">
      <label>
        Operation:
        <select name=operation>
          <option value=add>addition</option>
          <option value=sub>subtraction</option>
          <option value=mul>multiplication</option>
          <option value=div>division</option>
        </select>
      </label>
      <label>
        <input type=number name=a value=1 />
      </label>
      <label>
        <input type=number name=b value=1 />
      </label>
      <button type=submit>Calculate</button>
    </form>

    <?php
     if( $_SERVER["REQUEST_METHOD"] === "POST" )
       echo "<p>".htmlspecialchars($ret)."</p>\n";
    ?>

  </body>

</html>
