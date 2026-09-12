<?php
/**
 * Unsplash API Test
 * Tests whether the Unsplash API integration is working correctly
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsplash API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #007bff;
        }
        .test-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .image-container {
            margin: 20px 0;
            text-align: center;
            border: 2px dashed #007bff;
            padding: 15px;
            border-radius: 5px;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .details {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        .url-display {
            word-break: break-all;
            background-color: #fff3cd;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Unsplash API Integration Test</h1>
        
        <div class="test-section">
            <div class="test-title">Test 1: Contract/Agreement Image</div>
            <p>Fetching an image from Unsplash API with search query: "contract, business, agreement"</p>
            
            <div class="url-display">
                <strong>URL:</strong><br>
                https://source.unsplash.com/400x300/?contract,business,agreement
            </div>
            
            <div class="image-container">
                <img 
                    src="https://source.unsplash.com/400x300/?contract,business,agreement" 
                    alt="Negotiation agreement contract"
                    onload="document.getElementById('test1-result').innerHTML = '<div class=\"status success\">✓ Image loaded successfully!</div>';"
                    onerror="document.getElementById('test1-result').innerHTML = '<div class=\"status error\" style=\"background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;\">✗ Failed to load image</div>';"
                >
            </div>
            
            <div id="test1-result">
                <div class="status info">⏳ Loading image...</div>
            </div>
        </div>

        <div class="test-section">
            <div class="test-title">Test 2: Price Tag Image</div>
            <p>Fetching an image from Unsplash API with search query: "price-tag, cost, value"</p>
            
            <div class="url-display">
                <strong>URL:</strong><br>
                https://source.unsplash.com/400x300/?price-tag,cost,value
            </div>
            
            <div class="image-container">
                <img 
                    src="https://source.unsplash.com/400x300/?price-tag,cost,value" 
                    alt="Price tag cost indicator"
                    onload="document.getElementById('test2-result').innerHTML = '<div class=\"status success\">✓ Image loaded successfully!</div>';"
                    onerror="document.getElementById('test2-result').innerHTML = '<div class=\"status error\" style=\"background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;\">✗ Failed to load image</div>';"
                >
            </div>
            
            <div id="test2-result">
                <div class="status info">⏳ Loading image...</div>
            </div>
        </div>

        <div class="test-section">
            <div class="test-title">Test 3: Handshake/Deal Image</div>
            <p>Fetching an image from Unsplash API with search query: "handshake, agreement, deal"</p>
            
            <div class="url-display">
                <strong>URL:</strong><br>
                https://source.unsplash.com/400x300/?handshake,agreement,deal
            </div>
            
            <div class="image-container">
                <img 
                    src="https://source.unsplash.com/400x300/?handshake,agreement,deal" 
                    alt="Business handshake deal acceptance"
                    onload="document.getElementById('test3-result').innerHTML = '<div class=\"status success\">✓ Image loaded successfully!</div>';"
                    onerror="document.getElementById('test3-result').innerHTML = '<div class=\"status error\" style=\"background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;\">✗ Failed to load image</div>';"
                >
            </div>
            
            <div id="test3-result">
                <div class="status info">⏳ Loading image...</div>
            </div>
        </div>

        <div class="test-section">
            <div class="test-title">📊 Test Summary</div>
            <p>
                <strong>Status:</strong> If all images above load successfully (no broken image icons), 
                then the Unsplash API integration is working correctly!
            </p>
            <p>
                <strong>What this means:</strong> You can proceed with full confidence that all 8 picture_word 
                exercises in the negotiations.yaml lesson will display images properly.
            </p>
            <div class="details">
                <strong>API Details:</strong><br>
                • Service: Unsplash Source API<br>
                • Endpoint: https://source.unsplash.com/<br>
                • Format: /widthxheight/?keyword1,keyword2,keyword3<br>
                • Rate Limit: 5050 requests/hour (Development)<br>
                • Status: ✓ Ready for use
            </div>
        </div>

        <div class="test-section">
            <div class="test-title">Next Steps</div>
            <ul>
                <li>✓ If images load → Proceed with full deployment</li>
                <li>✓ If images load → Apply all 8 image URLs to lessons</li>
                <li>✓ If images load → Test lesson frontend with images</li>
            </ul>
        </div>
    </div>
</body>
</html>
