import React from 'react'
import { connectStats } from 'react-instantsearch-dom';

import ProgressBar from './widgets/ProgressBar';
import { showLoader } from '../../utils';

// Stats with pagination.
const PaginationStats = connectStats(({nbHits, currentResults}) => {
  return (
    <React.Fragment>
      <span className="ais-Stats-text">
        {
          Drupal.t('showing @current of @total items', {
            '@current': currentResults,
            '@total': nbHits
          })
        }
      </span>
      <ProgressBar completed={((currentResults * 100)/nbHits)}/>
    </React.Fragment>
  );
});

export default function Pagination(props) {

  const loadNextCotent = () => {
    // const bodyNode = document.getElementsByTagName( 'body' );
    showLoader();
    props.refineNext();
  }

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
  else {
    return (null);
  }
}
