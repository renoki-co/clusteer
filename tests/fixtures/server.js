const express = require('express');
const app = express();
const port = 8000;

app.get('/*', (req, res) => {
  res.sendFile('tester.html', { root: __dirname });
});

app.listen(port, () => {
  console.log(`Testing webserver on http://localhost:${port}`);
});
