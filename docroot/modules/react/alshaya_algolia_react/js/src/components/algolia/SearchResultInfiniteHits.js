import React, { useRef, useEffect } from 'react'
import connectInfiniteHits from './connectors/connectInfiniteHits';

import Teaser from '../teaser';
import { updateAfter, removeLoader } from '../../utils';

export default connectInfiniteHits(props => {
  const { hits, hasMore, refineNext } = props;
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  // Get height of each article and set the max height to all article tags.
  useEffect(
    () => {
      setTimeout(() => {
        Drupal.blazyRevalidate();
        Drupal.algoliaReact.stickyfacetfilter();
        removeLoader();
      }, updateAfter);
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
