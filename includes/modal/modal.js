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

var Modal = Class({
  initialize: function() {
    var _this = this;
    /* Get trigger element */
    var modalTrigger = document.getElementsByClassName('modal_trigger');
    /* Set onclick event handler for all trigger elements */
    for (var i = 0; i < modalTrigger.length; i++) {
      modalTrigger[i].onclick = function(e) {
        e.preventDefault();
        _this.openModal(this.getAttribute('href').split('#')[1]);
      }
    }

    /* Get close button */
    var closeButton = document.getElementsByClassName('modal_close');
    var closeOverlay = document.getElementsByClassName('modal_overlay');

    /* Set onclick event handler for close buttons */
    for (var i = 0; i < closeButton.length; i++) {
      closeButton[i].onclick = function(e) {
        e.preventDefault();
        _this.closeModal(this.parentNode.parentNode);
      }
    }

    /* Set onclick event handler for modal overlay */
    for (var i = 0; i < closeOverlay.length; i++) {
      closeOverlay[i].onclick = function(e) {
        e.preventDefault();
        _this.closeModal(this.parentNode);
      }
    }

    if (window.location.hash) {
      var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
      if (hash.indexOf("open_modal") > -1) {
        _this.openModal(hash)
      }
    }
  },
  openModal: function(target) {
    var modalWindow = document.getElementById(target);
    if (modalWindow) {
      modalWindow.classList
        ? modalWindow.classList.add('open')
        : modalWindow.className += ' ' + 'open';
    }
  },
  closeModal: function(modalWindow) {
    if (modalWindow) {
      modalWindow.classList
        ? modalWindow.classList.remove('open')
        : modalWindow.className = modalWindow.className.replace(new RegExp('(^|\\b)' +
          'open'.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
    }
  }
});
