import React from 'react';
import ReactDOM from 'react-dom';
import algoliasearch from 'algoliasearch/lite';
import {
  InstantSearch,
  SearchBox,
  Hits,
  Configure,
  RefinementList
} from 'react-instantsearch-dom';
import PropTypes from 'prop-types';
import {searchClient} from './Config/SearchClient'

class App extends React.Component {
  render() {
    return (
      <div className="ais-InstantSearch">
        <h1>React InstantSearch e-commerce demo</h1>
        <InstantSearch indexName={drupalSettings.algoliaSearch.indexName} searchClient={searchClient}>
          <Configure hitsPerPage={10} />
          <div className="right-panel">
            <SearchBox />
            <Hits hitComponent={Hit}/>
          </div>
        </InstantSearch>
      </div>
    );
  }
}

function Hit(props) {
  return (
    <div>
      <div className="title">{props.hit.title}</div>
    </div>
  );
}

Hit.propTypes = {
  hit: PropTypes.object.isRequired,
};

ReactDOM.render(
  <App />,
  document.querySelector('#alshaya-algolia-search')
);
