import React from 'react';
import { showLoader } from '../../../../alshaya_algolia_react/js/src/utils';

const WishlistPagination = React.memo((props) => {
  const loadNextCotent = () => {
    showLoader();
    props.refineNext();
  };

  return (props.results > 0) ? (
    <ul className="js-pager__items pager">
      {props.hasMore && (
        <li className="pager__item">
          <button
            type="button"
            className="button"
            rel="next"
            onClick={() => loadNextCotent()}
          >
            {props.children}
          </button>
        </li>
      )}
    </ul>
  ) : (null);
}, (prevProps, nextProps) => (
  prevProps.results === nextProps.results && prevProps.hasMore === nextProps.hasMore
));

export default WishlistPagination;
