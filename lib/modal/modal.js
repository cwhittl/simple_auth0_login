/* This script supports IE9+ */
(function() {
  /* Opening modal window function */
  if(window.location.hash) {
      var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
      if(hash.indexOf("open_modal") > -1){
        setTimeout(function(){
          openModal(hash)
        },300)
      }
  }
  function initModal() {
      /* Get trigger element */
      var modalTrigger = document.getElementsByClassName('modal_trigger');
      /* Set onclick event handler for all trigger elements */
      for(var i = 0; i < modalTrigger.length; i++) {
          modalTrigger[i].onclick = function() {
            openModal(this.getAttribute('href').split('#')[1]);
          }
      }

      /* Get close button */
      var closeButton = document.getElementsByClassName('modal_close');
      var closeOverlay = document.getElementsByClassName('modal_overlay');

      /* Set onclick event handler for close buttons */
        for(var i = 0; i < closeButton.length; i++) {
          closeButton[i].onclick = function() {
            closeModal(this.parentNode.parentNode);
          }
        }

      /* Set onclick event handler for modal overlay */
        for(var i = 0; i < closeOverlay.length; i++) {
          closeOverlay[i].onclick = function() {
            closeModal(this.parentNode);
          }
        }
  }

  function openModal(target){
    var modalWindow = document.getElementById(target);
    if(modalWindow){
      modalWindow.classList ? modalWindow.classList.add('open') : modalWindow.className += ' ' + 'open';
    }
  }

  function closeModal(modalWindow){
    if(modalWindow){
        modalWindow.classList ? modalWindow.classList.remove('open') : modalWindow.className = modalWindow.className.replace(new RegExp('(^|\\b)' + 'open'.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
    }
  }


  /* Handling domready event IE9+ */
  function ready(fn) {
    if (document.readyState != 'loading'){
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  /* Triggering modal window function after dom ready */
  ready(initModal);
}());
