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
    const { attributeAlias } = props;
    const facetValues = getFacetStorage(attributeAlias);
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
    const { attributeAlias } = this.props;
    Axios
      .get(Drupal.url(`facets-aliases/${attributeAlias}?cacheable=1`))
      .then((response) => {
        setFacetStorage(attributeAlias, response.data);
        this.setState({ wait: false });
      });
  }

  /**
   * Convert the fractional values to float numbers.
   *
   * @param {string} fractionalValue
   *  Fractional values like '32 1/3' or '2/3' etc.
   *
   * @returns
   *  Return a float conversion of the provided fractional value.
   */
  convertFractional = (fractionalValue) => {
    // Spit the fractional value with space. If string contains the full
    // fractional value like '32 2/3'.
    const [fractionalVal, ...restFractionVal] = fractionalValue.split(' ');
    const restFractionValString = restFractionVal.join(' ').trim();

    // This contains 32 after spliting the above number. If it's a simple
    // fractional number like '2/3', prefix will remain to 0;
    const prefixNumber = (restFractionValString.length > 0)
      ? parseInt(fractionalVal, 10)
      : 0;

    // This contains the parts of fractional values like '2/3' etc.
    let fractionalSplit = fractionalVal.split('/');
    if (restFractionValString.length > 0) {
      fractionalSplit = restFractionValString.split('/');
    }

    // This contains the actual float conversion of fractional value.
    const suffixNumber = (fractionalSplit.length > 1)
      ? parseFloat(fractionalSplit[0] / fractionalSplit[1])
      : parseFloat(fractionalSplit[0]);

    // Return the final float values of given fractional string.
    // Only add suffixNumber, if it's a numeric value.
    return Number.isNaN(suffixNumber)
      ? prefixNumber
      : (prefixNumber + suffixNumber);
  }

  /**
   * Function will prepare the attribute menu items with the provided target
   * category path and facet path aliases for rendering.
   */
  getAttributeMenuItems = () => {
    // Get the attribute and element from the props.
    const { items, element, attributeAlias } = this.props;

    // Iterate algolia data and add a newValue key converting fractional to
    // float values so we can sort the options based on that in a new array.
    const itemsToRender = [];
    items.forEach((item) => {
      itemsToRender.push({
        ...item,
        newValue: this.convertFractional(item.value.trim()),
      });
    });

    // Sort the new renderable array based on the newValue key added above.
    itemsToRender.sort((first, second) => (first.newValue - second.newValue));

    // By default there won't be any redirects.
    let urlPathForMenuItem = '#';
    if (typeof element.dataset.targetUrl !== 'undefined'
      && element.dataset.targetUrl !== '') {
      // Get the target category path from the data attribute.
      urlPathForMenuItem = element.dataset.targetUrl;
    }

    // Get the attribute facet values from storage.
    const facetValues = getFacetStorage(attributeAlias);

    // Prepare the menu item with the facet alias path and target category path.
    let attributeMenuItems = null;
    attributeMenuItems = itemsToRender.map((item) => {
      // If value doesn't exist, we will log it and return.
      if (!facetValues[item.value.trim()]) {
        Drupal.alshayaLogger('warning', 'Facet alias does not exist: @attributeAlias - @itemValue', {
          '@attributeAlias': attributeAlias,
          '@itemValue': item.value,
        });
        return null;
      }

      // Prepare the final menu item path with facet alias and value.
      const finalPathForMenuItem = `${urlPathForMenuItem}--${attributeAlias}-${facetValues[item.value.trim()]}`;
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
