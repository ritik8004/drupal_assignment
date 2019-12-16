import React from 'react';
import { connectStateResults } from 'react-instantsearch-dom';

const NoResults = ({ searchResults, isSearchStalled, searching }) => {
  if (searching || isSearchStalled) {
    return Drupal.t('fetching results...');
  }

  if (!searchResults || searchResults.nbHits > 0) {
    return null;
  }

  return (
    <div className="hits-empty-state">
      <div className="view-empty">
        {Drupal.t('Your search did not return any results.')}
      </div>
    </div>
  );
};

export default connectStateResults(NoResults);
