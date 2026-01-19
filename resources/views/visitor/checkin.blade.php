<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visitor Check-in</title>
</head>
<body>
    <h1>Visitor Check-in (manual)</h1>
    <form id="checkin">
        <label>Name: <input name="name" /></label><br />
        <label>Email: <input name="email" /></label><br />
        <label>Phone: <input name="phone" /></label><br />
        <button type="submit">Check in</button>
    </form>
    <pre id="out"></pre>
    <script>
        document.getElementById('checkin').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const body = Object.fromEntries(fd.entries());
            const res = await fetch('/api/visitors/checkin', { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
            const json = await res.json();
            document.getElementById('out').innerText = JSON.stringify(json, null, 2);
        });
    </script>
</body>
</html>
