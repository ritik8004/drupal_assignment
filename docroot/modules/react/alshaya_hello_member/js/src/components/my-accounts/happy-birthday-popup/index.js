import React from 'react';
import Popup from 'reactjs-popup';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../js/utilities/strings';
import { formatDate } from '../../../utilities';

class HappyBirthdayPopup extends React.Component {
  constructor(props) {
    super(props);
    const { myBenefitsList } = this.props;
    this.data = myBenefitsList.find((obj) => obj.type === 'HAPPY_BIRTHDAY');
    this.state = {
      isModelOpen: !!this.data,
    };
  }

  /**
  * Checks if popup was shown earlier for the current date.
  * */
  isAlreadyShown = () => {
    let expires = new Date();
    // Localstorage should be cleared at end of current date.
    expires.setHours(23, 59, 59, 999);

    // Get Remaining time in current day in secs.
    expires = (expires - new Date()) / 1000;

    // Check if LocalStorage is available.
    if (Drupal.getItemFromLocalStorage(`hello_member_happy_birthday_benefit_${drupalSettings.user.uid}`)) {
      // Return true if localStorage is availble.
      return true;
    }

    // Create a LocalStorage with current user id.
    // Using userid in storage key, to incorporate,
    // Multiple users logging in with the same browser.
    Drupal.addItemInLocalStorage(
      `hello_member_happy_birthday_benefit_${drupalSettings.user.uid}`,
      true,
      expires,
    );

    // Return false as localstorage was not availble.
    return false;
  };

  /**
   * Close modal popup.
   * */
  closeModal = () => {
    this.setState({
      isModelOpen: false,
    });
    // Push popup close event to gtm.
    if (hasValue(this.data)) {
      Drupal.alshayaSeoGtmPushBirtdayPopupClose(this.data.category_name);
    }
  };

  /**
   * Tracking voucher click to gtm.
   * */
  trackVoucherClick = () => {
    // Push popup close event to gtm.
    if (hasValue(this.data)) {
      Drupal.alshayaSeoGtmPushBirtdayPopupClick(this.data.category_name);
    }
  }

  render() {
    const { isModelOpen } = this.state;
    const { uid } = drupalSettings.user;
    if (typeof this.data === 'undefined') {
      return null;
    }
    // Check if birthday popup was shown earlier for current date.
    if (this.isAlreadyShown()) {
      return null;
    }

    // Push birthday pop display to gtm.
    if (isModelOpen) {
      Drupal.alshayaSeoGtmPushBirthdayPopupView(this.data.category_name);
    }

    return (
      <Popup
        open={isModelOpen}
        className="hello_member_happy_birthday_popup"
        closeOnDocumentClick={false}
        closeOnEscape={false}
      >
        <div className="hello-member-happy-birthday-popup">
          <div className="happy-birthday-popup-header">
            <button type="button" className="close" onClick={() => this.closeModal()} />
            <div className="image-container">
              <img src={this.data.small_image} />
            </div>
          </div>
          <div className="happy-birthday-popup-body">
            <div className="title">
              { this.data.category_name }
            </div>
            <div className="description">
              { this.data.description }
            </div>
            <div className="happy-birthday-voucher-details" onClick={() => this.trackVoucherClick()}>
              <a className="avail-it" key={this.data.id || this.data.code} href={`${Drupal.url(`user/${uid}`)}/hello-member-benefits/${hasValue(this.data.id) ? `coupon/${this.data.id}` : `offer/${this.data.code}`}`}>
                { Drupal.t('REDEEM IT', {}, { context: 'hello_member' }) }
              </a>
            </div>
            <div className="expiry">
              {getStringMessage('benefit_expire', { '@expire_date': formatDate(new Date(this.data.expiry_date || this.data.end_date)) })}
            </div>
          </div>
        </div>
      </Popup>
    );
  }
}

export default HappyBirthdayPopup;
