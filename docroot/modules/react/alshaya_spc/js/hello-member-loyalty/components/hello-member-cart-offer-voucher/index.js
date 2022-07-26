import React from 'react';
import Popup from 'reactjs-popup';
import {
  Tab,
  Tabs,
  TabList,
  TabPanel,
} from 'react-tabs';


class HelloMemberCartOffersVouchers extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false,
    };
  }

  // Toggle to set state for popup.
  // getOffersVouchers = () => {
  //   const params = getHelloMemberCustomerInfo();
  //   // Get coupons list.
  //   //const couponResponse = callHelloMemberApi('helloMemberCouponsList', 'GET', params);
  //   // Get offers list.
  //   //const offerResponse = callHelloMemberApi('helloMemberOffersList', 'GET', params);
  // };

  // Toggle to set state for popup.
  togglePopup = (openModal) => {
    this.setState({
      openModal,
    });
  };

  render() {
    const {
      openModal,
    } = this.state;
    return (
      <>
        <div className="hello-member-promo-section">
          <a className="hm-promo-pop-link" onClick={() => this.togglePopup(true)}> 
            Discounts & Vouchers 
            <span className="promo-notification" />
          </a>
          <div className="popup-container">
            <Popup
              open={openModal}
              closeOnDocumentClick={false}
              closeOnEscape={false}
            >
              <a className="close-modal" onClick={() => this.togglePopup(false)} />
              <div className="hm-promo-modal-title">Discount</div>
              <div className="hm-promo-modal-content">
                <div className="error-info-section">&nbsp;</div>
                <Tabs>
                  <TabList>
                    <Tab>Bonus Vouchers</Tab>
                    <Tab>Member Offers</Tab>
                  </TabList>

                  <TabPanel>
                    <form
                      className="egift-validate-form"
                      method="post"
                      id="egift-val-form"
                      onSubmit={this.handleSubmit}
                    >
                      <div className="hm-promo-tab-content-list">
                        <div className="hm-promo-tab-cont-item">
                          <input type="checkbox" id="vehicle1" value="Bike" />
                          <label htmlFor="vehicle1" className="checkbox-sim checkbox-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t(' I have a bike')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                        <div className="hm-promo-tab-cont-item">
                          <input type="checkbox" id="vehicle2" value="Car" />
                          <label htmlFor="vehicle2" className="checkbox-sim checkbox-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t(' I have a car')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                        <div className="hm-promo-tab-cont-item">
                          <input type="checkbox" id="vehicle3" value="Boat" />
                          <label htmlFor="vehicle3" className="checkbox-sim checkbox-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t(' I have a boat')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                        <div className="hm-promo-tab-cont-item">
                          <input type="checkbox" id="vehicle4" value="Boat" />
                          <label htmlFor="vehicle4" className="checkbox-sim checkbox-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t(' I have a flight')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                      </div>
                      <div className="hm-promo-tab-cont-action">
                        <input type="submit" value="apply vouchers" />
                        <a href="" className="clear-btn">{Drupal.t('clear all')}</a>
                      </div>
                    </form>
                  </TabPanel>
                  <TabPanel>
                    <form
                      className="egift-validate-2-form"
                      method="post"
                      id="egift-val-form-2"
                      onSubmit={this.handleSubmit}
                    >
                      <div className="hm-promo-tab-content-list radio-btn-list">
                        <div className="hm-promo-tab-cont-item">
                          <input type="radio" id="html" name="fav_language" value="HTML" />
                          <label htmlFor="html" className="radio-sim radio-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t('HTML')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                        <div className="hm-promo-tab-cont-item">
                          <input type="radio" id="css" name="fav_language" value="CSS" />
                          <label htmlFor="css" className="radio-sim radio-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t('CSS')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                        <div className="hm-promo-tab-cont-item">
                          <input type="radio" id="javascript" name="fav_language" value="JavaScript" />
                          <label htmlFor="javascript" className="radio-sim radio-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t('Javascript')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                        <div className="hm-promo-tab-cont-item">
                          <input type="radio" id="react" name="fav_language" value="React" />
                          <label htmlFor="react" className="radio-sim radio-label">
                            <div className="item-title">
                              <span className="title-text">{Drupal.t('React')}</span>
                              <span className="item-sub-title">Expires</span>
                            </div>
                          </label>
                        </div>
                      </div>
                      <div className="hm-promo-tab-cont-action">
                        <input type="submit" value="apply offers" />
                        <a href="" className="clear-btn">{Drupal.t('clear all')}</a>
                      </div>
                    </form>
                  </TabPanel>
                </Tabs>
              </div>
            </Popup>
          </div>
        </div>
      </>
    );
  }
}

export default HelloMemberCartOffersVouchers;
