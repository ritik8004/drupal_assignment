import React from 'react';
import { connectStats } from 'react-instantsearch-dom';

import ProgressBar from './widgets/ProgressBar';
import {
  showLoader, toggleSearchResultsContainer, toggleSortByFilter, toggleBlockCategoryFilter,
} from '../../utils';

// Stats with pagination.
const PaginationStats = connectStats(({ nbHits, currentResults }) => {
  toggleSearchResultsContainer();
  toggleSortByFilter('show');
  toggleBlockCategoryFilter('show');

  return (
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
  );
});

const Pagination = React.memo((props) => {
  const loadNextCotent = () => {
    showLoader();
    props.refineNext();
  };

  if (props.results > 0) {
    return (
      <ul className="js-pager__items pager">
        <li className="pager__item">
          <PaginationStats currentResults={props.results} />
        </li>
        {props.hasMore && (
          <li className="pager__item">
            <button
              className="button"
              type="submit"
              rel="next"
              onClick={() => loadNextCotent()}
            >
              {props.children}
            </button>
          </li>
        )}
      </ul>
    );
  }

  return (null);
}, (prevProps, nextProps) => (
  prevProps.results === nextProps.results
  && prevProps.hasMore === nextProps.hasMore
));

export default Pagination;
