# Unsplash API Integration Setup Guide

## Step 1: Get Your Access Key

1. Go to [Unsplash Developer Dashboard](https://unsplash.com/oauth/applications)
2. Click on your **ETWEU** application
3. Navigate to the **"Keys"** section (in the left sidebar)
4. Under **"Access Key"**, you'll see a long string starting with `rjk_`
5. **Copy the entire Access Key** (click the copy button)

## Step 2: Add the Key to the Proxy

Open the file: `/api/unsplash-proxy.php`

Find this line:
```php
const UNSPLASH_ACCESS_KEY = 'YOUR_ACCESS_KEY_HERE';
```

Replace it with your actual key:
```php
const UNSPLASH_ACCESS_KEY = 'rjk_d_[your_full_key_here]';
```

**Example:**
```php
const UNSPLASH_ACCESS_KEY = 'rjk_d_YrMddJoUlH3rcDJoaB3C7OyumqTrqNlSvqpvA';
```

## Step 3: Verify It Works

1. Open: `http://localhost/language-platform/api/debug-unsplash.php`
2. If it shows:
   - **HTTP Status: 200** ✓ Success!
   - With photo results ✓ API is working!
3. If still showing **401** → Double-check your access key is complete and correct

## Step 4: Update Lessons with Proxy URLs

Once verified, the image URLs in your lessons will change from:
```
https://source.unsplash.com/400x300/?contract,business,agreement
```

To:
```
http://localhost/language-platform/api/unsplash-proxy.php?action=fetch&q=contract,business,agreement&w=400&h=300&redirect=1
```

## Example Usage

### Direct API Test
```
/api/unsplash-proxy.php?action=fetch&q=contract&redirect=1
```

### Health Check
```
/api/unsplash-proxy.php?action=health
```

### Custom Dimensions
```
/api/unsplash-proxy.php?action=fetch&q=handshake&w=600&h=400&redirect=1
```

## Troubleshooting

| Error | Solution |
|-------|----------|
| **401 Invalid Token** | Check that you copied the full Access Key from the dashboard |
| **400 Bad Request** | Verify the search query is properly URL-encoded |
| **Rate limit exceeded** | Wait 1 hour (5050 requests/hour limit) |
| **No results found** | Try a different search query |

## Notes

- This proxy works on **localhost** and **production**
- It bypasses CORS issues by handling requests server-side
- Images are cached for 1 hour to reduce API calls
- The access key is safe in PHP (server-side only)
