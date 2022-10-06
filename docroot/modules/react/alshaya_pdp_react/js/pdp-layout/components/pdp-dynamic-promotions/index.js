import React from 'react';
import parse from 'html-react-parser';

class PdpDynamicPromotions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      label: null,
    };
  }

  componentDidMount() {
    const { skuMainCode, cartDataValue } = this.props;
    this.refreshDynamicPromoLabels(skuMainCode, cartDataValue);
  }

  componentDidUpdate(prevProps) {
    const { skuMainCode, cartDataValue } = this.props;
    if (prevProps.cartDataValue !== cartDataValue) {
      this.refreshDynamicPromoLabels(skuMainCode, cartDataValue);
    }
  }

  refreshDynamicPromoLabels = async (skuMainCode, cartDataValue) => {
    if (Drupal.alshayaPromotions && cartDataValue !== null) {
      const response = Drupal.alshayaPromotions.getDynamicLabel(skuMainCode, cartDataValue);
      if (response && response.label) {
        this.setState({
          label: response.label,
        });
      }
    }
  }

  render() {
    const { label } = this.state;

    return (label) ? (
      <p>{parse(label)}</p>
    ) : null;
  }
}
export default PdpDynamicPromotions;
