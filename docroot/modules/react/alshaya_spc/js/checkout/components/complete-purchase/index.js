import React from 'react';

export default class CompletePurchase extends React.Component {

  render() {
    let class_name = this.props.enable ? 'active' : 'in-active';
    return <div className={class_name}>{Drupal.t('Complete purchase')}</div>  	
  }

}
