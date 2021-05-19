import React, { useRef, useEffect } from 'react';
import connectInfiniteHits from './connectors/connectInfiniteHits';
import Teaser from '../teaser';
import { getAlgoliaStorageValues, removeLoader } from '../../utils';

export default connectInfiniteHits(({
  hits, hasMore, refineNext, pageNumber, pageType, children,
}) => {
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  useEffect(
    () => {
      if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
        if (hits.length > 0) {
          Drupal.blazyRevalidate();
          Drupal.algoliaReact.stickyfacetfilter();
        }
        removeLoader();
        // Trigger back to search page.
        window.onpageshow = () => {
          const storageValue = getAlgoliaStorageValues();
          if (typeof storageValue !== 'undefined' && storageValue !== null) {
            Drupal.processBackToSearch(storageValue);
          }
        };
        // Trigger gtm event one time, only when search we have search results.
        if (hits.length > 0) {
          Drupal.algoliaReact.triggerSearchResultsUpdatedEvent(hits.length);
        }
      }
    }, [hits],
  );

  return (
    <>
      <div className="view-content" ref={teaserRef}>
        { hits.length > 0
          ? hits.map((hit) => (
            <Teaser
              key={hit.objectID}
              hit={hit}
              pageType={pageType}
              pageNumber={pageNumber}
            />
          ))
          : (null)}
      </div>
      { children && children({ results: hits.length, hasMore, refineNext }) }
    </>
  );
});
