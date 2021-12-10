import React from 'react';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import UpdateEgiftCardAmount from '../UpdateEgiftCardAmount';
import ValidEgiftCard from '../ValidEgiftCard';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { callEgiftApi } from '../../../utilities/egift_util';

class PaymentMethodLinkedEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.egiftCardhelper = new ValidEgiftCard();
    this.state = {
      // OpenModal.
      openModal: false,
      // State to set if user balance is low.
      exceedingAmount: 0,
      // State to set remaining balance.
      remainingAmount: 0,
      // State to set card balance.
      egiftCardBalance: 0,
      // State to handle checkbox.
      setChecked: false,
      // State to check card validity.
      isEgiftCardValid: false,
      // State to check if redemption performed already.
      redeemed: false,
      // State to get the amount from Modal.
      amount: 0,
      // State to check if user has linked card.
      linkedEgiftCard: false,
      // State to get linked card number.
      linkedEgiftCardNumber: '',
      // State to perform api call.
      apiWait: false,
      // State to get api error message.
      apiErrorMessage: '',
    };
  }

  componentDidMount() {
    const { cart } = this.props;
    const params = { email: drupalSettings.userDetails.userEmailID };
    // Invoke magento API to get the user card number.
    const response = callEgiftApi('eGiftHpsSearch', 'GET', {}, params);
    if (response instanceof Promise) {
      response.then((result) => {
        if (result.status === 200) {
          if (result.data.card_number !== null && result.data.response_type !== false) {
            this.setState({
              linkedEgiftCard: true,
              linkedEgiftCardNumber: result.data.card_number,
              apiWait: true,
            });
          }
          // Handle error response.
          if (result.data.account_id === null && result.data.response_type === false) {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId . Response: @response', {
              '@emailId': params.email,
              '@response': result.data.response_message,
            });
            this.setState({
              apiErrorMessage: result.data.response_message,
            });
          }
        }
      });
    }
    const { linkedEgiftCardNumber } = this.state;
    // Invoke magento API to check if card has balance.
    const postData = {
      accountInfo: {
        cardNumber: linkedEgiftCardNumber,
        email: drupalSettings.userDetails.userEmailID,
      },
    };
    const balanceResponse = callEgiftApi('eGiftGetBalance', 'POST', postData);
    if (balanceResponse instanceof Promise) {
      balanceResponse.then((result) => {
        if (result.status === 200) {
          if (result.data.current_balance !== null && result.data.response_type !== false) {
            const currentTime = Math.floor(Date.now() / 1000);
            this.setState({
              egiftCardBalance: result.data.current_balance,
              isEgiftCardValid: (currentTime < result.data.expiry_date_timestamp),
            });
            // Handle if user already performed redemption.
            if (cart.cart.totals.extension_attributes.hps_redeemed_amount > 0) {
              if (cart.cart.cart_total > result.data.current_balance) {
                this.handleExceedingAmount(true,
                  cart.cart.cart_total - result.data.current_balance,
                  false);
              }
              if (cart.cart.cart_total <= result.data.current_balance) {
                this.setRedeemAmount(
                  true,
                  result.data.current_balance - cart.cart.cart_total,
                  false,
                );
              }
              this.setState({
                redeemed: true,
                setChecked: true,
              });
            }
          }
          // Handle error response.
          if (result.data.account_id === null && result.data.response_type === false) {
            logger.error('Error while calling the eGiftGetBalance. CardNumber: @cardNumber . Response: @response', {
              '@cardNumber': postData.accountInfo.cardNumber,
              '@response': result.data.response_message,
            });
            this.setState({
              apiErrorMessage: result.data.response_message,
            });
          }
        }
      });
    }
  }

  openModal = (e) => {
    this.setState({
      openModal: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      openModal: false,
    });
  };

  // Handle exceeding amount scenario.
  handleExceedingAmount = (status, extraAmount, model) => {
    this.setState({
      redeemed: status,
      exceedingAmount: extraAmount,
      openModal: model,
    });
  };

  // Get redeemed amount.
  setRedeemAmount = (status, redeemAmount, model) => {
    this.setState({
      redeemed: status,
      amount: redeemAmount,
      openModal: model,
    });
  };

  // Handle Onclick.
  handleOnClick = (e) => {
    // Reset the state to move back to initial redeem stage.
    this.setState({
      setChecked: e.target.checked,
    });
    const { linkedEgiftCardNumber, egiftCardBalance } = this.state;
    const { cart } = this.props;
    if (e.target.checked === true) {
      this.setState({
        exceedingAmount: 0,
      });
      const postData = {
        redeem_points: {
          action: 'set_points',
          quote_id: cart.cart.cart_id_int,
          amount: cart.cart.totals.base_grand_total,
          cardNumber: linkedEgiftCardNumber,
          payment_method: 'hps_payment',
          email: drupalSettings.userDetails.userEmailID,
        },
      };
      // Proceed only if postData object is available.
      if (postData) {
        showFullScreenLoader();
        // Invoke the redemption API to update the redeem amount.
        const response = callEgiftApi('eGiftRedemption', 'POST', postData);
        if (response instanceof Promise) {
          response.then((result) => {
            // Remove loader once result is available.
            removeFullScreenLoader();
            if (result.status === 200) {
              if (result.data.redeemed_amount !== null && result.data.response_type !== false) {
                if (cart.cart.cart_total <= egiftCardBalance) {
                  this.setState({
                    redeemed: true,
                    amount: egiftCardBalance - cart.cart.cart_total,
                    openModal: false,
                  });
                }
                if (cart.cart.cart_total > egiftCardBalance) {
                  this.setState({
                    exceedingAmount: cart.cart.cart_total - egiftCardBalance,
                  });
                }
                if (egiftCardBalance >= cart.cart.cart_total) {
                  this.setState({
                    redeemed: true,
                    amount: egiftCardBalance - cart.cart.cart_total,
                    openModal: false,
                  });
                }
              }
              if (result.status === 200 && result.data.response_type === false) {
                logger.error('Error while calling the eGiftRedemption. Action: @action CardNumber: @cardNumber Response: @response', {
                  '@action': postData.redeem_points.action,
                  '@cardNumber': postData.redeem_points.cardNumber,
                  '@response': result.data.response_message,
                });
                this.setState({
                  apiErrorMessage: result.data.response_message,
                });
              }
            }
          });
        }
      }
    } else {
      this.setState({
        exceedingAmount: 0,
      });
      const postData = {
        redeem_points: {
          action: 'remove_points',
          quote_id: cart.cart.cart_id_int,
        },
      };
      showFullScreenLoader();
      // Invoke the redemption API.
      const response = callEgiftApi('eGiftRedemption', 'POST', postData);
      if (response instanceof Promise) {
        // Handle the error and success message after the egift card is removed
        // from the cart.
        response.then((result) => {
          removeFullScreenLoader();
          if (result.status === 200 && result.data.response_type !== false) {
            this.setRedeemAmount(false, 0, false);
          }
          if (result.data.response_type === false) {
            logger.error('Error while calling the cancel eGiftRedemption. Action: @action Response: @response', {
              '@action': postData.redeem_points.action,
              '@response': result.data.response_message,
            });
            this.setState({
              apiErrorMessage: result.data.response_message,
            });
          }
        });
      }
    }
  };

  render() {
    const {
      openModal,
      egiftCardBalance,
      remainingAmount,
      apiErrorMessage,
      setChecked,
      exceedingAmount,
      redeemed,
      apiWait,
      linkedEgiftCard,
      amount,
      linkedEgiftCardNumber,
      isEgiftCardValid,
    } = this.state;
    const {
      cart,
    } = this.props;
    if (!apiWait && !linkedEgiftCard) {
      return null;
    }

    return (
      <>
        <div className="payment-method fadeInUp payment-method-checkout_com_egift_linked_card">
          <div className="payment-method-top-panel">
            <input type="checkbox" id="link-egift-card" checked={setChecked} onChange={this.handleOnClick} />
            <div className="payment-method-label-wrapper">
              <label className="checkbox-sim checkbox-label egift-link-card-label">
                <ConditionalView condition={isEgiftCardValid && !redeemed}>
                  {
                    Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': egiftCardBalance,
                      }, { context: 'egift' })
                  }
                </ConditionalView>
                <ConditionalView condition={isEgiftCardValid && redeemed}>
                  {
                    Drupal.t('Pay using egift card (Remaining Balance: @currencyCode @amount)',
                      {
                        '@currencyCode': getCurrencyCode(), '@amount': amount,
                      }, { context: 'egift' })
                  }
                </ConditionalView>
              </label>
            </div>
            <svg version="1.1" id="fLayer_1" xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="170px" height="83px" viewBox="0 0 170 83" enableBackground="new 0 0 170 83" space="preserve">
              <image overflow="visible" width="170" height="83" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKoAAABTCAMAAAA4NWxhAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjhCREU2NzhEREJCQzExRUFCMTI3REZFRjE3QTFFNkY1IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjhCREU2NzhFREJCQzExRUFCMTI3REZFRjE3QTFFNkY1Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6OEJERTY3OEJEQkJDMTFFQUIxMjdERkVGMTdBMUU2RjUiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6OEJERTY3OENEQkJDMTFFQUIxMjdERkVGMTdBMUU2RjUiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7NobVyAAADAFBMVEWlyNlWl7f/6QAAScllkmb/3TkhfqV5m1q91+TJ3OcAWbLr8vYqeYr/4lYAW4y3tjYXcpdyqsQAYqX/3AD/87X/+NgAa5n/0gjk7PMNc53/zQBTinNxl2GirEQAbJcYeKIedY0xfIfT5OyKtsz/6obkyBv/7JIAUoadwtX/1gCTpUvLvCvNwCZOlrY1iq3/++b+1wctgKf/+wC8uC7/2iQAZaH/5gAAaZYAOHRCg33/2QAAVrb/7gD/5AAMbpoAU7zszBSHoVJspsGrsz3/2gD/8a7/4gCTvtL/76IAaZwVbJkAXa7FuizawiLp0xf/1ABWjW4AYZH/30L/8wD/6H3/9MTNvSdioL3/4ACXsGr/3Qr/1gMAXql9ssoAYak3g3n/3QD//PL/0gPdxB43gIO32PgATYLBujJtnLuBoFkAZp//2y3UxCWMpUz/0wT/5WX/zwBEgYXlzBf/1xAAZJL/0QDe6vFKiXT00yzZ5u4AKmpckWjx9vmnrT+Zq0vyzRL1+Pr/2QUAQnv/1QkAVoj/1gw3eqI/hqu20uDhxx0kdpAAW7AVaab/2Rr50g0AZpQMaZcAZKZPhXv60wpZn7yRo1HbzCH4+/z/1yQLZpUNYqsEbJhDjrD9+/5mkG4AT775/f8ibplNi24Na6acrUP+/P0Abpr20w3/2AL+1xsAZ6LUwCf9/v4FbJ2TqUn/1QPVvioAbJgAbpcFaZ4AcJuNutD/4AP/3QH/2wH/2gP//fj8/P35+vsAY6MGaZatzdzw0RIFZ5UHYJAAa5P/////2R7/1AMAbZf/1QEAbZn/1AL/zwn//v8AX49CiHL//f5wkG2epEzQxDf/8xb05BAPaKD8/f753Aju4BOerD4eaoa0sTwGZZP22AkEapz01gdgi3ENYLhjkXjIwC4AWr8RcZT/4itCkbIBbJnP4OrGx6HP5//kxSEhbL/63QDn0jHv8vYAbpX/0QSXq0YAT6CeqET31B4/f4PLyihJgX7+/v7r0hBDgHhHhnnSykP///9PCp9uAAABAHRSTlP///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////8AU/cHJQAADdZJREFUeNrkm3tYE1cWwMOIgCNGSDRKDAOIOkbACsoEkgwgxGgMNgZayTYhjUhjiStmMdFan9W0VbKa4qMPbUu1a5YiNp2WSnT1s4/ZDEGi3Yfb3bXrdrd11X0Wt67V1nXvTBKegfb7LCZ+e/6Amzszl9+ce865554bWHeAjCubs/PnESs755TNoylZd+48MLLdFemyYjyNOl7rug+kfTxALXPdFzLyDmvBoO+hspr5jJitqggwkWmsspDzr20381VPl616aMqUh1aNXK3im8MOW8baGQpUxeePXDD+BBmQE+MXjOTzVeE16hWs1SGmnm8e8zDZTx4eY7Y2ecKIOmcgKlDpivFkCBn/n3+6ayMJVWvmTyNDy2MCubw2clC15qpxg5CeW1P4zTG8asAsCGnR3nNUrfnaeHIwmWxpTHsE76VXT4cBgjBcLpfjENRUe09RhyQlp9s+LPwRpulGqm2Ddm0URC8Gsj56Y5PwnqKqzAsGJyWzUAop/DMmDpIa2vYdhSV2Rgolmfg9ReU/NATpxPctBMVr3CT1q9Xjxm43chCKIHgKBQ9RzsbuIarW/OKtIVDnowCL4rw+1eC/Hasp5FEUpZMoGxqUhYVL7iWqyjxuCNLXDrK9BEF8+PlfMWaR5e5aqKcAevzc2ZlLNsau3AYMQyjW4DiOyd1+u22SicVBcxGCpljbu+3Rgp8yIVhWhFw5hms94l6393u6Pyp/zFBKTTQ6ASnhS3nlL2b6bjxTryAIXTxXigE6EAGuySDMvfnIkoyMHS4MEtd6tFy3W6bBtACmqg2TuWUGuYtuczG3TGZwV7UZQJ9GK4awPYtKFzWJNTK3uDtyV7XJxeCJnrjSG7WdP5RPTXzOB+af8KIV5DQ+rVbsgpJH8OBN0o7A2Pix64fSCho4HL0i9dBGOS5umlRfnRb/EzlQsaZubXx1WuqbeLvLo/l6cVp19aXflZckVFfXL4aw62sTmpVwprga3BI/W+4fTlOXHw86pmpCoVrfODEE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" />
            </svg>
            <div id="api-error">{apiErrorMessage}</div>
            <div className="payment-method-bottom-panel payment-method-form checkout_com_egift_linked_card">
              <ConditionalView condition={setChecked === true}>
                <ConditionalView conditional={openModal}>
                  <UpdateEgiftCardAmount
                    closeModal={this.closeModal}
                    open={openModal}
                    cardBalance={egiftCardBalance}
                    remainingAmount={remainingAmount}
                    amount={cart.cart.totals.base_grand_total}
                    updateAmount={this.egiftCardhelper.handleAmountUpdate}
                    redeemAmount={this.setRedeemAmount}
                    handleExceedingAmount={this.handleExceedingAmount}
                    cart={cart.cart}
                    egiftCardNumber={linkedEgiftCardNumber}
                  />
                </ConditionalView>
                <div className="spc-payment-method-desc">
                  <div className="desc-content">
                    <ConditionalView condition={!isEgiftCardValid}>
                      {
                        Drupal.t('Card is expired please use another payment method to complete purchase', {}, { context: 'egift' })
                      }
                    </ConditionalView>
                    <ConditionalView condition={setChecked === true
                      && egiftCardBalance === 0 && isEgiftCardValid}
                    >
                      {
                        Drupal.t('Linked card has 0 balance please use another payment method to complete purchase', {}, { context: 'egift' })
                      }
                    </ConditionalView>
                    <ConditionalView condition={exceedingAmount > 0 && isEgiftCardValid}>
                      {
                        Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                          {
                            '@currencyCode': getCurrencyCode(), '@amount': exceedingAmount,
                          }, { context: 'egift' })
                      }
                    </ConditionalView>
                  </div>
                </div>
                <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
              </ConditionalView>
            </div>
          </div>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedEgiftCard;
