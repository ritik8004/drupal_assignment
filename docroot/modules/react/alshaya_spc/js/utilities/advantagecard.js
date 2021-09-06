const Advantagecard = {
  isAdvantageCardApplied: (items) => {
    let advantageCardApplied = false;
    Object.entries(items).forEach(([, item]) => {
      if (item.extension_attributes.adv_card_applicable) {
        advantageCardApplied = true;
      }
    });
    return advantageCardApplied;
  },

  isAdvantageCardEligibleProduct: (items, itemid) => {
    let advantageCardEligibleProduct = true;
    Object.entries(items).forEach(([, item]) => {
      if (item.item_id === itemid) {
        advantageCardEligibleProduct = item.extension_attributes.adv_card_applicable;
      }
    });
    return advantageCardEligibleProduct;
  },

};

export default Advantagecard;
