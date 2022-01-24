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

  var pageEntity = null;
  const classRcsLoaded = 'rcs-loaded';

  $(document).ready(function ready() {
    if (
      !drupalSettings.rcsProcessed &&
      typeof globalThis.rcsPhCommerceBackend !== 'undefined' &&
      typeof globalThis.rcsPhRenderingEngine !== 'undefined'
    ) {
      globalThis.rcs_ph_context = 'browser';

      // Process the block placeholders. This is async process, the
      // rendering engine is responsible of the entire processing and
      // replacement.
      $("[id^=rcs-ph-][data-rcs-dependency='none']").once('rcs-ph-process').each(eachBlockPh);

      // Retrieve overall page details if needed.
      globalThis.rcsPhCommerceBackend
        .getEntity(drupalSettings.path.currentLanguage)
        .then(entity => {
          pageEntity = entity;

          // Process the block placeholders. This is async process, the
          // rendering engine is responsible of the entire processing and
          // replacement.
          $("[id^=rcs-ph-]").once('rcs-ph-process').each(eachBlockPh);

          const pageType = rcsPhGetPageType();
          if (pageType) {
            const attributes = rcsPhGetSetting('placeholderAttributes');

            // Identify all the field placeholders and get the replacement
            // value. Parse the html to find all occurrences at apply the
            // replacement.
            rcsPhReplaceEntityPh(document.documentElement.innerHTML, pageType, entity, drupalSettings.path.currentLanguage)
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

          // RCS Entity Loaded.
          if (pageType) {
            RcsEventManager.fire('alshayaPageEntityLoaded', {
              detail: {
                pageType,
                entity,
              }
            });
          }

          // Add class to remove loader styles after RCS info is filled.
          $('.rcs-page').addClass(classRcsLoaded);
        });
    }
  });

  // Process the Drupal.t markup from middleware.
  // For direct access we should not use this class.
  $('.rcs-drupal-t').each((index, item) => {
    $(item).replaceWith(Drupal.t(
      $(item).attr('data-str'),
      JSON.parse(unescape($(item).attr('data-args'))),
      JSON.parse(unescape($(item).attr('data-options'))),
    ));
  });

  function eachBlockPh() {
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
      if (typeof globalThis.rcsPhSearchBackend === 'undefined'
        || typeof globalThis.rcsPhSearchRenderingEngine === 'undefined') {
        console.log('Search backend not available but used.');
        return '';
      }
      backend = globalThis.rcsPhSearchBackend;
      renderer = globalThis.rcsPhSearchRenderingEngine;
    }

    // Allow specifying separate entity than the block id.
    const entityToGet = params['entity-to-get'] || blockPhId[1];

    params['get-data'] = params['get-data'] === "true";

    // Acquire data from the selected backend.
    backend
      .getData(
        entityToGet,
        params,
        pageEntity,
        drupalSettings.path.currentLanguage,
        $(this)[0].innerHTML
      )
      .then(data => {
        if (!params["get-data"] || data) {
          try {
            // Pass the data to the rendering engine.
            $(this).html(
              renderer.render(
                drupalSettings,
                blockPhId[1],
                params,
                data,
                pageEntity,
                drupalSettings.path.currentLanguage,
                $(this)[0].innerHTML
              )
            );

            // Add class to remove loader styles on RCS Placeholders.
            $(this).addClass(classRcsLoaded);
            // Re-attach all behaviors.
            rcsPhApplyDrupalJs($(this).parent()[0]);
            return;
          } catch (error) {
            Drupal.alshayaLogger(
              "error",
              "Error occurred while rendering block of ID @blockId - @error",
              {
                "@blockId": blockPhId[1],
                "@error": error,
              }
            );
          }
        }

        // Add class to remove loader styles on RCS Placeholders.
        $(this).addClass(classRcsLoaded);
      });
  }

})(jQuery, Drupal, drupalSettings);
