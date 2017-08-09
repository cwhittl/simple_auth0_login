class SimpleAuth0Login {
  constructor(ajax_url, support_email) {
    this.ajax_url = ajax_url;
    this.support_email = support_email;
    this.lostPasswordInit();
    this.profileAdditionsInit();
    this.registrationInit();
  }

  params(obj) {
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

  register(email, password) {
    var _this = this;
    var ajax_url = this.ajax_url;
    return new Promise(function(resolve, reject) {
      fetch(ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({'Content-Type': 'application/x-www-form-urlencoded'}),
        body: _this.params({action: "simple_auth0_login_register", email: email, password: password})
      }).then((resp) => resp.json()).then(function(data) {
        resolve(data);
      }).catch(function(error) {
        console.log(error);
        reject(error);
      });
    });
  }

  profileAdditionsInit() {
    var _this = this;
    var password_reset = document.getElementById("password_reset");
    var email = document.getElementById("email");
    if(!email){
      return;
    }
    email.disabled = true;

    password_reset.addEventListener('click', function(e) {
      e.preventDefault();
      _this.resetPassword(email.value).then(function(data) {
        if (data.hasOwnProperty("success") && (data.success === "true" || data.success === true)) {
          alert("A password request has been sent to your email.");
        } else {
          console.log(data);
          alert("There was an unknown error, please contact " + this.support_email + " if this continues");
        }
      }, function(data) {
        console.log(data);
        alert("There was an unknown error, please contact " + this.support_email + " if this continues");
      });
    });
  }

  registrationInit() {
    var registerform = document.getElementById("registerform");
    if (!registerform) {
      return;
    }
    var user_email = registerform.getElementById("user_email");
    var user_login = registerform.getElementById("user_login");
    user_email.onkeyup = function(e) {
      user_login.value = e.target.value;
    }
  }

  lostPasswordInit() {
    var _this = this;
    var user_login = document.getElementById("user_login")
    var nav = document.getElementById("nav");
    if(!nav){
      return;
    }
    var nav_links = nav.getElementsByTagName("A");
    var password_reset_form = document.getElementById("lostPassword").getElementsByTagName("form")[0];
    var possible_email = document.getElementById("possible_email");
    var send_password = document.getElementById("send_password");

    user_login.setAttribute('type', 'email');
    for (var i = 0; i < nav_links.length; ++i) {
      var nav_link = nav_links[i];
      var hash = nav_link.hash.substr(1);
      if(hash && hash.indexOf("modal") > -1){
        nav_link.className = "modal_trigger";
        nav_link.addEventListener('click', function(e) {
          possible_email.value = user_login.value
        });
      }
    }

    send_password.addEventListener('click', function(e) {
      e.preventDefault();
      if (password_reset_form.checkValidity()) {
        _this.resetPassword(possible_email.value).then(function(data) {
          if (data.success && (data.success === "true" || data.success === true)) {
            alert("A password request has been sent to your email.");
          } else {
            console.log(data);
            alert("There was an unknown error, please contact " + this.support_email + " if this continues");
          }
        }, function(data) {
          console.log(data);
          alert("There was an unknown error, please contact " + this.support_email + " if this continues");
        });
      } else {
        alert("Please include a valid email address");
      }
    });
  }
}
