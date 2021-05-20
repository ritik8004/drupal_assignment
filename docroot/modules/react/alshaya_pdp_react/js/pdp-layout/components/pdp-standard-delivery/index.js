import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import setupAccordionHeight from '../../../utilities/sidebarCardUtils';
import HomeDeliverySVG from '../../../svg-component/hd-svg';

class PdpStandardDelivery extends React.Component {
  constructor(props) {
    super(props);
    this.expandRef = React.createRef();
    this.state = {
      open: true,
    };
  }

  componentDidMount() {
    // Accordion setup.
    setupAccordionHeight(this.expandRef);
  }

  showHomeDeliveryBlock = () => {
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

  render() {
    const { homeDelivery } = drupalSettings;
    const { open } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'show' : '';
    // If homeDelivery is not set we exit.
    if (homeDelivery === undefined) {
      return null;
    }
    return (
      <div
        className="magv2-pdp-standard-delivery-wrapper card fadeInUp"
        style={{ animationDelay: '1s' }}
        ref={this.expandRef}
      >
        <div
          className={`magv2-standard-delivery-title-wrapper title ${expandedState}`}
          onClick={() => this.showHomeDeliveryBlock()}
        >
          <PdpSectionTitle>
            <span className="magv2-card-icon-svg">
              <HomeDeliverySVG />
            </span>
            {homeDelivery.title}
          </PdpSectionTitle>
          <div className="magv2-accordion" />
        </div>
        <PdpSectionText className="content standard-delivery-detail">
          <span>{homeDelivery.subtitle}</span>
          <span>{homeDelivery.standard_subtitle}</span>
        </PdpSectionText>
      </div>
    );
  }
}

export default PdpStandardDelivery;
