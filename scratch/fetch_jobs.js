const https = require('https');

const options = {
  hostname: 'api.github.com',
  path: '/repos/kadiry37/bybossK/actions/runs/26308803072/jobs',
  method: 'GET',
  headers: {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
  }
};

const req = https.request(options, (res) => {
  let data = '';
  res.on('data', (chunk) => { data += chunk; });
  res.on('end', () => {
    try {
      const parsed = JSON.parse(data);
      if (parsed.jobs && parsed.jobs[0]) {
        const job = parsed.jobs[0];
        console.log(`Job: ${job.name} - Status: ${job.status} - Conclusion: ${job.conclusion}`);
        job.steps.forEach(step => {
          console.log(`  Step ${step.number}: ${step.name} - Status: ${step.status} - Conclusion: ${step.conclusion}`);
        });
      } else {
        console.log(data);
      }
    } catch (e) {
      console.log('Error parsing:', e.message);
      console.log('Raw data:', data.substring(0, 1000));
    }
  });
});

req.on('error', (e) => {
  console.error(e);
});

req.end();
