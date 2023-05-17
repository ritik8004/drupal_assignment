import React from 'react';
import parse from 'html-react-parser';
import { connectStateResults } from 'react-instantsearch-dom';
import {
  toggleSearchResultsContainer,
  toggleSortByFilter,
  toggleBlockCategoryFilter,
  updatePredictiveSearchContainer,
} from '../../utils';

const NoResults = ({
  searchResults, isSearchStalled, searching, searchingForFacetValues,
}) => {
  if (!searchResults || searchResults.nbHits > 0) {
    return null;
  }

  // For checking state of predictiveSearch.
  const { predictiveSearchEnabled } = drupalSettings.algoliaSearch;
  if (!searching && !isSearchStalled && !searchingForFacetValues) {
    toggleSearchResultsContainer();
    toggleSortByFilter('hide');
    toggleBlockCategoryFilter('hide');

    if (predictiveSearchEnabled) {
      // Hide result count and grid switcher block and
      // also update the SRP page title with Showing result for @keyword.
      updatePredictiveSearchContainer('hide', searchResults.query);
    }
  }

  // Trigger GTM for no results found.
  Drupal.algoliaReact.triggerSearchResultsUpdatedEvent(0);
  if (predictiveSearchEnabled) {
    return (
      <div className="hits-empty-state">
        <div className="view-empty">
          {parse(Drupal.t('Unfortunately, nothing matches your search. Please try another search term, or browse by category below.'))}
        </div>
      </div>
    );
  }

  return (
    <div className="hits-empty-state">
      <div className="view-empty">
        {parse(Drupal.t('Your search did not return any results.'))}
      </div>
    </div>
  );
};

export default connectStateResults(NoResults);
