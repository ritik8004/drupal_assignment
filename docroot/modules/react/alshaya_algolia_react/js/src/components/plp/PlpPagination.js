import React from 'react';
import { connectStats } from 'react-instantsearch-dom';

import ProgressBar from '../algolia/widgets/ProgressBar';
import { showLoader } from '../../utils';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

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

const PlpPagination = ({
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

export default PlpPagination;
