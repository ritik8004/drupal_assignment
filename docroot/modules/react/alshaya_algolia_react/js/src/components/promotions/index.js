import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import { isPromotionFrameEnabled } from '../../utils/indexUtils';
import fixHref from '../../../../../js/utilities/helpers';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const isCurrentPromotion = (promotion) => {
  const settings = drupalSettings.algoliaSearch;

  // Return if page type is not promotion.
  if (!hasValue(settings.pageSubType)
    || settings.pageSubType !== 'promotion') {
    return false;
  }

  // Validate if promotion is same based on promotion id.
  if (hasValue(settings.promotionNodeId)
    && hasValue(promotion.id)
    && settings.promotionNodeId === promotion.id) {
    return true;
  }

  // Validate if promotion is same based on promotion rule_id.
  // Adding check of rule_id as for V3, we don't have promotion id but we
  // have rule_id. So using promotion.id for V1 and promotion.rule_id for
  // V3.
  if (hasValue(settings.promotionNodeId)
    && hasValue(promotion.rule_id)
    && settings.promotionNodeId === promotion.rule_id) {
    return true;
  }

  return false;
};

const Promotion = ({ promotion }) => (
  <span className="sku-promotion-item">
    {isCurrentPromotion(promotion) ? (
      <span className="sku-promotion-text">{promotion.text}</span>
    ) : (
      <>
        <ConditionalView condition={isPromotionFrameEnabled()}>
          <div className="sku-promotion-text">{promotion.text}</div>
          <a className="sku-promotion-link" href={fixHref(promotion[`url_${drupalSettings.path.currentLanguage}`])}>
            {Drupal.t('Shop all products in this offer')}
          </a>
        </ConditionalView>
        <ConditionalView condition={!isPromotionFrameEnabled()}>
          <a className="sku-promotion-link" href={fixHref(promotion[`url_${drupalSettings.path.currentLanguage}`])}>
            {promotion.text}
          </a>
        </ConditionalView>
      </>
    )}
  </span>
);

const Promotions = ({ promotions }) => {
  const promotionList = (promotions)
    ? promotions.map((promotion) => {
      if (typeof promotion.context === 'undefined' || promotion.context.includes('web') || !promotion.context.length) {
        return <Promotion key={promotion.text} promotion={promotion} />;
      }
      return '';
    })
    : '';
  if (promotionList !== '' && promotionList !== 'null') {
    return <div className="promotions">{promotionList}</div>;
  }
  return (null);
};

export default Promotions;
