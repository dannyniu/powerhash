# PowerHash

PowerHash is a stateless anti-crawler plugin based on a Proof-of-Work 
algorithm. It's designed to deter most dumb crawlers that aren't 
programmed to execute JavaScript codes in web pages, as well as 
making it harder for smart crawlers to access the real contents of 
your websites.

# Advantages

- **Stateless**. This means PowerHash require nothing but space to store its 
codes, and it does not make use of any database.

- **Lightweight**. PowerHash has very small execution footprint on the server.

- **Compatibile**. The HTTP `GET` requests are best protected by PowerHash. 
All that site developer have to do is the include the "powerhash-main.php" 
at the very beginning of your web pages. That's just for textual assets, and 
if you needs to serve media assets such as images or videos, you can use the
PHP `readfile` function or the `byteserve` function from 

> https://github.com/dannyniu/byte-serving-php

The byte-serving-php repository was created by R.V.Florian, some improvements
were added by DannyNiu. Other forks may have other improvements.

# Disadvantages

- **Irrevocable Tokens**. Because PowerHash is stateless, it cannot keep track 
of exposed tokens or ones that're being abused. Rate-limit must also be applied 
to your website in order to contain their damage.

- **Non-Idempotent Requests Unsupported**. Non-idempotent request needs to be
changed in order to enjoy protection by PowerHash. An example of protection of
the HTTP `POST` request is given in this package. Other request methods need
specialized handling.
