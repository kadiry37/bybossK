const fs = require('fs');
const content = fs.readFileSync('C:\\Users\\asus\\.gemini\\antigravity\\brain\\48511709-47ec-4c46-843e-630c271e1ecb\\.system_generated\\steps\\344\\content.md', 'utf8');
const jsonStr = content.substring(content.indexOf('---') + 3).trim();
try {
  const data = JSON.parse(jsonStr);
  const job = data.jobs[0];
  console.log(`Job: ${job.name} - Status: ${job.status} - Conclusion: ${job.conclusion}`);
  job.steps.forEach(step => {
    console.log(`  Step ${step.number}: ${step.name} - Status: ${step.status} - Conclusion: ${step.conclusion}`);
  });
} catch (e) {
  console.error('Failed to parse:', e);
}
