const https = require('https');
const fs = require('fs');

const options = {
  hostname: 'api.github.com',
  path: '/repos/kadiry37/bybossK/actions/jobs/77452175863/logs',
  method: 'GET',
  headers: {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
  }
};

function downloadLog(url) {
  https.get(url, (res) => {
    if (res.statusCode === 302 || res.statusCode === 301 || res.statusCode === 307 || res.statusCode === 308) {
      downloadLog(res.headers.location);
      return;
    }
    let data = '';
    res.on('data', (chunk) => { data += chunk; });
    res.on('end', () => {
      const lines = data.split('\n');
      console.log(`Total log lines: ${lines.length}`);
      console.log('--- LAST 60 LINES ---');
      lines.slice(-60).forEach(line => console.log(line));
    });
  }).on('error', (e) => {
    console.error(e);
  });
}

const req = https.request(options, (res) => {
  if (res.statusCode === 302 || res.statusCode === 301 || res.statusCode === 307 || res.statusCode === 308) {
    downloadLog(res.headers.location);
  } else {
    let data = '';
    res.on('data', (chunk) => { data += chunk; });
    res.on('end', () => {
      console.log('Response status:', res.statusCode);
      console.log(data);
    });
  }
});

req.on('error', (e) => {
  console.error(e);
});

req.end();
