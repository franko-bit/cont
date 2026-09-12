import json
import os
import urllib.error
import urllib.request
from pathlib import Path

# load .env
env_path = Path('.env')
if env_path.exists():
    for raw_line in env_path.read_text(encoding='utf-8').splitlines():
        line = raw_line.strip()
        if not line or line.startswith('#') or '=' not in line:
            continue
        key, value = line.split('=', 1)
        os.environ[key] = value.strip().strip('"').strip("'")

api_key = os.environ.get('GEMINI_API_KEY', '')
print('API_KEY_PRESENT', bool(api_key))
url = f"https://generativelanguage.googleapis.com/v1beta/models?key={api_key}"
print('LIST_MODELS_URL', url)

req = urllib.request.Request(url, headers={'Content-Type': 'application/json'}, method='GET')
try:
    with urllib.request.urlopen(req, timeout=30) as resp:
        payload = json.loads(resp.read().decode('utf-8', errors='replace'))
        print('STATUS', resp.status)
        print(json.dumps(payload, indent=2)[:12000])
except Exception as e:
    print(type(e).__name__, e)
    if isinstance(e, urllib.error.HTTPError):
        body = e.read().decode('utf-8', errors='replace')
        print('BODY', body)
