<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Protected by PowerHash</title>

    <!-- Remember to change these references to source code includes -->
    <script>
     var newcookie = <?= $newcookie; ?>;

     function PowerHash_Renew()
     {
       document.cookie = "powerhash="+newcookie;
     }

     window.onload = PowerHash_Renew;
    </script>
  </head>
  <!-- 2020-11-01 TODO: Add UI. -->
