import React, { useRef, useEffect } from 'react';
import connectInfiniteHits from '../algolia/connectors/connectInfiniteHits';
import Teaser from '../teaser';
import { removeLoader } from '../../utils';

const PlpResultInfiniteHits = connectInfiniteHits(({
  hits, hasMore, refineNext, children = null, gtmContainer, pageType,
}) => {
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  useEffect(
    () => {
      if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
        if (hits.length > 0) {
          Drupal.blazyRevalidate();
          Drupal.algoliaReactPLP.stickyfacetfilter();
          Drupal.refreshGrids();
          // Trigger gtm event one time, only when search we have search results.
          Drupal.algoliaReactPLP.triggerResultsUpdatedEvent(hits);

          // Trigger back to search page.
          setTimeout(Drupal.processBackToPLP, 10);
        }

        removeLoader();
      }
    }, [hits],
  );

  let groupEnabled = false;
  const results = [];
  const items = [];
  const { subCategories } = drupalSettings.algoliaSearch;
  if (subCategories !== undefined) {
    groupEnabled = true;
    Object.keys(subCategories).forEach((key) => {
      const categoryField = subCategories[key].category.category_field;
      const { hierarchy } = subCategories[key].category;

      items[key] = [];
      // Check to match the items with sub categories
      Object.keys(hits).forEach((index) => {
        const level = categoryField.split('.')[1];
        const field = categoryField.split('.')[0];
        const hierarchies = hits[index][field][level];
        if (hierarchies.includes(hierarchy)) {
          items[key].push(hits[index]);
        }
      });

      // Unset sub category if hits is empty.
      if (items[key].length !== 0) {
        // Creating the array with sub category key.
        results[key] = {};
        results[key].title = subCategories[key].title;
        results[key].desc = subCategories[key].description;
        results[key].hits = [];
        results[key].hits = items[key];
      }
    });
  }

  return (!groupEnabled) ? (
    <>
      <div className="view-content" ref={teaserRef}>
        { hits.length > 0
          ? hits.map((hit) => (
            <Teaser
              key={hit.objectID}
              hit={hit}
              gtmContainer={gtmContainer}
              pageType={pageType}
            />
          ))
          : (null)}
      </div>
      {children && children({
        results: hits.length,
        hasMore,
        refineNext,
      })}
    </>
  ) : (
    <div className="group-enabled">
      {Object.keys(results).map((key) => (
        <>
          <div className="sub-category-title-block">
            <p className="sub-category-title">{results[key].title}</p>
            <p className="sub-category-desc">{results[key].desc}</p>
          </div>
          <div className="view-content" ref={teaserRef}>
            { results[key].hits.length > 0
              ? results[key].hits.map((hit) => (
                <Teaser
                  key={hit.objectID}
                  hit={hit}
                  gtmContainer={gtmContainer}
                  pageType={pageType}
                />
              ))
              : (null)}
          </div>
        </>
      ))}
      {children && children({
        results: hits.length,
        hasMore,
        refineNext,
      })}
    </div>
  );
});

export default PlpResultInfiniteHits;
