const Advantagecard = {
  // Helper function to check if Advantage Card Applied.
  isAdvantageCardApplied: (items) => {
    let advantageCardApplied = false;
    Object.entries(items).forEach(([, item]) => {
      if (typeof item.extension_attributes !== 'undefined') {
        if (item.extension_attributes.adv_card_applicable) {
          advantageCardApplied = true;
        }
      }
    });
    return advantageCardApplied;
  },

  // Helper function to check if Advantage Card Eligible Product.
  isAdvantageCardEligibleProduct: (items, itemid) => {
    let advantageCardEligibleProduct = true;
    Object.entries(items).forEach(([, item]) => {
      if (item.item_id === itemid) {
        if (typeof item.extension_attributes !== 'undefined') {
          advantageCardEligibleProduct = item.extension_attributes.adv_card_applicable;
        }
      }
    });
    return advantageCardEligibleProduct;
  },

  // Helper function for Advantage card status.
  isAdvantagecardEnabled: () => {
    if (typeof drupalSettings.alshaya_spc.advantageCard !== 'undefined'
      && drupalSettings.user.uid) {
      if (drupalSettings.alshaya_spc.advantageCard.enabled
        && typeof drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix !== 'undefined'
        && drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix) {
        return true;
      }
    }

    return false;
  },

  // Helper function to check valid Advantage card pattern.
  isValidAdvantagecard: (advantageCard) => {
    const advantageCardType = ['01', '02', '03'];
    if (advantageCard.slice(0, 7) === drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix
      && advantageCard.length !== 16) {
      return false;
    }
    if (advantageCard.slice(0, 7) === drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix
      && !advantageCardType.includes(advantageCard.substring(9, 7))) {
      return false;
    }
    if (advantageCard.slice(0, 7) === drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix
      && !/^\d+$/.test(advantageCard.substring(16, 9))) {
      return false;
    }

    return true;
  },

  // Helper function for Advantage card status.
  isAllItemsExcludedForAdvCard: (totals) => {
    if (typeof drupalSettings.alshaya_spc.advantageCard !== 'undefined'
      && drupalSettings.user.uid
      && drupalSettings.alshaya_spc.advantageCard.enabled
      && typeof drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix !== 'undefined'
      && drupalSettings.alshaya_spc.advantageCard.advantageCardPrefix
      && typeof totals.allExcludedForAdcard !== 'undefined'
      && totals.allExcludedForAdcard === 'true') {
      return true;
    }

    return false;
  },

};

export default Advantagecard;
