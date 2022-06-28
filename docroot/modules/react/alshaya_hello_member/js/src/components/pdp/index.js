import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { callHelloMemberApi } from '../../../../../js/utilities/helloMemberHelper';
import logger from '../../../../../js/utilities/logger';
import { getPriceToHelloMemberPoint, getDictionaryDataFromStorage } from '../../utilities';

class HelloMemberPDP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productPoints: null,
    };
  }

  async componentDidMount() {
    if (getDictionaryDataFromStorage() !== null) {
      this.setState({
        productPoints: this.getInitialProductPoints(),
      });
    } else {
      // If dictionary data does not exists in storage, we do api call.
      const response = await callHelloMemberApi('helloMemberGetDictionaryData', 'GET', { programCode: 'hello_member' });
      if (hasValue(response.data) && !hasValue(response.data.error)) {
        // Save dictionary data in local storage as it would remain constant.
        Drupal.addItemInLocalStorage('hello_member_pdp', response.data, 24 * 60 * 60);
        this.setState({
          productPoints: this.getInitialProductPoints(),
        });
      } else {
        // If coupon details API is returning Error.
        logger.error('Error while calling the dictonary data api, @message', {
          '@message': response.data.message,
        });
      }
    }

    // Update hello member points on variant select.
    document.addEventListener('onSkuVariantSelect', this.updateHelloMemberPoints, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onSkuVariantSelect', this.updateHelloMemberPoints, false);
  }

  /**
   * Utility function to get hello member product points for current product.
   */
  getInitialProductPoints = () => {
    // If above details are not there in props, proceed with usual approach to
    // get the data from price amount HTML text block.
    const selector = document.querySelector('.content__title_wrapper .special--price .price-amount') || document.querySelector('.content__title_wrapper .price-amount');
    // Fetch Product price using selector.
    const productPrice = (selector !== null) ? selector.innerText.replace(/,/g, '') : 0;

    // Return price as hello member points.
    this.setState({
      productPoints: getPriceToHelloMemberPoint(productPrice),
    });
  };

  /**
   * Utility function to update hello member points for variant selected.
   */
  updateHelloMemberPoints = (variantDetails) => {
    const { data } = variantDetails.detail;

    if (data.length !== 0) {
      this.setState({
        productPoints: getPriceToHelloMemberPoint(data.price),
      });
    }

    return null;
  };

  render() {
    const {
      productPoints,
    } = this.state;

    if (!hasValue(productPoints)) {
      return null;
    }

    return (
      <>
        <div className="hello-member-points">
          <p>{Drupal.t('Members Earn @pointValue points.', { '@pointValue': productPoints }, { context: 'hello_member' })}</p>
        </div>
      </>
    );
  }
}

export default HelloMemberPDP;
