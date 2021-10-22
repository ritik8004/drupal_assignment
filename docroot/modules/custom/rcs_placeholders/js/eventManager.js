/* EventManager
 *
 * Copyright (c) 2009, Howard Rauscher
 * Licensed under the MIT License
 */
function EventManager() {
  this.listeners = {};
}
EventManager.prototype = {
  addListener: function (name, fn, priority) {
    if (typeof priority === 'undefined') {
      priority = 0;
    }

    // Initialize.
    this.listeners[name] = this.listeners[name] || {};
    this.listeners[name][priority] = this.listeners[name][priority] || [];

    // Add the listener.
    this.listeners[name][priority].push(fn);
    return this;
  },
  fire: function (name, args) {
    var listeners = [];
    Object.values(this.listeners[name]).forEach(function (priorityListeners) {
      listeners = listeners.concat(priorityListeners);
    });

    args = args || {};
    if (typeof listeners !== 'undefined') {
      var len = listeners.length;

      for (var i = len -1; i >= 0; i--) {
        listeners[i].call(window, args);
      }
    }
    return args;
  },
};

window.EventManager = new EventManager();
