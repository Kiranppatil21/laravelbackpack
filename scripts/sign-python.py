#!/usr/bin/env python3
"""
Example Python script to sign payload with HMAC and POST to visitor checkin endpoint.
"""
import os
import time
import hmac
import hashlib
import json
import requests

def sign(payload_str, secret, timestamp=None):
    ts = timestamp or str(int(time.time()))
    msg = f"{ts}|{payload_str}".encode('utf-8')
    sig = hmac.new(secret.encode('utf-8'), msg, hashlib.sha256).hexdigest()
    return ts, sig

def main():
    secret = os.environ.get('VISITOR_HMAC_SECRET')
    if not secret:
        print('Set VISITOR_HMAC_SECRET environment variable')
        return 1

    payload = json.dumps({ 'name': 'Python Device', 'email': 'py@example.test' })
    ts, sig = sign(payload, secret)

    url = os.environ.get('VISITOR_ENDPOINT', 'http://127.0.0.1:8000/api/visitors/checkin')
    headers = {
        'Content-Type': 'application/json',
        'X-VISITOR-TIMESTAMP': ts,
        'X-VISITOR-SIGNATURE': sig,
    }

    r = requests.post(url, data=payload, headers=headers)
    print(r.status_code)
    print(r.text)
    return 0

if __name__ == '__main__':
    raise SystemExit(main())
