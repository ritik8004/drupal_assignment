import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import { getAreaFieldKey, getDeliveryAreaStorage, getDeliveryAreaValue } from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import { setStorageInfo } from '../../../utilities/storage';
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
    const areaFieldKey = getAreaFieldKey();
    document.addEventListener('handleAreaSelect', this.handleAreaSelect);
    if (currentArea !== null && areaFieldKey !== null) {
      const { currentLanguage } = drupalSettings.path;
      // Fetching label from api if user switches language.
      if (!(currentLanguage in currentArea.label)) {
        getDeliveryAreaValue(currentArea.value[areaFieldKey]).then(
          (result) => {
            if (result !== null && result.items.length > 0) {
              const areaObj = result.items.find(
                (element) => element.location_id === currentArea.value[areaFieldKey],
              );
              if (areaObj && Object.keys(areaObj).length !== 0) {
                currentArea.label[currentLanguage] = areaObj.label;
                setStorageInfo(currentArea, 'deliveryinfo-areadata');
                this.setState({
                  areaLabel: areaObj.label,
                });
              }
            }
          },
        );
      } else {
        this.setState({
          areaLabel: currentArea.label[currentLanguage],
        });
      }
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
    // remove class loading when the delivery panel opens.
    document.querySelector('.delivery-loader').classList.remove('loading');
  }

  render() {
    const { areaLabel } = this.state;
    const { getPanelData, animationDelayValue, showAreaAvailabilityStatusOnCart } = this.props;

    return (
      <ConditionalView condition={showAreaAvailabilityStatusOnCart === true}>
        <div id="delivery-area-select" className="fadeInUp" style={{ animationDelay: animationDelayValue }}>
          <div className="delivery-area-label">
            <span>{`${Drupal.t('Deliver to')}: `}</span>
            <div onClick={() => getPanelData(this.openModal())}>
              <span className="delivery-area-name delivery-loader">{areaLabel}</span>
              <span className="delivery-area-button" />
            </div>
          </div>
        </div>
      </ConditionalView>
    );
  }
}
