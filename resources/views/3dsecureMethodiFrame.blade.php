<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test iframe PostMessage</title>
</head>
<body>
<h1>Test iframe PostMessage</h1>
<p>This page will send a "Hello World" message to the parent window.</p>

<script>
    window.parent.postMessage({!!  json_encode($details->toArray())!!}, '*');
</script>
</body>
</html>
