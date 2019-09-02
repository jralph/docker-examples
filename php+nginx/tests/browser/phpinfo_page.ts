// @ts-ignore
import { Selector, RequestLogger } from 'testcafe';

// @ts-ignore
const url = `${process.env.APP_URL}?phpinfo`;

const logger = RequestLogger(url, {
    logResponseHeaders: true,
    logResponseBody: false
});

// @ts-ignore
fixture `PHPInfo Page`
    .page(url)
    .requestHooks(logger);

// @ts-ignore
test('Test page loads', async t => {
    const img = Selector('img[alt="PHP logo"]').exists;

    await t
        .expect(img).ok();
});

// @ts-ignore
test('Test response is 200 OK', async t => {
    await t
        .expect(logger.contains(r => r.response.statusCode === 200)).ok();
});
