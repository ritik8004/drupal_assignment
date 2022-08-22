import React from 'react';
import Axios from 'axios';
import { connectMenu } from 'react-instantsearch-dom';
import { getFacetStorage, setFacetStorage } from '../../../../alshaya_algolia_react/js/src/utils/requests';

class Menu extends React.Component {
  constructor(props) {
    super(props);

    // Get attribute facet values from the storage if available and set the
    // wait state accordingly. If facet values are null, we need to wait for
    // values to be loaded in componentDidMount and render the component then.
    const { attributeAliase } = props;
    const facetValues = getFacetStorage(attributeAliase);
    this.state = {
      wait: !facetValues,
    };
  }

  componentDidMount = () => {
    // We will check if the wait is false means we have attribute facet data
    // in local storage and don't need to call an API.
    const { wait } = this.state;
    if (!wait) {
      return;
    }

    // If wait is true, means we don't have attribute facet data in local
    // storage so we need to call the API request to get data loaded in storage
    // and then update the wait state to load the component.
    const { attributeAliase } = this.props;
    Axios
      .get(Drupal.url(`facets-aliases/${attributeAliase}`))
      .then((response) => {
        setFacetStorage(attributeAliase, response.data);
        this.setState({ wait: false });
      });
  }

  /**
   * Function will prepare the attribute menu items with the provided target
   * category path and facet path aliases for rendering.
   */
  getAttributeMenuItems = () => {
    // Get the attribute and element from the props.
    const { items, element, attributeAliase } = this.props;

    // By default there won't be any redirects.
    let urlPathForMenuItem = '#';
    if (typeof element.dataset.targetUrl !== 'undefined'
      && element.dataset.targetUrl !== '') {
      // Get the target category path from the data attribute.
      urlPathForMenuItem = element.dataset.targetUrl;
    }

    // Get the attribute facet values from storage.
    const facetValues = getFacetStorage(attributeAliase);

    // Prepare the menu item with the facet alias path and target category path.
    let attributeMenuItems = null;
    attributeMenuItems = items.map((item) => {
      // Return if value doesn't exist.
      if (!facetValues[item.value.trim()]) {
        return null;
      }

      // Prepare the final menu item path with facet alias and value.
      const finalPathForMenuItem = `${urlPathForMenuItem}--${attributeAliase}-${facetValues[item.value.trim()]}`;
      return (
        <li key={item.label} className="shop-by-filter-attribute__list-item">
          <a
            href={finalPathForMenuItem}
          >
            {item.label}
          </a>
        </li>
      );
    });

    // Return the menu items.
    return attributeMenuItems;
  }

  render() {
    const { wait } = this.state;
    // Return null if the wait is true.
    if (wait) {
      return null;
    }

    // Get the attribute and element from the props.
    const { element } = this.props;

    return (
      <>
        <div className="shop-by-filter-attribute__label">{element.dataset.label}</div>
        <ul className="shop-by-filter-attribute__list">
          {this.getAttributeMenuItems()}
        </ul>
      </>
    );
  }
}

export default connectMenu(Menu);
