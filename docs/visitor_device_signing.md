# Visitor Device Signing (HMAC)

This document shows how to sign HTTP requests from kiosks / IoT devices so the server can verify they are authentic.

Protocol summary
- The device sends two headers along with the JSON body:
  - `X-VISITOR-TIMESTAMP` — UNIX timestamp (seconds) when the request was created
  - `X-VISITOR-SIGNATURE` — HMAC-SHA256 hex string of `timestamp + '|' + raw_body` using `VISITOR_HMAC_SECRET`

Examples

Node.js (example)
```js
const crypto = require('crypto');

function sign(payloadStr, secret, timestamp = Math.floor(Date.now() / 1000).toString()) {
  const msg = `${timestamp}|${payloadStr}`;
  const sig = crypto.createHmac('sha256', secret).update(msg).digest('hex');
  return { timestamp, signature: sig };
}

// usage
const payload = JSON.stringify({ name: 'Device', email: 'd@example.com' });
const secret = process.env.VISITOR_HMAC_SECRET;
const { timestamp, signature } = sign(payload, secret);

fetch('https://example.com/api/visitors/checkin', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-VISITOR-TIMESTAMP': timestamp,
    'X-VISITOR-SIGNATURE': signature,
  },
  body: payload,
});
```

Python (example)
```py
import time, hmac, hashlib, requests, json, os

def sign(payload_str, secret, timestamp=None):
    ts = timestamp or str(int(time.time()))
    msg = f"{ts}|{payload_str}".encode('utf-8')
    sig = hmac.new(secret.encode('utf-8'), msg, hashlib.sha256).hexdigest()
    return ts, sig

payload = json.dumps({ 'name': 'Device', 'email': 'd@example.com' })
secret = os.environ.get('VISITOR_HMAC_SECRET')
ts, sig = sign(payload, secret)

resp = requests.post('https://example.com/api/visitors/checkin', data=payload, headers={
    'Content-Type': 'application/json',
    'X-VISITOR-TIMESTAMP': ts,
    'X-VISITOR-SIGNATURE': sig,
})

print(resp.status_code, resp.text)
```

cURL example (manual signing via openssl)
```sh
TS=$(date +%s)
PAYLOAD='{"name":"Device","email":"d@example.com"}'
MSG="$TS|$PAYLOAD"
SECRET='your_secret_here'
SIG=$(printf "%s" "$MSG" | openssl dgst -sha256 -hmac "$SECRET" -hex | sed 's/^.* //')

curl -X POST https://example.com/api/visitors/checkin \
  -H 'Content-Type: application/json' \
  -H "X-VISITOR-TIMESTAMP: $TS" \
  -H "X-VISITOR-SIGNATURE: $SIG" \
  -d "$PAYLOAD"
```

PHP example (using helper inside this project)
```php
use App\Services\VisitorSignature;

$payload = json_encode(['name' => 'Device', 'email' => 'd@example.com']);
$secret = getenv('VISITOR_HMAC_SECRET');
$s = VisitorSignature::sign($payload, $secret);

$headers = [
  'Content-Type: application/json',
  "X-VISITOR-TIMESTAMP: {$s['timestamp']}",
  "X-VISITOR-SIGNATURE: {$s['signature']}",
];

// use curl or Guzzle to send request
```

Server note
- The app middleware supports both simple API key (`X-VISITOR-API-KEY`) and HMAC signing. If neither secret is configured the middleware is a no-op for local development. In production you should configure at least one and add the secret to your repository secrets.
