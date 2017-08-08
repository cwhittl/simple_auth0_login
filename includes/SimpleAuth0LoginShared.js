class SimpleAuth0LoginShared {
  constructor(ajax_url, support_email) {
    this.ajax_url = ajax_url;
    this.support_email = support_email;
  }

  params (obj) {
    return Object.keys(obj).map((k) => encodeURIComponent(k) + '=' + encodeURIComponent(obj[k])).join('&')
  }

  resetPassword(email) {
    var _this = this;
    var ajax_url = this.ajax_url;
    return new Promise(function(resolve, reject) {
      fetch(ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({'Content-Type': 'application/x-www-form-urlencoded'}),
        body: _this.params({action: "simple_auth0_login_password_reset", email: email})
      }).then((resp) => resp.json()).then(function(data) {
        resolve(data);
      }).catch(function(error) {
        console.log(error);
        reject(error);
      });
    });
  }

  register(email,password) {
    var _this = this;
    var ajax_url = this.ajax_url;
    return new Promise(function(resolve, reject) {
      fetch(ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({'Content-Type': 'application/x-www-form-urlencoded'}),
        body: _this.params({action: "simple_auth0_login_register", email: email, password:password})
      }).then((resp) => resp.json()).then(function(data) {
        resolve(data);
      }).catch(function(error) {
        console.log(error);
        reject(error);
      });
    });
  }
}
