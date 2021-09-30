import React from 'react';
import { getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import AreaListBlock from '../area-list-block';

export default class DeliveryAreaSelect extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      areaLabel: Drupal.t('Select'),
      currentArea: getDeliveryAreaStorage(),
    };
  }

  componentDidMount() {
    const { currentArea } = this.state;
    document.addEventListener('handleAreaSelect', this.handleAreaSelect);
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: currentArea.label[currentLanguage],
      });
    }
    dispatchCustomEvent('displayShippingMethods', currentArea);
  }

  handleAreaSelect = (e) => {
    e.preventDefault();
    if (e.detail !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: e.detail.label[currentLanguage],
      });
    }
  }

  openModal = () => {
    document.addEventListener('openDeliveryAreaPanel', this.openDeliveryAreaPanel);

    return (
      <AreaListBlock
        closeModal={() => this.closeModal()}
      />
    );
  };

  closeModal = () => {
    const { removePanelData } = this.props;
    document.querySelector('body').classList.remove('overlay-delivery-area');
    setTimeout(() => {
      removePanelData();
    }, 400);
  };

  openDeliveryAreaPanel = (event) => {
    event.preventDefault();
    // to make sure that markup is present in DOM.
    document.querySelector('body').classList.add('overlay-delivery-area');
  }

  render() {
    const { areaLabel } = this.state;
    const { getPanelData, animationDelayValue } = this.props;

    return (
      <div id="delivery-area-select" className="fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <div className="delivery-area-label">
          <span>{`${Drupal.t('Deliver to')}: `}</span>
          <span className="delivery-area-name">{areaLabel}</span>
          <span onClick={() => getPanelData(this.openModal())} className="delivery-area-button" />
        </div>
      </div>
    );
  }
}
