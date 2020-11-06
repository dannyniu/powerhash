function buffer2str(ab)
{
  var ptr = new Uint8Array(ab);
  var ret = "";

  for(var i=0; i<ab.byteLength; i++)
    ret += String.fromCharCode(ptr[i]);

  return ret;
}

function str2bytes(s)
{
  function* utf8cu(s)
  {
    var i=0;

    while( i < s.length )
    {
      c = s.codePointAt(i);

      if( c < 0x80 )
      {
        yield c;
      }

      else if( c < 0x800 )
      {
        yield 0xc0 | (31 & (c >> 6));
        yield 0x80 | (63 & (c     ));
      }

      else if( c < 0x10000 )
      {
        yield 0xe0 | (15 & (c >> 12));
        yield 0x80 | (63 & (c >>  6));
        yield 0x80 | (63 & (c      ));
      }

      else if( c < 0x110000 )
      {
        yield 0xf0 | ( 3 & (c >> 18));
        yield 0x80 | (63 & (c >> 12));
        yield 0x80 | (63 & (c >>  6));
        yield 0x80 | (63 & (c      ));
      }

      i += i >= 0x10000 ? 2 : 1;
    }
  }
  return new Uint8Array(utf8cu(s));
}

async function PowerHash_POW(msg, bits, callback, cbctx)
{
  function btrunc(ba, bits)
  {
    var ret = "";

    for(var i=0; i<bits; i+=8)
    {
      var b = ba[i >> 3];
      var s = Math.min(bits - i, 8);

      b &= ~0 << (8 - s);
      ret +=
        Number(15 & (b >> 4)).toString(16) +
        Number(15 & (b     )).toString(16);
    }

    return ret;
  }

  function* nextkeys()
  {
    var a = [ 0, 0, 0, ]

    for(;;)
    {
      yield (new Uint16Array(a)).buffer;

      var i=0;
      for(; i<a.length; i++)
      {
        a[i]++;
        a[i] &= 0xffff;
        if( a[i] ) break;
      }
      if( i == a.length ) break;
    }

    return;
  }

  var ht = {}; // hash table.

  for( let rawkey of nextkeys() )
  {
    let algo = { "name": "HMAC", "hash": "SHA-256", };
    let key = await crypto.subtle.importKey(
      "raw", rawkey, algo, false, ["sign"]);

    let mac = crypto.subtle.sign(algo, key, msg.buffer);
    let tag = btrunc(new Uint8Array(await mac), bits);

    if( ht[tag] )
    {
      return callback.call(cbctx, rawkey, ht[tag]);
    }
    else ht[tag] = rawkey;
  }
}
