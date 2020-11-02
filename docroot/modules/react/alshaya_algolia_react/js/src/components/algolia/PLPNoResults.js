import { connectStateResults } from 'react-instantsearch-dom';

const PLPNoResults = ({ searchResults }) => {
  if (!searchResults || searchResults.nbHits > 0) {
    return null;
  }
  // Trigger GTM for no results found.
  Drupal.algoliaReactPLP.triggerResultsUpdatedEvent(0);
  return (null);
};

export default connectStateResults(PLPNoResults);
