import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../js/utilities/logger';
import { getHelloMemberDictionaryData } from '../../hello_member_api_helper';
import { getPriceToHelloMemberPoint } from '../../utilities';

class HelloMemberPDP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productPoints: null,
      dictionaryData: null,
    };
  }

  async componentDidMount() {
    // If dictionary data does not exists in storage, we do api call.
    const requestData = {
      type: 'HM_ACCRUAL_RATIO',
      programCode: 'hello_member',
    };
    const response = await getHelloMemberDictionaryData(requestData);
    if (hasValue(response.data) && !hasValue(response.data.error)) {
      this.setState({
        dictionaryData: response.data,
      }, () => {
        this.setInitialProductPoints();
      });
    } else {
      // If coupon details API is returning Error.
      logger.error('Error while calling the dictonary data api, @message', {
        '@message': response.data.message,
      });
    }

    // Update hello member points on variant select.
    document.addEventListener('onSkuVariantSelect', this.updateHelloMemberPoints, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onSkuVariantSelect', this.updateHelloMemberPoints, false);
  }

  /**
   * Utility function to set hello member product points for current product.
   */
  setInitialProductPoints = () => {
    // Get the product SKU fom the form tag.
    const productSku = document.querySelector('.sku-base-form').dataset.sku;

    // Get product data from the drupal settings for the given SKU.
    const productData = window.commerceBackend.getProductData(productSku);
    if (!productData) {
      return;
    }

    // Get the current selected product variant in form.
    const selectedVariant = document.getElementsByName('selected_variant_sku')[0].value;
    if (!selectedVariant) {
      return;
    }

    // Get selected variant price from the product data.
    const productPrice = productData.variants[selectedVariant].finalPrice || 0;

    // Return price as hello member points.
    const { dictionaryData } = this.state;
    this.setState({
      productPoints: getPriceToHelloMemberPoint(productPrice, dictionaryData),
    });
  };

  /**
   * Utility function to update hello member points for variant selected.
   */
  updateHelloMemberPoints = (variantDetails) => {
    const { dictionaryData } = this.state;
    const { data } = variantDetails.detail;

    if (data.length !== 0) {
      this.setState({
        productPoints: getPriceToHelloMemberPoint(data.price, dictionaryData),
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
