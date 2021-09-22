const Handlebars = require("handlebars");

/**
 * Register helper for Drupal.t().
 */
Handlebars.registerHelper('t', (str) => rcsTranslatedText(str));

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
  let templates = (typeof window !== 'undefined')
    ? window.rcsHandlebarsTemplates
    : rcsHandlebarsTemplates;

  // Get the source template.
  let source = resolvePath(template, templates);

  // Compile source.
  let render = Handlebars.compile(source);

  // Return rendered template using data provided.
  return render(data);
}

exports.render = function render(
  template,
  data
) {
  return handlebarsRender(template, data);
};
