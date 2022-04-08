'use strict';

function timer(callback, timeout) {
  jest.useFakeTimers();

  setTimeout(() => {
    callback && callback();
  }, timeout * 1000);

  jest.advanceTimersByTime(timeout * 1000);
  jest.useRealTimers();
}

module.exports = timer;
