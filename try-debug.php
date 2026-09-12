<?php
// Debug version - no authentication required
?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Try - Debug Version</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=DM+Serif+Display:wght@400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --sand: #f5ede6;
            --sage-deep: #2d5a3d;
            --ink: #1a1a18;
            --surface: #ffffff;
        }
        html, body { height: 100%; }
        body { font-family: Inter, sans-serif; background: var(--sand); color: var(--ink); }
        .dash { display: flex; height: 100vh; }
        .sidebar {
            width: 240px;
            flex-shrink: 0;
            background: var(--sage-deep);
            color: white;
            padding: 20px;
            overflow-y: auto;
        }
        .main { flex: 1; overflow-y: auto; background: var(--sand); }
        .tab-content { display: none; padding: 20px; }
        .tab-content.visible { display: block; }
        .nav-item { display: block; width: 100%; padding: 10px; margin: 5px 0; background: transparent; border: none; color: white; cursor: pointer; text-align: left; }
        .nav-item.active { background: rgba(255,255,255,0.2); border-radius: 4px; }
    </style>
</head>
<body>
<div class="dash">
    <aside class="sidebar">
        <h2>Test Menu</h2>
        <button class="nav-item active" data-tab="dashboard">Dashboard</button>
        <button class="nav-item" data-tab="tests">Tests</button>
        <button class="nav-item" data-tab="certificates">Certificates</button>
    </aside>
    
    <div class="main" id="mainContent">
        <div id="tab-dashboard" class="tab-content visible">
            <h1>Dashboard</h1>
            <p>This is the dashboard tab. If you can see this, the tab system works!</p>
        </div>
        
        <div id="tab-tests" class="tab-content">
            <h1>Tests</h1>
            <p>This is the tests tab.</p>
        </div>
        
        <div id="tab-certificates" class="tab-content">
            <h1>Certificates</h1>
            <p>This is the certificates tab.</p>
        </div>
    </div>
</div>

<script>
console.log('Page loaded');

function activateTab(tabId) {
    console.log('Activating tab:', tabId);
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('visible'));
    document.querySelectorAll('.nav-item').forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabId));
    const target = document.getElementById(`tab-${tabId}`);
    if (target) target.classList.add('visible');
}

document.querySelectorAll('.nav-item').forEach(btn => {
    btn.addEventListener('click', () => activateTab(btn.dataset.tab));
});

console.log('Event listeners attached');
activateTab('dashboard');
console.log('Page initialization complete');
</script>
</body>
</html>
