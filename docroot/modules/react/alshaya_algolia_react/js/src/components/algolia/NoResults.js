import React from 'react';
import { connectStateResults } from 'react-instantsearch-dom';
import { toggleSearchResultsContainer, toggleSortByFilter } from '../../utils';

const NoResults = ({ searchResults, isSearchStalled, searching, searchingForFacetValues }) => {
  if (!searchResults || searchResults.nbHits > 0) {
    return null;
  }

  if (!searching && !isSearchStalled && !searchingForFacetValues) {
    toggleSearchResultsContainer('show');
    toggleSortByFilter('hide');
  }

  // Trigger GTM for no results found.
  Drupal.algoliaReact.triggerGTMSearchResults(0);
  return (
    <div className="hits-empty-state">
      <div className="view-empty">
        {Drupal.t('Your search did not return any results.')}
      </div>
    </div>
  );
};

export default connectStateResults(NoResults);
