import json
import urllib.error
import urllib.request

import language_pair_translation_map as m

b = m.GeminiBackend()
print('ENDPOINT', b.endpoint)
print('MODEL', b.model)
print('API_KEY_PRESENT', bool(b.api_key))

clean_model = b.model.replace('models/', '').replace('model/', '')
url = f"{b.endpoint.rstrip('/')}/models/{clean_model}:generateContent?key={b.api_key}"
print('URL', url)

body = json.dumps({
    'contents': [{'parts': [{'text': 'hello'}]}]
}).encode('utf-8')

req = urllib.request.Request(
    url,
    data=body,
    headers={'Content-Type': 'application/json'},
    method='POST',
)

try:
    with urllib.request.urlopen(req, timeout=30) as resp:
        print('STATUS', resp.status)
        print(resp.read().decode('utf-8', errors='replace'))
except Exception as e:
    print(type(e).__name__, e)
    if isinstance(e, urllib.error.HTTPError):
        print('BODY', e.read().decode('utf-8', errors='replace'))
