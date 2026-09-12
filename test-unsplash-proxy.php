<?php
/**
 * Unsplash API Proxy Test
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsplash API Proxy Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
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
            border-bottom: 3px solid #ff6b6b;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #ff6b6b;
            border-radius: 4px;
        }
        .test-title {
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 10px;
        }
        .image-container {
            margin: 15px 0;
            padding: 10px;
            border: 2px dashed #ff6b6b;
            border-radius: 5px;
            text-align: center;
            background-color: #fafafa;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .loading {
            color: #666;
            font-style: italic;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .query-display {
            background-color: #e8f4f8;
            padding: 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
            word-break: break-all;
        }
        .metadata {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            font-style: italic;
        }
        button {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #ff5252;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Unsplash API Proxy Test (with Access Key)</h1>
        
        <div class="test-section">
            <div class="test-title">API Configuration</div>
            <div class="info">
                <strong>Status:</strong> Using Unsplash API with Access Key Authentication<br>
                <strong>Endpoint:</strong> /api/unsplash-proxy.php<br>
                <strong>Method:</strong> Server-side proxy (bypasses CORS)<br>
                <strong>Rate Limit:</strong> 5050 requests/hour (Development Account)
            </div>
        </div>

        <div class="test-section">
            <div class="test-title">Test 1: Contract/Agreement Image</div>
            <div class="query-display">Query: contract,business,agreement</div>
            <div id="test1-container" class="image-container">
                <p class="loading">⏳ Loading image...</p>
            </div>
            <div id="test1-result"></div>
        </div>

        <div class="test-section">
            <div class="test-title">Test 2: Price Tag Image</div>
            <div class="query-display">Query: price-tag,cost,value</div>
            <div id="test2-container" class="image-container">
                <p class="loading">⏳ Loading image...</p>
            </div>
            <div id="test2-result"></div>
        </div>

        <div class="test-section">
            <div class="test-title">Test 3: Handshake/Deal Image</div>
            <div class="query-display">Query: handshake,agreement,deal</div>
            <div id="test3-container" class="image-container">
                <p class="loading">⏳ Loading image...</p>
            </div>
            <div id="test3-result"></div>
        </div>

        <div class="test-section">
            <div class="test-title">Test 4: Risk Image</div>
            <div class="query-display">Query: risk,danger,caution</div>
            <div id="test4-container" class="image-container">
                <p class="loading">⏳ Loading image...</p>
            </div>
            <div id="test4-result"></div>
        </div>

        <div class="test-section">
            <div class="test-title">Summary</div>
            <button onclick="runAllTests()">🔄 Run All Tests Again</button>
            <div id="summary-result" style="margin-top: 20px;"></div>
        </div>
    </div>

    <script>
        async function loadImage(query, containerId, resultId, testNum) {
            const container = document.getElementById(containerId);
            const resultDiv = document.getElementById(resultId);
            
            try {
                // Call our API proxy
                const response = await fetch(`./api/unsplash-proxy.php?action=fetch&q=${encodeURIComponent(query)}&w=400&h=300`);
                const data = await response.json();
                
                if (data.success && data.url) {
                    // Load the image
                    const img = new Image();
                    img.onload = function() {
                        container.innerHTML = `<img src="${data.url}" alt="${query}">`;
                        resultDiv.innerHTML = `
                            <div class="success">
                                ✓ Image loaded successfully!<br>
                                <div class="metadata">
                                    Author: ${data.author}<br>
                                    Photo ID: ${data.photo_id}
                                </div>
                            </div>
                        `;
                    };
                    img.onerror = function() {
                        container.innerHTML = '<p class="error">❌ Failed to display image</p>';
                        resultDiv.innerHTML = '<div class="error">Image loading failed</div>';
                    };
                    img.src = data.url;
                } else {
                    container.innerHTML = '<p class="error">❌ API Error</p>';
                    resultDiv.innerHTML = `<div class="error">Error: ${data.error || 'Unknown error'}</div>`;
                }
            } catch (error) {
                container.innerHTML = '<p class="error">❌ Request Failed</p>';
                resultDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        function runAllTests() {
            const tests = [
                { query: 'contract,business,agreement', container: 'test1-container', result: 'test1-result', num: 1 },
                { query: 'price-tag,cost,value', container: 'test2-container', result: 'test2-result', num: 2 },
                { query: 'handshake,agreement,deal', container: 'test3-container', result: 'test3-result', num: 3 },
                { query: 'risk,danger,caution', container: 'test4-container', result: 'test4-result', num: 4 }
            ];

            tests.forEach(test => {
                loadImage(test.query, test.container, test.result, test.num);
            });

            setTimeout(() => {
                updateSummary();
            }, 3000);
        }

        function updateSummary() {
            const allLoaded = document.querySelectorAll('.image-container img').length === 4;
            const summary = document.getElementById('summary-result');
            
            if (allLoaded) {
                summary.innerHTML = `
                    <div class="success">
                        ✓ All tests passed! The Unsplash API proxy is working correctly.<br>
                        You can now proceed with updating the lessons.
                    </div>
                `;
            }
        }

        // Run tests on page load
        window.addEventListener('load', runAllTests);
    </script>
</body>
</html>
