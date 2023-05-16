import React from 'react';
import parse from 'html-react-parser';
import { connectStateResults } from 'react-instantsearch-dom';
import {
  toggleSearchResultsContainer,
  toggleSortByFilter,
  toggleBlockCategoryFilter,
} from '../../utils';

const NoResults = ({
  searchResults, isSearchStalled, searching, searchingForFacetValues,
}) => {
  if (!searchResults || searchResults.nbHits > 0) {
    return null;
  }

  if (!searching && !isSearchStalled && !searchingForFacetValues) {
    toggleSearchResultsContainer();
    toggleSortByFilter('hide');
    toggleBlockCategoryFilter('hide');
  }

  // Trigger GTM for no results found.
  Drupal.algoliaReact.triggerSearchResultsUpdatedEvent(0);

  return (
    <div className="hits-empty-state">
      <div className="view-empty">
        {parse(Drupal.t('Unfortunately, nothing matches your search. Please try another search term, or browse by category below.'))}
      </div>
    </div>
  );
};

export default connectStateResults(NoResults);
