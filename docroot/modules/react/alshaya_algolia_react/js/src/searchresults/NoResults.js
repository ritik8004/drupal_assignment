import React from 'react';
import { connectStateResults, ClearRefinements } from 'react-instantsearch-dom';

const NoResults = ({ searchResults }) => {
  if (!searchResults || searchResults.nbHits > 0) {
    return null;
  }

  const clearRefinements = searchResults.getRefinements().length > 0
    ? (<ClearRefinements
      translations={{
        reset: (
          <div className="clear-filters">
            Clear filters
          </div>
        ),
      }}
    />)
    : (null);

  return (
    <div className="hits-empty-state">
      <div class="view-empty">
        Your search did not return any results.
      </div>
      {clearRefinements}
    </div>
  );
};

export default connectStateResults(NoResults);
