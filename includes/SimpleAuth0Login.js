var Class = function(methods) {
  var klass = function() {
    this.initialize.apply(this, arguments);
  };

  for (var property in methods) {
    klass.prototype[property] = methods[property];
  }

  if (!klass.prototype.initialize)
    klass.prototype.initialize = function() {};

  return klass;
};

var SimpleAuth0Login = Class({
  initialize: function(ajax_url, support_email) {
    this.ajax_url = ajax_url;
    this.support_email = support_email;
    this.lostPasswordInit();
    this.profileAdditionsInit();
    this.registrationInit();
    new Modal();
  },
  params: function(obj) {
    return Object.keys(obj).map(function(k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
    }).join('&');
  },
  resetPassword: function(email) {
    var _this = this;
    var ajax_url = this.ajax_url;
    return new Promise(function(resolve, reject) {
      fetch(ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({'Content-Type': 'application/x-www-form-urlencoded'}),
        body: _this.params({action: "simple_auth0_login_password_reset", email: email})
      }).then((function(resp) {
        return resp.json();
      })).then(function(data) {
        resolve(data);
      }).catch(function(error) {
        console.log(error);
        reject(error);
      });
    });
  },
  register: function(email, password) {
    var _this = this;
    var ajax_url = this.ajax_url;
    return new Promise(function(resolve, reject) {
      fetch(ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({'Content-Type': 'application/x-www-form-urlencoded'}),
        body: _this.params({action: "simple_auth0_login_register", email: email, password: password})
      }).then((function(resp) {
        return resp.json();
      })).then(function(data) {
        resolve(data);
      }).catch(function(error) {
        console.log(error);
        reject(error);
      });
    });
  },
  profileAdditionsInit: function() {
    var _this = this;
    var password_reset = document.getElementById("password_reset");
    var email = document.getElementById("email");
    if (!email) {
      return;
    }
    email.disabled = true;

    password_reset.addEventListener('click', function(e) {
      e.preventDefault();
      _this.resetPassword(email.value).then(function(data) {
        if (data.hasOwnProperty("success") && (data.success === "true" || data.success === true)) {
          alert("A password request has been sent to your email, this could take up to 30 minutes.");
        } else {
          console.log(data);
          alert("There was an unknown error, please contact " + this.support_email + " if this continues");
        }
      }, function(data) {
        console.log(data);
        alert("There was an unknown error, please contact " + this.support_email + " if this continues");
      });
    });
  },
  registrationInit: function() {
    var registerform = document.getElementById("registerform");
    if (!registerform) {
      return;
    }
    var user_email = document.getElementById("user_email");
    var user_login = document.getElementById("user_login");
    user_email.onkeyup = function(e) {
      var value = e.target.value.toLowerCase();
      e.target.value = value;
      user_login.value = value;
    }
  },
  lostPasswordInit: function() {
    var _this = this;
    var user_login = document.getElementById("user_login")
    var nav = document.getElementById("nav");
    if (!nav) {
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
      if (hash && hash.indexOf("modal") > -1) {
        nav_link.className = "modal_trigger";
        nav_link.addEventListener('click', function(e) {
          possible_email.value = user_login.value
        });
      }
    }

    send_password.addEventListener('click', function(e) {
      e.preventDefault();
      var email = possible_email.value;
      if (email && _this.validEmail(email)) {
        _this.resetPassword(email).then(function(data) {
          if (data.success && (data.success === "true" || data.success === true)) {
            alert("A password request has been sent to your email, this could take up to 30 minutes.");
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
  },
  validEmail:function(mail)
  {
    if (/^[_a-z0-9-]+(\.[_a-z0-9-]+)*(\+[a-z0-9-]+)?@[a-z0-9-]+(\.[a-z0-9-]+)*$/i.test(mail))
    {
      return (true)
    }else{
      return (false)
    }
  }
});
