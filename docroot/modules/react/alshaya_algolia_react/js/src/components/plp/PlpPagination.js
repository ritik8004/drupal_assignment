import React from 'react';
import { connectStats } from 'react-instantsearch-dom';

import ProgressBar from '../algolia/widgets/ProgressBar';
import { showLoader } from '../../utils';

// Stats with pagination.
const PaginationStats = connectStats(({ nbHits, currentResults }) => (
  <>
    <span className="ais-Stats-text">
      {
        Drupal.t('showing @current of @total items', {
          '@current': currentResults,
          '@total': nbHits,
        })
      }
    </span>
    <ProgressBar completed={((currentResults * 100) / nbHits)} />
  </>
));

const PlpPagination = React.memo((props) => {
  const loadNextCotent = () => {
    showLoader();
    props.refineNext();
  };

  return (props.results > 0) ? (
    <ul className="js-pager__items pager">
      <li className="pager__item">
        <PaginationStats currentResults={props.results} />
      </li>
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

export default PlpPagination;
