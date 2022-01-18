// Render function to prepare the markup for carousel and replace placeholders
// with API Response.
exports.render = function render(
  settings,
  inputs,
  innerHtml
) {
  const children = Array.isArray(inputs.children) ? inputs.children : [];
  const termData = [];

  children.forEach(function (child) {
    termData.push({
      path: child.url_path,
      label: child.name,
      active_class: '',
    });
  });

  handlebarsRenderer.render('carousel.accordion', {
    title: 'abcd',
    content: termData,
    view_all_link: 'View All',
    view_all_text: '',
  });






  // Covert innerHtml to a jQuery object.
  const innerHtmlObj = jQuery('<div>').html(innerHtml);

  // Extract the clickable and unclickable elements.
  let clickable = '';
  let unclickable = '';
  if (innerHtmlObj.find('li').length > 1) {
    clickable = innerHtmlObj.find('li').first().html();
    unclickable = innerHtmlObj.find('li').first().next().html();
  }

  // Proceed only if the elements are present.
  if (clickable && unclickable) {
    // Remove the placeholder li elements.
    innerHtmlObj.find('li').remove();
    // @todo Handle special base where we separate URL by - instead of /.
    const firstLevelTermUrl = rcsWindowLocation().pathname.match(`\/${settings.path.currentLanguage}\/(.*?)\/(.*?)$`);
    if (firstLevelTermUrl) {
      inputs = inputs.filter((input) => {
        return input.url_path == firstLevelTermUrl[1];
      });

      // Retrive the item from Level 3 as the response that we get from MDC starts
      // from level 2.
      // @todo Supercategory special case needs to verfied.
      let tempInputs = [];
      inputs.length && inputs[0].children && inputs[0].children.forEach((input, key) => {
        tempInputs[key] = input;
      });

      // Get the enrichment data. It's a sync call.
      let enrichmentData = rcsGetEnrichedCategories();
      innerHtmlObj.find('ul').append(buildLhnHtml('', tempInputs, clickable, unclickable, settings, enrichmentData));
    }
  }

  return innerHtmlObj.html();
}
