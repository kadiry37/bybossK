const fs = require('fs');
const path = require('path');

function walk(dir, results = []) {
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    file = path.join(dir, file);
    const stat = fs.statSync(file);
    if (stat && stat.isDirectory()) {
      walk(file, results);
    } else {
      if (file.endsWith('.astro')) {
        results.push(file);
      }
    }
  });
  return results;
}

const files = walk('c:\\Users\\asus\\Desktop\\Kadir2026\\astro-project\\src\\pages');

files.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  if (content.includes('await get')) {
    console.log('========================================================================');
    console.log(`File: ${path.relative('c:\\Users\\asus\\Desktop\\Kadir2026\\astro-project', file)}`);
    const lines = content.split('\n');
    let hasTry = false;
    // Check if the file has "try" before "await get"
    lines.forEach((line, idx) => {
      if (line.includes('await get')) {
        // print surrounding lines
        const start = Math.max(0, idx - 5);
        const end = Math.min(lines.length - 1, idx + 5);
        for (let i = start; i <= end; i++) {
          const marker = (i === idx) ? '>>>' : '   ';
          console.log(`${marker} Line ${i + 1}: ${lines[i]}`);
        }
      }
    });
  }
});
