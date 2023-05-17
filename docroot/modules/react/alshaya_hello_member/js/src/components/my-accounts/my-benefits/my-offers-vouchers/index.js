import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { isMobile } from '../../../../../../../js/utilities/display';
import { formatDate } from '../../../../utilities';
import { sortBenefits } from '../../../../../../../js/utilities/helloMemberHelper';

const { showMoreLimit } = drupalSettings.helloMember;
class MyOffersAndVouchers extends React.Component {
  constructor(props) {
    super(props);
    const display = isMobile();
    const viewAllBenefits = new URLSearchParams(window.location.search).get('view-all-benefits');

    this.state = {
      expanded: viewAllBenefits || display,
    };
  }

  handleShowMoreClick = () => {
    const { expanded } = this.state;
    this.setState({
      expanded: !expanded,
    });
    // Push show more click data to gtm.
    Drupal.alshayaSeoGtmPushBenefitShowmore(expanded);
  }

  getShowBenefitsLink = () => {
    const { currentPath } = drupalSettings.path;
    const { uid } = drupalSettings.user;
    const { myBenefitsList } = this.props;

    if (currentPath === 'home') {
      return (
        <a className="view-all-benefits" href={`${Drupal.url(`user/${uid}?view-all-benefits=true`)}`}>
          {getStringMessage('view_all_benefits')}
        </a>
      );
    }
    if (myBenefitsList.length > showMoreLimit) {
      const { expanded } = this.state;
      return (
        <a onClick={() => this.handleShowMoreClick()}>
          {expanded ? getStringMessage('show_less') : getStringMessage('show_all')}
        </a>
      );
    }
    return '';
  };

  /**
   * Function to return true or false based on the availability of 'I' tag from the list.
   *
   * @param {array} myBenefitsList
   * @param {string} tag
   * @returns {boolean} true if the tag is available
   */
  isInfoTagAvailable = (myBenefitsList, tag) => myBenefitsList.some((el) => el.tag === tag);

  render() {
    const { myBenefitsList } = this.props;
    // Check if the list has 'I' tag available.
    const isInfoTypeAvailable = this.isInfoTagAvailable(myBenefitsList, 'I');
    const benefitList = isInfoTypeAvailable ? sortBenefits(myBenefitsList) : myBenefitsList;
    const { expanded } = this.state;
    const dataForDisplay = expanded ? benefitList : benefitList.slice(0, showMoreLimit);
    const { uid } = drupalSettings.user;
    return (
      <div className="my-benefits-wrapper">
        {dataForDisplay.map((data) => (
          <a className="my-offers-vouchers-details" key={data.id || data.code} href={`${Drupal.url(`user/${uid}`)}/hello-member-benefits/${hasValue(data.id) ? `coupon/${data.id}` : `offer/${data.code}`}`}>
            <div className="image-container">
              <img src={data.small_image} />
            </div>
            <div className="voucher-wrapper">
              <div className="title">
                {data.category_name}
              </div>
              <div className="info">
                {data.description}
              </div>
              {(!hasValue(data.tag) || (data.tag !== 'I'))
                && (
                <div className="expiry">
                  {getStringMessage('benefit_expire', { '@expire_date': formatDate(new Date(data.expiry_date || data.end_date)) })}
                </div>
                )}
            </div>
          </a>
        ))}
        <div className="btn-wrapper">
          { this.getShowBenefitsLink()}
        </div>
      </div>
    );
  }
}

export default MyOffersAndVouchers;
