import React from 'react';
import { connectStats } from 'react-instantsearch-dom';
import { showLoader } from '../../../../alshaya_algolia_react/js/src/utils';
import ProgressBar from '../../../../alshaya_algolia_react/js/src/components/algolia/widgets/ProgressBar';

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

const WishlistPagination = React.memo((props) => {
  const loadNextCotent = (e) => {
    e.preventDefault();
    e.persist();
    e.stopPropagation();

    showLoader();
    props.increasePagesToLoadByDefault();
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
            onClick={(e) => loadNextCotent(e)}
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
