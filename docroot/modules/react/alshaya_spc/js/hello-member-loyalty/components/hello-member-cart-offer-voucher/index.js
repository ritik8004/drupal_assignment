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
        <a className="close-modal" onClick={() => this.togglePopup(true)}> Discounts & Vouchers </a>
        <div className="popup-container">
          <Popup
            open={openModal}
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <a className="close-modal" onClick={() => this.togglePopup(false)}> close </a>
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
                  <input type="checkbox" id="vehicle1" value="Bike" />
                  <label htmlFor="vehicle1"> I have a bike</label>
                  <br />
                  <input type="checkbox" id="vehicle2" value="Car" />
                  <label htmlFor="vehicle2"> I have a car</label>
                  <br />
                  <input type="checkbox" id="vehicle3" value="Boat" />
                  <label htmlFor="vehicle3"> I have a boat</label>
                  <br />
                  <input type="submit" value="Submit" />
                </form>

              </TabPanel>
              <TabPanel>
                <form
                  className="egift-validate-2-form"
                  method="post"
                  id="egift-val-form-2"
                  onSubmit={this.handleSubmit}
                >
                  <input type="radio" id="html" name="fav_language" value="HTML" />
                  <label htmlFor="html">HTML</label>
                  <br />
                  <input type="radio" id="css" name="fav_language" value="CSS" />
                  <label htmlFor="css">CSS</label>
                  <br />
                  <input type="radio" id="javascript" name="fav_language" value="JavaScript" />
                  <label htmlFor="javascript">JavaScript</label>
                  <input type="submit" value="Submit" />
                </form>
              </TabPanel>
            </Tabs>
          </Popup>
        </div>
      </>
    );
  }
}

export default HelloMemberCartOffersVouchers;
