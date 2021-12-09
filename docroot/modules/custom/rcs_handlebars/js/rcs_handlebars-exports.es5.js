const Handlebars = require("handlebars");

/**
 * Render Handlebars templates.
 *
 * @param {string} id
 *   The library id i.e "article.block.foo"
 *
 * @param {object} data
 *   The data.
 *
 * @returns {object}
 *   Returns the object containing the value and ellipsis information.
 */
function handlebarsRender(id, data) {
  const templates = (typeof window !== 'undefined')
    ? window.rcsHandlebarsTemplates
    : rcsHandlebarsTemplates;

  if (!templates || templates.length === 0) {
    // It was noticed that sometimes, after a cache clear the hook
    // rcs_handlebars_templates() doesn't get called for some reason.
    // Throwing an error to monitor this.
    throw new Error("No handlebars templates found on the page.");
  }

  // Get the source template.
  const source = templates[id];

  // Compile source.
  const render = Handlebars.compile(source);

  // Return rendered template using data provided.
  return render(data);
}

exports.render = function render(
  template,
  data
) {
  return handlebarsRender(template, data);
};

/**
 * Helpers
 */

/**
 * Register helper for string translations.
 * Limitation: Only supports @ filter and one argument.
 * @todo Find a way to support multiple arguments and other filters.
 * @todo Support contexts.
 */
Handlebars.registerHelper('t', (str, args, options) => {
  args = args.hash || {};
  options = options || {};

  // Add @ to each key.
  Object.keys(args).map((key) => {
    args[`@${key}`] = args[key];
  });

  return Drupal.t(str, args, options);
});

/**
 * Register helper render other templates.
 */
Handlebars.registerHelper('render', (template, data) => handlebarsRender(template, data));

/**
 * Register helper format numbers.
 */
Handlebars.registerHelper('formatNumber', (number, digits) => number.toFixed(digits).toLocaleString());

/**
 * Register helpers for comparison i.e.
 *
 * {{#if (or
 *       (eq section1 "foo")
 *       (ne section2 "bar"))}}
 *   .. content
 * {{/if}}
 */
Handlebars.registerHelper({
  eq: (v1, v2) => v1 === v2,
  ne: (v1, v2) => v1 !== v2,
  lt: (v1, v2) => v1 < v2,
  gt: (v1, v2) => v1 > v2,
  lte: (v1, v2) => v1 <= v2,
  gte: (v1, v2) => v1 >= v2,
  and() {
    return Array.prototype.every.call(arguments, Boolean);
  },
  or() {
    return Array.prototype.slice.call(arguments, 0, -1).some(Boolean);
  }
});
