const request = require('supertest');

req = request(`${process.env.APP_URL}`);

describe('GET /', function () {
  this.slow(200);
  it('responds with 200 OK', function (done) {
    req
      .get('/')
      .expect(200, done);
  });
  it('responds with docker image', function (done) {
    req
      .get('/')
      .expect(function (res) {
        if (!res.res.text.includes('img/docker.png')) throw new Error('missing "http://www.php.net" in response body');
      })
      .end(done);
  })
});

describe('GET /?phpinfo', function () {
  this.slow(200);
  it('responds with 200 OK', function (done) {
    req
      .get('/?phpinfo')
      .expect(200, done);
  });
  it('responds with php info', function (done) {
    req
      .get('/?phpinfo')
      .expect(function (res) {
        if (!res.res.text.includes('PHP Version')) throw new Error('missing "http://www.php.net" in response body');
      })
      .end(done);
  });
});
