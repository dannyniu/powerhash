# Setup

To setup PowerHash on your server:

1. copy the directory "web-include" to somewhere that can be included in 
your PHP scripts.

2. Generate a 128-bit (or 192-, 256- bit at your preference) secret key
in base64 or hexadecimal format using operating system entropy sources 
such as `/dev/random` or `/dev/urandom` or command line: 

```sh
# change the brackets and braces appropriately.
dd if=/dev/[u]random bs={16,24,32} count=1 | openssl enc -a
# or
openssl rand {-base64,-hex} {16,24,32}
```

2.1. Copy the result into "web-include/powerhash-main.php" as a string and 
assign it to the static class variable `$privkey`.

2.2. Alternatively, you can change the line to use `getenv` and configure
the key from server configuration.

- Done.
