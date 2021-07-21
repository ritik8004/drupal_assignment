/**
 * Identify the RCS placeholders in the page.
 * Call the commerce backend to get the some data to put in the placeholder.
 * Cal the search backend to get some data to put in the placeholder.
 * Call the rendering engine to generate the HTML markup.
 *
 * For this to work, rcsPhCommerceBackend, rcsPhSearchEngine and
 * rcsPhRenderingEngine need to be set.
 */

/* global globalThis */

(function main($) {
  $(document).ready(function ready() {
    if (
      !drupalSettings.rcsProcessed &&
      typeof globalThis.rcsPhCommerceBackend !== 'undefined' &&
      // Currently we do not have the following configured.
      // typeof globalThis.rcsPhSearchBackend !== 'undefined' &&
      typeof globalThis.rcsPhRenderingEngine !== 'undefined'
      // typeof globalThis.rcsPhSearchRenderingEngine !== 'undefined'
    ) {
      globalThis.rcs_ph_context = 'browser';

      // @todo make this configurable.
      const langcode = drupalSettings.path.currentLanguage;

      // Retrieve overall page details if needed.
      globalThis.rcsPhCommerceBackend
        .getEntity(langcode)
        .then(entity => {
          // Process the block placeholders. This is async process, the
          // rendering engine is responsible of the entire processing and
          // replacement.
          $("[id^=rcs-ph-]").each(function eachBlockPh() {
            // Extract the placeholder ID.
            const blockPhRegex = /rcs-ph-([^"]+)/;
            const blockPhId = $(this)[0].id.match(blockPhRegex);

            // Extract the parameters.
            const params = [];
            $($(this)[0].attributes).each(function eachBlockPhAttributes() {
              const blockPhParamRegex = /data-param-([^"]+)/;
              const blockPhParamId = $(this)[0].name.match(
                blockPhParamRegex
              );

              if (blockPhParamId && blockPhParamId.length !== 0) {
                params[blockPhParamId[1]] = $(this)[0].value;
              }
            });

            let backend = globalThis.rcsPhCommerceBackend;
            let renderer = globalThis.rcsPhRenderingEngine;
            if (params.backend && params["backend"] === "search") {
              backend = globalThis.rcsPhSearchBackend;
              renderer = globalThis.rcsPhSearchRenderingEngine;
            }

            // Acquire data from the selected backend.
            backend
              .getData(
                blockPhId[1],
                params,
                entity,
                langcode,
                $(this)[0].innerHTML
              )
              .then(data => {
                if (data === null) {
                  return;
                }

                // Pass the data to the rendering engine.
                $(this).html(
                  renderer.render(
                    drupalSettings,
                    blockPhId[1],
                    params,
                    data,
                    entity,
                    langcode,
                    $(this)[0].innerHTML
                  )
                );

                // Re-attach all behaviors.
                rcsPhApplyDrupalJs($(this).parent());
              });
          });

          if (typeof drupalSettings.rcsPage !== 'undefined') {
            // Hard coded list of html attributes which we need to parse to
            // find field placeholders.
            const attributes = drupalSettings.rcsPhSettings.placeholderAttributes;

            // Identify all the field placeholders and get the replacement
            // value. Parse the html to find all occurrences at apply the
            // replacement.
            rcsPhReplaceEntityPh(document.documentElement.innerHTML, drupalSettings.rcsPage.type, entity, langcode)
              .forEach(function eachReplacement(r) {
                const fieldPh = r[0];
                const entityFieldValue = r[1];

                // Apply the replacement on all the elements containing the
                // placeholder. We filter to keep only the child element
                // and not the parent ones.
                $(`:contains('${fieldPh}')`)
                  .filter(function entityPhSelector() {
                    return $(this).children().length === 0;
                  })
                  .each(function eachEntityPhReplace() {
                    $(this).html(
                      $(this)
                        .html()
                        .replace(fieldPh, entityFieldValue)
                    );
                  });

                //":contains" only returns the elements for which the
                // placeholder is part of the content, it won't return the
                // elements for which the placeholder is part of the
                // attribute values. We are now fetching all the elements
                // which have placeholders in the attributes and we
                // apply the replacement.
                for (const attribute of attributes) {
                  $(`[${ attribute } *= '${ fieldPh }']`)
                    .each(function eachEntityPhAttributeReplace() {
                      $(this).attr(attribute, $(this).attr(attribute).replace(fieldPh, entityFieldValue));
                    });
                }
              });
          }

          // Re-attach all behaviors.
          rcsPhApplyDrupalJs(document);
        });
    }
  });

  // Process the Drupal.t markup from middleware.
  // For direct access we should not use this class.
  // This is added for middleware in rcsTranslatedText().
  $('.rcs-drupal-t').each((index, item) => {
    $(item).replaceWith(Drupal.t(
      $(item).attr('data-str'),
      JSON.parse(unescape($(item).attr('data-args'))),
      JSON.parse(unescape($(item).attr('data-options'))),
    ));
  });
})(jQuery, Drupal, drupalSettings);
