import React from 'react';
import Popup from 'reactjs-popup';
import Promotions from '../promotions';

export default class PromotionsFrame extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  openModal = (e) => {
    this.setState({
      open: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
  };

  render() {
    const { promotions } = this.props;
    const { open } = this.state;

    return (
      <>
        {(promotions.length > 0 && (typeof promotions[0].context === 'undefined' || promotions[0].context.includes('web') || !promotions[0].context.length))
          && (
          <div className="promotions">
            <span className="sku-promotion-item">
              <div className="sku-promotion-text sku-promotion-text--ellipse" onClick={(e) => this.openModal(e)}>{`${promotions[0].text.substring(0, 30)}...`}</div>
              <Popup
                open={open}
                className="algolia-promotion-list"
                onClose={this.closeModal}
                closeOnDocumentClick={false}
              >
                <div className="promotions-popup-wrapper">
                  <div className="promotions-popup-title">
                    {' '}
                    {Drupal.t('Offers available')}
                    {' '}
                  </div>
                  <a className="close" onClick={() => this.closeModal()}> &times; </a>
                  <div className="promotions-popup-subtitle">
                    {Drupal.t('You can combine promotions but only one offer code can be used per transaction')}
                  </div>
                  <Promotions promotions={promotions} />
                </div>
              </Popup>
            </span>
          </div>
          )}
      </>
    );
  }
}
