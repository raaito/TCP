import http from 'node:http'

const sleep = ms => new Promise(r => setTimeout(r, ms))

function get(url) {
    return new Promise((res, rej) => {
        http.get(url, r => {
            let d = ''
            r.on('data', c => d += c)
            r.on('end', () => res(d))
        }).on('error', rej)
    })
}

const list = JSON.parse(await get('http://127.0.0.1:9223/json/list'))
const page = list.find(t => t.type === 'page')
if (!page) { console.error('no page target'); process.exit(1) }

const ws = new WebSocket(page.webSocketDebuggerUrl)
let id = 0
const pending = new Map()
const events = []

function send(method, params = {}) {
    return new Promise((res, rej) => {
        const mid = ++id
        pending.set(mid, { res, rej })
        ws.send(JSON.stringify({ id: mid, method, params }))
    })
}

ws.onmessage = e => {
    const m = JSON.parse(e.data)
    if (m.id && pending.has(m.id)) {
        pending.get(m.id).res(m.result)
        pending.delete(m.id)
    } else if (m.method) {
        events.push(m)
    }
}

await new Promise(r => { ws.onopen = r })
console.log('connected')

await send('Runtime.enable')
await send('Page.enable')
await send('Log.enable')
await send('Network.enable')

await sleep(4000)

await send('Runtime.evaluate', {
    expression: `(() => {
        const setVal = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        const setInput = (sel, val) => {
            const el = document.querySelector(sel);
            if (!el) return 'MISS:' + sel;
            setVal.call(el, val);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            return 'OK';
        };
        const r1 = setInput('#app input[type=text]', '+2348010000001');
        const r2 = setInput('#app input[type=password]', 'password');
        const form = document.querySelector('#app form');
        if (!form) return 'no form';
        form.requestSubmit();
        return 'submitted ' + r1 + ' ' + r2;
    })()`,
    returnByValue: true,
}).then(r => console.log('login submit:', JSON.stringify(r.result)))

await sleep(6000)

const res = await send('Runtime.evaluate', {
    expression: `(() => ({
        url: location.href,
        title: document.title,
        appHtmlLen: (document.getElementById('app') || {}).innerHTML ? document.getElementById('app').innerHTML.length : -1,
        bodyText: (document.body && document.body.innerText || '').slice(0, 300),
    }))()`,
    returnByValue: true,
})
console.log('state:', JSON.stringify(res.result.value))

const errs = events.filter(e => e.method === 'Runtime.exceptionThrown' || (e.method === 'Log.entryAdded' && ['error', 'warning'].includes(e.params.entry.level)))
console.log('\n--- JS errors/warnings ---')
for (const e of errs) {
    if (e.method === 'Runtime.exceptionThrown') {
        console.log('EXCEPTION:', JSON.stringify(e.params.exceptionDetails.exception?.description || e.params.exceptionDetails.text).slice(0, 800))
    } else {
        console.log('LOG:', e.params.entry.level, e.params.entry.text.slice(0, 800))
    }
}
console.log('--- end ---')

const respEvents = events.filter(e => e.method === 'Network.responseReceived').map(e => `${e.params.response.status} ${e.params.response.url}`)
console.log('\n--- responses ---')
for (const r of respEvents) console.log(r)

ws.close()
process.exit(0)
