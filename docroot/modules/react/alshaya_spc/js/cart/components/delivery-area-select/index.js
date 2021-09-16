import React from 'react';
import Popup from 'reactjs-popup';
import dispatchCustomEvent from '../../../utilities/events';
import { getStorageInfo } from '../../../utilities/storage';
import AreaListBlock from '../area-list-block';

export default class DeliveryAreaSelect extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
      areaLabel: Drupal.t('Select Area'),
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
    document.body.classList.add('open-form-modal');

    this.setState({
      isModelOpen: true,
    });
  };

  closeModal = () => {
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
  };

  render() {
    const {
      isModelOpen,
      areaLabel,
    } = this.state;
    return (
      <div id="delivery-area-select">
        <div className="delivery-area-label">
          <span>{`${Drupal.t('Deliver to')}: `}</span>
          <span className="delivery-area-name">{areaLabel}</span>
          <span onClick={() => this.openModal()} className="delivery-area-button">
            Arrow
          </span>
          <Popup
            open={isModelOpen}
            className="spc-area-list-popup"
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <AreaListBlock
              closeModal={() => this.closeModal()}
            />
          </Popup>
        </div>
      </div>
    );
  }
}
