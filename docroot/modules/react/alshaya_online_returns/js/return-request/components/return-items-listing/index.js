import React from 'react';
import Collapsible from 'react-collapsible';
import Popup from 'reactjs-popup';
import ReturnItemDetails from '../return-item-details';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { getDefaultResolutionId } from '../../../utilities/return_request_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../../js/utilities/display';
import PromotionsWarningModal from '../promotions-warning-modal';
import { getPreparedOrderGtm, getProductGtmInfo } from '../../../utilities/online_returns_gtm_util';

class ReturnItemsListing extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      btnDisabled: true,
      open: true,
      promotionModalOpen: false,
      discountedRuleId: null,
      itemNotEligibleForReturn: false,
    };
    this.productsListRef = React.createRef();
    this.handleSelectedReason = this.handleSelectedReason.bind(this);
    this.processSelectedItems = this.processSelectedItems.bind(this);
  }

  componentDidMount() {
    const { open } = this.state;
    // Dispatch event to disable refund accordion on page load.
    dispatchCustomEvent('updateRefundAccordionState', !open);
  }

  /**
   * If any reason is selected by customer
   * continue button will be enabled.
   */
  handleSelectedReason = (selectedReason, sku) => {
    if (selectedReason) {
      const { handleSelectedItems, itemsSelected } = this.props;

      const items = itemsSelected.map((item) => {
        const data = { ...item };
        if (data.sku === sku) {
          data.reason = selectedReason.value;
        }
        return data;
      });

      this.setState({
        btnDisabled: items.some((item) => (!hasValue(item.reason) || item.reason === 0)),
      });

      handleSelectedItems(items);
    }
  }

  /**
   * Update selected quantity in state.
   */
  handleSelectedQuantity = (selectedQuantity, sku) => {
    if (selectedQuantity) {
      const { handleSelectedItems, itemsSelected } = this.props;

      const items = itemsSelected.map((item) => {
        const data = { ...item };
        if (data.sku === sku) {
          data.qty_requested = selectedQuantity.value;
        }
        return data;
      });

      handleSelectedItems(items);
    }
  }

  /**
   * Capturing selected items and enabling/disabling button
   * as per item selection.
   */
  processSelectedItems = (checked, item) => {
    const { handleSelectedItems, itemsSelected, products } = this.props;
    const {
      extension_attributes: {
        auto_select: autoSelect,
      },
      applied_rule_ids: appliedRuleIdsWithDiscount,
    } = item;
    let selectedItemDiscountPromotion = [];

    // Check for discount promotion only if autoselect is set.
    if (autoSelect && hasValue(appliedRuleIdsWithDiscount)) {
      selectedItemDiscountPromotion = appliedRuleIdsWithDiscount.split(',');
    }
    const itemDetails = item;
    let discountedProducts = [];
    let itemEligibleForReturn = true;

    if (selectedItemDiscountPromotion.length > 0) {
      // Filter out all the valid product which are having discounted rule id and
      // non zero ordered qty available.
      discountedProducts = products.filter(
        (product) => product.applied_rule_ids
        && item.sku !== product.sku
        && product.ordered > 0,
      );

      // Check if item is eligible for return or not.
      itemEligibleForReturn = !(discountedProducts.some(
        (individualItem) => {
        // Explode all the applied discounted rule ids.
          const itemDiscountedRuleIds = individualItem.applied_rule_ids.split(',');
          return !(itemDiscountedRuleIds.some((pid) => selectedItemDiscountPromotion.includes(pid))
            && individualItem.is_returnable);
        },
      ));
    }

    if (!itemEligibleForReturn) {
      this.setState({
        promotionModalOpen: true,
        discountedRuleId: selectedItemDiscountPromotion,
        itemNotEligibleForReturn: true,
      });
    } else {
      // Set checked status.
      itemDetails.isChecked = checked;
    }

    if (checked) {
      // Check if any promotion is applied to the item.
      if (selectedItemDiscountPromotion.length > 0) {
        // Display promotions warning modal.
        this.setState({
          promotionModalOpen: true,
          discountedRuleId: selectedItemDiscountPromotion,
        });
        // Add a flag value to disable the qty button.
        itemDetails.disableQtyBtn = true;
        itemDetails.qty_requested = itemDetails.qty_ordered;
      } else {
        // Add default quantity and resolution.
        itemDetails.qty_requested = 1;
      }
      itemDetails.resolution = getDefaultResolutionId();

      this.setState({
        btnDisabled: true,
      });
      handleSelectedItems([...itemsSelected, itemDetails]);
    } else if (selectedItemDiscountPromotion.length > 0) {
      this.handlePromotionDeselect();
    } else {
      const filteredSelectedItems = itemsSelected.filter(
        (product) => product.sku !== itemDetails.sku,
      );
      handleSelectedItems(filteredSelectedItems);
      this.setState({
        btnDisabled: filteredSelectedItems.length > 0
          && filteredSelectedItems.some((filteredItem) => (
            !hasValue(filteredItem.reason) || filteredItem.reason === 0)),
      });
    }
  }

  handlePromotionContinue = () => {
    const { products, handleSelectedItems, itemsSelected } = this.props;
    const { discountedRuleId } = this.state;

    if (!hasValue(discountedRuleId)) {
      return;
    }

    const promotionalItems = [];

    products.forEach((product) => {
      const productDetails = product;
      let productDiscountedRuleIds = [];
      if (hasValue(product.applied_rule_ids)) {
        productDiscountedRuleIds = product.applied_rule_ids.split(',');
      }
      if (hasValue(productDiscountedRuleIds)
        && productDiscountedRuleIds.some((pid) => discountedRuleId.includes(pid))
        // We don't want to include the products which are already returned.
        && productDetails.ordered > 0
        && !itemsSelected.some((item) => item.sku === product.sku)) {
        productDetails.qty_requested = productDetails.qty_ordered;
        productDetails.resolution = getDefaultResolutionId();
        productDetails.isChecked = true;
        // Add a flag value to disable the qty button.
        productDetails.disableQtyBtn = true;

        promotionalItems.push(productDetails);
      }
    });

    if (hasValue(promotionalItems)) {
      handleSelectedItems([...itemsSelected, ...promotionalItems]);
    }

    this.setState({
      promotionModalOpen: false,
    });
  };

  handlePromotionDeselect = () => {
    const { products, handleSelectedItems, itemsSelected } = this.props;
    const { discountedRuleId } = this.state;

    if (!hasValue(discountedRuleId)) {
      return;
    }

    const promotionalItems = [];

    products.forEach((product) => {
      const productDetails = product;
      let productDiscountedRuleIds = [];
      if (hasValue(product.applied_rule_ids)) {
        productDiscountedRuleIds = product.applied_rule_ids.split(',');
      }
      if (hasValue(productDiscountedRuleIds)
        && productDiscountedRuleIds.some((pid) => discountedRuleId.includes(pid))) {
        productDetails.isChecked = false;
        // Enable the qty button.
        productDetails.disableQtyBtn = false;
        promotionalItems.push(productDetails);
      }
    });

    if (hasValue(promotionalItems)) {
      handleSelectedItems(itemsSelected.filter(
        (item) => !promotionalItems.some((promoItem) => promoItem.sku === item.sku),
      ));
    }

    this.setState({
      promotionModalOpen: false,
      btnDisabled: itemsSelected.length > 0
        && itemsSelected.some((item) => (!hasValue(item.reason) || item.reason === 0)),
    });
  };

  /**
   * Display the return items accordion trigger component.
   * On click of this component, item details div will open.
   */
  itemListHeader = (products) => {
    if (hasValue(products)) {
      return (
        <div className="select-items-label">
          <span className="select-items-header">{ Drupal.t('1. Select items to return', {}, { context: 'online_returns' }) }</span>
        </div>
      );
    }
    return null;
  }

  /**
   * When item details accordion is opened, refund
   * accordion is collapsed.
   */
  disableRefundComponent = () => {
    this.updateRefundAccordion(false);
  };

  /**
   * Process return request continue.
   */
  handleReturnContinue = async () => {
    const { open } = this.state;

    const { itemsSelected } = this.props;
    // Push data to GTM.
    Drupal.alshayaSeoGtmPushReturn(
      getProductGtmInfo(itemsSelected),
      await getPreparedOrderGtm('item_confirmed'),
      'item_confirmed',
    );
    // When user clicks continue button, disable the item
    // details accordion and enable refund accordion.
    this.updateRefundAccordion(open);
    this.scrollToProductsList();
  }

  /**
   * Scroll to the refund details step of accordion.
   */
  scrollToProductsList = () => {
    const productsList = this.productsListRef.current;
    const headerOffset = isMobile() ? 64 : 32;
    const productsListPosition = productsList.getBoundingClientRect().top;
    const offsetPosition = productsListPosition + window.pageYOffset - headerOffset;

    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth',
    });
  }

  /**
   * Update accordion state of refund details component.
   */
  updateRefundAccordion = (accordionState) => {
    this.setState({
      open: !accordionState,
    });

    dispatchCustomEvent('updateRefundAccordionState', accordionState);
  }

  render() {
    const {
      btnDisabled,
      open,
      promotionModalOpen,
      itemNotEligibleForReturn,
    } = this.state;
    const { products, itemsSelected } = this.props;
    // If no item is selected, button remains disabled.
    const btnState = !!((itemsSelected.length === 0 || btnDisabled));
    return (
      <div className="products-list-wrapper" ref={this.productsListRef}>
        <Collapsible
          trigger={this.itemListHeader(products)}
          open={open}
          onOpening={() => this.disableRefundComponent()}
        >
          {products.map((item) => (
            <div key={item.sku} className="item-list-wrapper">
              <ReturnItemDetails
                item={item}
                handleSelectedReason={this.handleSelectedReason}
                processSelectedItems={this.processSelectedItems}
                handleSelectedQuantity={this.handleSelectedQuantity}
              />
            </div>
          ))}
          <div className="continue-button-wrapper">
            <button
              type="button"
              disabled={btnState}
              onClick={this.handleReturnContinue}
            >
              <span className="continue-button-label">{Drupal.t('Continue', {}, { context: 'online_returns' })}</span>
            </button>
          </div>
        </Collapsible>
        <Popup
          className="promotions-warning-modal"
          open={promotionModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <PromotionsWarningModal
            handlePromotionDeselect={this.handlePromotionDeselect}
            handlePromotionContinue={this.handlePromotionContinue}
            itemNotEligibleForReturn={itemNotEligibleForReturn}
          />
        </Popup>
      </div>
    );
  }
}

export default ReturnItemsListing;
