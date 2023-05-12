import React from 'react';
import { connectStats } from 'react-instantsearch-dom';

import ProgressBar from './widgets/ProgressBar';
import {
  showLoader,
  toggleSearchResultsContainer,
  toggleSortByFilter,
  toggleBlockCategoryFilter,
  updatePredictiveSearchContainer,
} from '../../utils';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

// Stats with pagination.
const PaginationStats = connectStats(({ nbHits, currentResults }) => {
  toggleSearchResultsContainer();
  toggleSortByFilter('show');
  toggleBlockCategoryFilter('show');
  updatePredictiveSearchContainer('show');

  return (
    <>
      <span
        className="ais-Stats-text"
        gtm-pagination-stats={`showing ${currentResults} of ${nbHits} items`}
      >
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

const Pagination = ({
  refineNext, results, hasMore, children,
}) => {
  const loadNextContent = (e) => {
    e.preventDefault();
    e.persist();
    e.stopPropagation();

    showLoader();
    refineNext(e);
  };

  return (
    <ConditionalView condition={results > 0}>
      <ul className="js-pager__items pager">
        <li className="pager__item">
          <PaginationStats currentResults={results} />
        </li>

        <ConditionalView condition={hasMore}>
          <li className="pager__item">
            <button
              type="button"
              className="button"
              rel="next"
              onClick={(e) => loadNextContent(e)}
            >
              {children}
            </button>
          </li>
        </ConditionalView>
      </ul>
    </ConditionalView>
  );
};

export default Pagination;
