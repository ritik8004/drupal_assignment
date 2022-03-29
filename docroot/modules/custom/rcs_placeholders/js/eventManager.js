/**
 * Reference for this code has been taken from the following gist:
 * https://gist.github.com/howardr/118668/ecd43be756079494f85d303ef69e9e04ef72031e
 */

/**
 * A global object which stores the list of event listeners and their priority.
 * We do not modify this variable directly.
 * Instead we use RcsEventManager to add/trigger the listeners.
 *
 * @var {object}
 */
var rcsSubscribedEvents = {};

/**
 * A global object which contains methods to add/trigger listeners.
 *
 * Use RcsEventManager.addListener() to add the listener for a specific event
 * along with it's priority.
 *
 * Use RcsEventManager.fire() to trigger an event with the given args.
 */
globalThis.RcsEventManager = {
  addListener: function (name, callback, priority) {
    if (typeof priority === 'undefined') {
      // Highest priority will be executed first.
      // If priority value is same for two functions it
      // will be executed on first come first serve bases.
      priority = 0;
    }

    // Make sure priority is always integer so we can sort.
    priority = parseInt(priority, 10);

    // Initialize if required.
    rcsSubscribedEvents[name] = rcsSubscribedEvents[name] || {};
    rcsSubscribedEvents[name][priority] = rcsSubscribedEvents[name][priority] || [];

    // Add the listener.
    rcsSubscribedEvents[name][priority].push(callback);
  },
  fire: function (name, args) {
    if (typeof rcsSubscribedEvents[name] === 'undefined') {
      return args;
    }

    // A flat array which will hold the listeners.
    var listeners = [];

    // Sort by key which is priority.
    // Highest priority will be executed first.
    // If priority value is same for two functions it
    // will be executed on first come first serve bases.
    Object.keys(rcsSubscribedEvents[name])
      .sort()
      .reverse()
      .forEach(function (priority) {
        listeners = listeners.concat(rcsSubscribedEvents[name][priority]);
      });

    args = args || {};

    // Now call the event listeners with the arguments.
    listeners.forEach(function (callback) {
      callback.call(this, args);
    });

    // Return args so calling function can have updated object in response.
    return args;
  },
};
