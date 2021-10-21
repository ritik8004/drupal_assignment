/* EventManager
 *
 * Copyright (c) 2009, Howard Rauscher
 * Licensed under the MIT License
 */

EventArg = function (name, data) {
  this.name = name;
  this.data = data;
  this.cancelled = false;
  this.removed = false;
};
EventArg.prototype = {
  cancel: function () {
    this.cancelled = true;
  },
  remove: function () {
    this.removed = true;
  },
};

function EventManager() {
  this.listeners = {};
}
EventManager.prototype = {
  addListener: function (name, fn, priority) {
    if (typeof priority === 'undefined') {
      priority = 0;
    }

    // Initialize.
    this.listeners[name] = typeof this.listeners[name] !== 'undefined' ? this.listeners[name] : {};
    this.listeners[name][priority] = typeof this.listeners[name][priority] !== 'undefined' ? this.listeners[name][priority] : [];

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
      var data = {}, evt;
      var len = listeners.length;

      for (var i = len -1; i >= 0; i--) {
        evt = new EventArg(name, data);
        listeners[i].call(window, args);
        data = evt.data;

        if (evt.removed) {
          listeners.splice(i, 1);
          len = listeners.length;
          --i;
        }
        if (evt.cancelled) {
          break;
        }
      }
    }
    return args;
  },
};

window.EventManager = new EventManager();
