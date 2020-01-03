import React, { useRef, useEffect } from 'react';
import connectInfiniteHits from './connectors/connectInfiniteHits';
import Teaser from '../teaser';
import { getAlgoliaStorageValues, removeLoader } from '../../utils';

export default connectInfiniteHits(props => {
  const { hits, hasMore, refineNext } = props;
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  // Get height of each article and set the max height to all article tags.
  useEffect(
    () => {
      if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
        Drupal.blazyRevalidate();
        Drupal.algoliaReact.stickyfacetfilter();
        removeLoader();
        Drupal.refreshGrids();
        // Trigger back to search page.
        window.onpageshow = function(){
          var storage_value = getAlgoliaStorageValues();
          if (typeof storage_value !== 'undefined' && storage_value !== null) {
            Drupal.processBackToSearch(storage_value)
          }
        };
        // Trigger gtm event one time, only when search we have search results.
        if (hits.length > 0) {
          Drupal.algoliaReact.triggerGTMSearchResults(hits.length);
        }
      }
    }, [hits]
  );

  return (
    <React.Fragment>
      <div className="view-content" ref={teaserRef}>
        { hits.length > 0 ? hits.map(hit => <Teaser key={hit.objectID} hit={hit} />) : (null) }
      </div>
      { props.children({results: hits.length, hasMore: hasMore, refineNext: refineNext}) }
    </React.Fragment>
  );
});
