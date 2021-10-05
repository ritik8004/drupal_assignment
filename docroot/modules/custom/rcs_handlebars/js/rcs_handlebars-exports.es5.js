const Handlebars = require("handlebars");

/**
 * Returns the value from object using nested keys i.e. "field.field_name"
 *
 * @param path string
 *   The path for the value inside the object separated by .
 * @param obj
 *   The object.
 * @param separator
 *   The separator, defaults to .
 * @return {*}
 *   The data.
 */
function resolvePath(path, obj=self, separator='.') {
  var properties = Array.isArray(path) ? path : path.split(separator)
  return properties.reduce((prev, curr) => prev && prev[curr], obj)
}

/**
 * Render Handlebars templates.
 *
 * @param {object} path
 *   The path in the object. i.e "block.block_name"
 *
 * @param {object} data
 *   The data.
 *
 * @returns {object}
 *   Returns the object containing the value and ellipsis information.
 */
function handlebarsRender(template, data) {
  const templates = (typeof window !== 'undefined')
    ? window.rcsHandlebarsTemplates
    : rcsHandlebarsTemplates;

  if (!templates || templates.length === 0) {
    return '';
  }

  // Get the source template.
  const source = resolvePath(template, templates);

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
 */
Handlebars.registerHelper('t', (str) => rcsTranslatedText(str));

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
