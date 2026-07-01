// Reads a JSON job from stdin: { subscription, payload, vapid: { publicKey, privateKey, subject } }
// Sends one Web Push notification and prints { ok: true } or { ok: false, error } to stdout.
const webpush = require('web-push');

let input = '';
process.stdin.on('data', (chunk) => { input += chunk; });
process.stdin.on('end', async () => {
  try {
    const job = JSON.parse(input);
    webpush.setVapidDetails(job.vapid.subject, job.vapid.publicKey, job.vapid.privateKey);

    await webpush.sendNotification(job.subscription, JSON.stringify(job.payload));
    process.stdout.write(JSON.stringify({ ok: true }));
  } catch (err) {
    process.stdout.write(JSON.stringify({
      ok: false,
      error: err.message || String(err),
      statusCode: err.statusCode || null,
    }));
  }
});
