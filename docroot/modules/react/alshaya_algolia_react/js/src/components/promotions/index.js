import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import { isPromotionFrameEnabled } from '../../utils/indexUtils';
import fixHref from '../../../../../js/utilities/helpers';

const Promotion = ({ promotion }) => (
  <span className="sku-promotion-item">
    {(drupalSettings.algoliaSearch.pageSubType !== 'undefined'
      && drupalSettings.algoliaSearch.pageSubType === 'promotion'
      && ((drupalSettings.algoliaSearch.promotionNodeId !== 'undefined'
        && promotion.id !== 'undefined'
        && drupalSettings.algoliaSearch.promotionNodeId === promotion.id)
      // Adding check of rule_id as for V3, we don't have promotion id but we
      // have rule_id. So using promotion.id for V1 and promotion.rule_id for
      // V3.
      || (promotion.rule_id !== 'undefined'
        && drupalSettings.algoliaSearch.promotionNodeId === promotion.rule_id))) ? (
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
