import React, { useRef, useEffect } from 'react'
import connectInfiniteHits from './connectors/connectInfiniteHits';

import Teaser from '../teaser';
import { updateAfter } from '../../utils';

export default connectInfiniteHits(props => {
  const { hits, hasMore, refineNext } = props;
  // Create ref to get element after it gets rendered.
  const teaserRef = useRef();

  // Get height of each article and set the max height to all article tags.
  useEffect(
    () => {
      setTimeout(() => {
        if (typeof teaserRef.current === 'object' && teaserRef.current !== null) {
          var elements = teaserRef.current.getElementsByTagName('article');
          if (elements.length > 0) {
            Array.prototype.forEach.call(elements, element => {
              element.parentElement.style.height = '';
            });

            var heights = [];
            Array.prototype.forEach.call(elements, element => heights.push(element.parentElement.offsetHeight));
            var maxheight = Math.max(...heights);

            if (maxheight > 0) {
              Array.prototype.forEach.call(elements, element => {
                element.parentElement.style.height = maxheight + 'px'; //= maxheight;
              });
            }
          }
          Drupal.algoliaReact.stickyfacetfilter();
        }
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
