const Handlebars = require("handlebars");

/**
 * Register helper for Drupal.t().
 */
Handlebars.registerHelper('t', (str) => Drupal.t(str));

/**
 * Converts twig templates to handlebars.
 *
 * @param source
 */
function convertTwigToHandlebars(source) {
  let replacements = [
    // Convert translated strings.
    { "{{.*?('.*?')(|t).*?}}": "{{t $1}}" },
    // Make Twig comments become Handlebars strings.
    { "{#": "{{'" },
    { "#}": "'}}" },
    // Convert if statements.
    { "{% if (.*?) %}": "{{#if $1 }}" },
    { "{% endif %}": "{{/if}}" },
  ];

  replacements.forEach((item) => {
    let search = Object.entries(item)[0][0];
    let replace = Object.entries(item)[0][1];
    source = source.replace(new RegExp(search, 'gm'), replace);
  });

  return source;
}

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
    ? window.rcsTwigTemplates
    : rcsTwigTemplates;

  // Get the source template.
  let source = resolvePath(template, templates);

  // Convert twig template to handlebar template.
  source = convertTwigToHandlebars(source);

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
