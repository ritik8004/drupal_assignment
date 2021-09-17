import React from 'react';
import dispatchCustomEvent from '../../../utilities/events';
import { getStorageInfo } from '../../../utilities/storage';
import AreaListBlock from '../area-list-block';

export default class DeliveryAreaSelect extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      areaLabel: Drupal.t('Select'),
    };
  }

  componentDidMount() {
    const currentArea = getStorageInfo('deliveryinfo-areadata');
    document.addEventListener('handleAreaSelect', this.handleAreaSelect);
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: currentArea.label[currentLanguage],
      });
    }
    dispatchCustomEvent('displayShippingMethods', currentArea);
  }

  handleAreaSelect = (event) => {
    event.preventDefault();
    const currentArea = getStorageInfo('deliveryinfo-areadata');
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: currentArea.label[currentLanguage],
      });
    }
  }

  openModal = () => {
    // to make sure that markup is present in DOM.
    setTimeout(() => {
      document.querySelector('body').classList.add('overlay-desc');
    }, 150);

    return (
      <AreaListBlock
        closeModal={() => this.closeModal()}
      />
    );
  };

  closeModal = () => {
    const { removePanelData } = this.props;
    document.querySelector('body').classList.remove('overlay-desc');
    setTimeout(() => {
      removePanelData();
    }, 400);
  };

  render() {
    const { areaLabel } = this.state;
    const { getPanelData } = this.props;

    return (
      <div id="delivery-area-select">
        <div className="delivery-area-label">
          <span>{`${Drupal.t('Deliver to')}: `}</span>
          <span className="delivery-area-name">{areaLabel}</span>
          <span onClick={() => getPanelData(this.openModal())} className="delivery-area-button" />
        </div>
      </div>
    );
  }
}
