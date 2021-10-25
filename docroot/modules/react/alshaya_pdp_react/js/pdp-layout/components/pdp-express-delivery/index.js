import React from 'react';
import setupAccordionHeight from '../../../utilities/sidebarCardUtils';
import HomeDeliverySVG from '../../../svg-component/hd-svg';
import PdpSectionTitle from '../utilities/pdp-section-title';
import DeliveryOptions from '../../../../../alshaya_spc/js/expressdelivery/components/delivery-options';

class PdpExpressDelivery extends React.Component {
  constructor(props) {
    super(props);
    this.expandRef = React.createRef();
    this.state = {
      open: true,
    };
  }

  componentDidMount() {
    // Accordion setup.
    document.addEventListener('setDeliveryOptionAccordionHeight', this.setDeliveryOptionAccordionHeight, false);
  }

  /**
   * Show delivery options on click of arrow button.
   */
  showExpressDeliveryBlock = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.expandRef.current.classList.add('close-card');
    } else {
      this.setState({
        open: true,
      });
      this.expandRef.current.classList.remove('close-card');
    }
  };

  /**
   * Adjust accordion height as per delivery options content.
   */
  setDeliveryOptionAccordionHeight = (event) => {
    event.preventDefault();
    setupAccordionHeight(React.createRef());
  }

  render() {
    const { open } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'show' : '';
    return (
      <div
        className="pdp-express-delivery-wrapper card fadeInUp"
        style={{ animationDelay: '1s' }}
        ref={this.expandRef}
      >
        <div
          className={`express-delivery-title-wrapper title ${expandedState}`}
          onClick={() => this.showExpressDeliveryBlock()}
        >
          <div className="express-delivery-title">
            <PdpSectionTitle>
              <span className="card-icon-svg">
                <HomeDeliverySVG />
              </span>
              {Drupal.t('Delivery Options')}
            </PdpSectionTitle>
            <div className="magv2-accordion" />
          </div>
          <div className="express-delivery-subtitle">{Drupal.t('Explore the delivery options applicable to your area.')}</div>
        </div>
        <DeliveryOptions />
      </div>
    );
  }
}

export default PdpExpressDelivery;
