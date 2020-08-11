import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import { setupAccordionHeight } from '../../../utilities/sidebarCardUtils';

class PdpStandardDelivery extends React.Component {
  constructor(props) {
    super(props);
    this.expandRef = React.createRef();
    this.state = {
      open: false,
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
      this.expandRef.current.style.removeProperty('max-height');
    } else {
      this.setState({
        open: true,
      });
      const maxHeight = this.expandRef.current.getAttribute('data-max-height');
      this.expandRef.current.style.maxHeight = maxHeight;
    }
  };

  render() {
    const { homeDelivery } = drupalSettings;
    const { open } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'show' : '';
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
