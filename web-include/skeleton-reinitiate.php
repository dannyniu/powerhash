<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <meta name=viewport content="width=device-width, height=device-height"/>
    <meta name=color-scheme content="light dark"/>
    <title>Protected by PowerHash</title>

    <!-- Remember to change these references to source code includes -->
    <script>
     var workfactor = <?= PowerHash::workfactor(); ?>;

     <?php include "powerhash.js"; ?>

     async function PowerHash_InitiateSession()
     {
       function POW_OnComplete(k1, k2)
       {
         document.cookie =
           "powerhash=pow:" +
           this.time + ":" +
           btoa(buffer2str(k1)) + ":" +
           btoa(buffer2str(k2)) + "; Secure";
         location.reload();
       }

       var ctx = {};
       ctx.time = String(Math.floor(Date.now() / 1000));
       ctx.ua = String(navigator.userAgent).trim();
       ctx.msg = str2bytes(ctx.time + ":" + ctx.ua);

       PowerHash_POW(ctx.msg, workfactor, POW_OnComplete, ctx);
     }

     window.onload = PowerHash_InitiateSession;
    </script>

    <style>
     body {
       position:         fixed;
       left:             0;
       top:              0;
       right:            0;
       bottom:           0;

       display:          flex;
       flex-flow:        column;
       justify-content:  center;
       align-items:      center;

       font-family:      ui-sans-serif, sans-serif;
     }

     div.sign {
       text-align:       center;
       font-size:        2em;
       font-size:        calc(2vw + 2vh);
     }

     div.note {
       text-align:       justify;
       width:            360px;
       width:            57vw;
     }
    </style>

  </head>
  <body>

    <div class=sign>
      <p>
        Verifying your browsers. <br/>
        You'll enter the website shortly. <br/>
      </p>
    </div>

    <div class=note id=note>
      <p>
        This website is protected from crawlers by PowerHash
        using JavaScript and Cookies. It requires some of the
        latest web technologies and may not be compatible with
        older browsers. Microsoft Internet Explorer in particular
        is not supported.
      </p>

      <p>
        If you haven't been able to enter the website after a while,
        please check if JavaScript and Cookies are enabled, and
        check for browser updates.
      </p>
    </div>

  </body>
</html>
