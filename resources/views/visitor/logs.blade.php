<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visitor Logs</title>
</head>
<body>
    <h1>Visitor Logs (JSON)</h1>
    <pre id="out"></pre>
    <script>
        (async () => {
            const res = await fetch('/api/visitors/logs', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            document.getElementById('out').innerText = JSON.stringify(json, null, 2);
        })();
    </script>
</body>
</html>
