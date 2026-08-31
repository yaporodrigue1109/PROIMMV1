import fs from 'node:fs/promises';

const [wsUrl, url, output, email, password] = process.argv.slice(2);
if (!wsUrl || !url || !output) {
  throw new Error('Usage: node documentation/capture-page.mjs <websocket-url> <url> <output> [email] [password]');
}

const socket = new WebSocket(wsUrl);
let nextId = 1;
const pending = new Map();

socket.addEventListener('message', ({ data }) => {
  const message = JSON.parse(data);
  if (!message.id || !pending.has(message.id)) return;
  const { resolve, reject } = pending.get(message.id);
  pending.delete(message.id);
  message.error ? reject(new Error(message.error.message)) : resolve(message.result);
});

await new Promise((resolve, reject) => {
  socket.addEventListener('open', resolve, { once: true });
  socket.addEventListener('error', reject, { once: true });
});

function send(method, params = {}) {
  const id = nextId++;
  socket.send(JSON.stringify({ id, method, params }));
  return new Promise((resolve, reject) => pending.set(id, { resolve, reject }));
}

const wait = (duration) => new Promise((resolve) => setTimeout(resolve, duration));

await send('Page.enable');
await send('Runtime.enable');
await send('Emulation.setDeviceMetricsOverride', {
  width: 1440,
  height: 1000,
  deviceScaleFactor: 1,
  mobile: false,
});
await send('Page.navigate', { url });
await wait(3500);

if (email && password) {
  const expression = `(() => {
    const email = document.querySelector('input[type="email"]');
    const password = document.querySelector('input[type="password"]');
    if (!email || !password) return 'form-not-found';
    const setValue = (element, value) => {
      const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
      setter.call(element, value);
      element.dispatchEvent(new Event('input', { bubbles: true }));
      element.dispatchEvent(new Event('change', { bubbles: true }));
    };
    setValue(email, ${JSON.stringify(email)});
    setValue(password, ${JSON.stringify(password)});
    const form = email.closest('form');
    if (form?.requestSubmit) form.requestSubmit();
    else form?.submit();
    return 'submitted';
  })()`;
  await send('Runtime.evaluate', { expression });
  await wait(5000);
}

const { data } = await send('Page.captureScreenshot', {
  format: 'png',
  captureBeyondViewport: false,
  fromSurface: true,
});
await fs.writeFile(output, Buffer.from(data, 'base64'));
socket.close();
