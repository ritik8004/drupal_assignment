import React from 'react';
import Select from 'react-select';
import {
  checkExpressDeliveryStatus,
  checkSameDayDeliveryStatus,
} from '../../../../../js/utilities/expressDeliveryHelper';
import ConditionalView from '../../../common/components/conditional-view';
import {
  getAreaParentFieldKey,
  getDeliveryAreaList,
  getDeliveryAreaStorage,
  getGovernatesList,
  setDeliveryAreaStorage,
} from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import SectionTitle from '../../../utilities/section-title';
import getStringMessage from '../../../utilities/strings';
import AvailableAreaItems from '../available-area-items';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default class AreaListBlock extends React.Component {
  constructor(props) {
    super(props);
    this.parentVisibility = drupalSettings.address_fields.area_parent.visible;
    this.state = {
      governateOptions: '',
      governateDefault: '',
      areaListItems: [],
      items: [],
      activeItem: null,
    };
  }

  /**
   * Pre-populate city/area from storage values.
   */
  componentDidMount() {
    let governateDefaultLabel = '';
    const governateKey = getAreaParentFieldKey();
    const areaSelected = getDeliveryAreaStorage();
    let defaultOptions = {
      value: 'none',
      label: getStringMessage('governate_label', { '@label': governateDefaultLabel }),
    };
    if (drupalSettings.address_fields) {
      governateDefaultLabel = drupalSettings.address_fields.area_parent.label;
    }
    getGovernatesList().then(
      (response) => {
        const options = [];
        if (response !== null && Object.keys(response.items).length > 0) {
          response.items.forEach((item) => {
            options.push({
              value: item.location_id,
              label: item.label,
            });
          });
          if (areaSelected !== null && governateKey !== null) {
            defaultOptions = options.find(
              (element) => parseInt(element.value, 10) === areaSelected.value[governateKey],
            );
          } else if (options.length > 0) {
            [defaultOptions] = options;
          }
        }
        getDeliveryAreaList(defaultOptions.value).then(
          (result) => {
            if (hasValue(result) && Object.keys(result.items).length > 0) {
              this.setState({
                areaListItems: result.items,
                items: result.items,
              });
            }
          },
        );
        this.setState({
          governateOptions: options,
          governateDefault: defaultOptions,
        });
        dispatchCustomEvent('openDeliveryAreaPanel', {});
      },
    );
  }

  /**
   * handle on change function for city select list.
   */
  handleSelect = (selectedOption) => {
    if (selectedOption.value) {
      const defaultOptions = [{
        value: selectedOption.value,
        label: selectedOption.label,
      }];
      this.setState({
        governateDefault: defaultOptions,
      });
      getDeliveryAreaList(selectedOption.value).then(
        (response) => {
          if (hasValue(response) && Object.keys(response.items).length > 0) {
            this.setState({
              items: response.items,
              areaListItems: response.items,
            });
          }
        },
      );
    }
  };

  /**
   * Filter the list on search.
   */
  filterList = (e) => {
    const { areaListItems } = this.state;
    let updatedList = areaListItems;
    updatedList = updatedList.filter((item) => item.label.toLowerCase().search(
      e.target.value.toLowerCase(),
    ) !== -1);

    this.setState({
      items: updatedList,
    });
  };

  /**
   * Set active classes on selection of particular area.
   */
  handleLiClick = (e) => {
    if (e.currentTarget) {
      this.setState({
        activeItem: {
          areaId: parseInt(e.currentTarget.getAttribute('data-area-id'), 10),
          areaParentId: parseInt(e.currentTarget.getAttribute('data-parent-id'), 10),
          areaLabel: e.currentTarget.getAttribute('data-label'),
        },
      });
      // Remove the previous active class.
      const activeElem = document.querySelector('.spc-delivery-area ul#delivery-area-list-items li.active');
      if (activeElem) {
        activeElem.classList.remove('active');
        activeElem.classList.toggle('in-active');
      }
      // Set active class on the current element.
      const elem = document.querySelector(`.spc-delivery-area ul#delivery-area-list-items li#value${e.currentTarget.getAttribute('data-area-id')}`);
      if (elem.classList.contains('in-active')) {
        elem.classList.remove('in-active');
      }
      elem.classList.toggle('active');
    }
  };

  /**
   * Set new value of city/area in storage and refresh list on submit.
   */
  handleSubmit = (e, activeItem) => {
    e.preventDefault();
    if (activeItem !== null) {
      const { closeModal } = this.props;
      const areaSelected = {
        label: activeItem.areaLabel,
        area: activeItem.areaId,
        governate: activeItem.areaParentId,
      };
      setDeliveryAreaStorage(areaSelected);
      closeModal();
      const currentArea = getDeliveryAreaStorage();
      dispatchCustomEvent('handleAreaSelect', currentArea);
      // Show delivery methods with cart items.
      dispatchCustomEvent('displayShippingMethods', currentArea);
    }
  };

  render() {
    const {
      governateOptions, governateDefault, items, activeItem,
    } = this.state;
    const { closeModal, placeHolderText } = this.props;
    let governateDefaultLabel = '';
    if (drupalSettings.address_fields) {
      governateDefaultLabel = drupalSettings.address_fields.area_parent.label;
    }
    return (
      <div className="spc-delivery-wrapper">
        <div className="spc-delivery-area">
          <div className="title-block">
            <SectionTitle>{getStringMessage('check_area_availability')}</SectionTitle>
            <a className="close-modal" onClick={closeModal} />
          </div>
          <div className="area-list-block-content">
            <div className="area-list-block-wrapper">
              <ConditionalView condition={this.parentVisibility}>
                <div className="governate-label">{getStringMessage('governate_label', { '@label': governateDefaultLabel })}</div>
                <div className="governate-drop-down">
                  <Select
                    classNamePrefix="spcSelect"
                    className="spc-select"
                    onChange={this.handleSelect}
                    options={governateOptions}
                    defaultValue={governateDefault}
                    value={governateDefault}
                    isSearchable
                  />
                </div>
              </ConditionalView>
              <div className="area-label">{`${Drupal.t('Search area')}`}</div>
              <div className="spc-filter-panel-search-form-item">
                <input className="spc-filter-panel-search-field" type="text" placeholder={placeHolderText} onChange={this.filterList} />
              </div>
              <div className="delivery-type-wrapper">
                <span className="standard-delivery">{Drupal.t('Standard')}</span>
                <ConditionalView condition={checkSameDayDeliveryStatus()}>
                  <span className="sameday-delivery">{Drupal.t('Same Day')}</span>
                </ConditionalView>
                <ConditionalView condition={checkExpressDeliveryStatus()}>
                  <span className="express-delivery">{Drupal.t('Express')}</span>
                </ConditionalView>
              </div>
              <div className="area-list-label">{`${Drupal.t('Select an area')}`}</div>
              <ConditionalView condition={items.length !== 0}>
                <ul id="delivery-area-list-items" className="area-list-wrapper">
                  {items.map((item) => (
                    <AvailableAreaItems
                      key={item.location_id}
                      attr={item.location_id}
                      value={item.label}
                      handleLiClick={this.handleLiClick}
                      parentId={item.parent_id}
                      isStandardDelivery={item.is_standard_delivery}
                      isSameDayDelivery={item.is_sameday_delivery}
                      isExpressDelivery={item.is_express_delivery}
                    />
                  ))}
                </ul>
              </ConditionalView>
              <div className="actions">
                <div className="select-area-link submit">
                  <a
                    onClick={(e) => this.handleSubmit(e, activeItem)}
                    href="#"
                    className="select-area-link"
                  >
                    {Drupal.t('Select this area')}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
