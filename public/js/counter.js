/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************!*\
  !*** ./resources/js/counter.js ***!
  \*********************************/
var interval;
self.addEventListener('message', function (e) {
  switch (e.data) {
    case 'start':
      interval = setInterval(function () {
        self.postMessage('tick');
      }, 1000);
      break;

    case 'stop':
      clearInterval(interval);
      break;
  }

  ;
}, false);
/******/ })()
;