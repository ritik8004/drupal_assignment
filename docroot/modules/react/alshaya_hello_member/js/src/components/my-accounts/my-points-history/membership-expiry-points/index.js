import React from 'react';
import setupAccordionHeight from '../../../../utilities';

export default class MembershipExpiryPoints extends React.PureComponent {
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

  showExpiryContent = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.expandRef.current.classList.add('close-expiry-accordion');
    } else {
      this.setState({
        open: true,
      });
      this.expandRef.current.classList.remove('close-expiry-accordion');
    }
  };

  render() {
    const {
      open,
    } = this.state;

    // Add correct class.
    const expandedState = open === true ? 'show' : '';

    return (
      <div
        className="expiry-accordion disabled fadeInUp"
        style={{ animationDelay: '1.2s' }}
        ref={this.expandRef}
      >
        <div
          className={`title ${expandedState}`}
          onClick={() => this.showExpiryContent()}
        >
          <div className="">{/* Points */}</div>
          <div className="points-accordion">{/* 55 pt */}</div>
        </div>
        <div className="content">
          <div className="points-earned-block">
            <div className="earned-items">
              <div className="item">{/* Purchase */}</div>
              <div className="points">{/* 0 Pt */}</div>
            </div>
          </div>
          <div className="earned-points-info">
            <div className="info-items">
              <p className="info-item-title">{/* Purchase */}</p>
              <p className="info-item-subtitle">
                {/* Every time you shop online or in store, you’ll earn points. 1 KWD= 1 points. */}
              </p>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
