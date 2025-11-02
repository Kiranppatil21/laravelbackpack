#!/usr/bin/env node
// Example Node script to sign and POST a visitor checkin using HMAC secret
const crypto = require('crypto');
const fetch = require('node-fetch');

function sign(payloadStr, secret, timestamp = Math.floor(Date.now() / 1000).toString()) {
  const msg = `${timestamp}|${payloadStr}`;
  const sig = crypto.createHmac('sha256', secret).update(msg).digest('hex');
  return { timestamp, signature: sig };
}

async function main() {
  const secret = process.env.VISITOR_HMAC_SECRET;
  if (!secret) {
    console.error('Set VISITOR_HMAC_SECRET in env to run this script');
    process.exit(1);
  }

  const payload = JSON.stringify({ name: 'Node Device', email: 'node@example.test' });
  const { timestamp, signature } = sign(payload, secret);

  const res = await fetch(process.env.VISITOR_ENDPOINT || 'http://127.0.0.1:8000/api/visitors/checkin', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-VISITOR-TIMESTAMP': timestamp,
      'X-VISITOR-SIGNATURE': signature,
    },
    body: payload,
  });

  console.log('status', res.status);
  console.log(await res.text());
}

main().catch((err) => { console.error(err); process.exit(1); });
