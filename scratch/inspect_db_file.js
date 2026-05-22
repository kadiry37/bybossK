const fs = require('fs');
const path = require('path');

const dbPath = path.join(__dirname, '../db/custom.db');
if (!fs.existsSync(dbPath)) {
    console.log("File not found at:", dbPath);
    process.exit(1);
}

const stats = fs.statSync(dbPath);
console.log("Size:", stats.size, "bytes");

const fd = fs.openSync(dbPath, 'r');
const buffer = Buffer.alloc(100);
fs.readSync(fd, buffer, 0, 100, 0);
fs.closeSync(fd);

console.log("First 100 bytes (HEX):");
console.log(buffer.toString('hex'));

console.log("First 100 bytes (ASCII/UTF8):");
console.log(buffer.toString('utf8').replace(/[^\x20-\x7E]/g, '.'));
