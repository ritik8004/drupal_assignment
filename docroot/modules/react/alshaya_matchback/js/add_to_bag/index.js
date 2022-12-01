import AddToBagContainer from "../../../js/utilities/components/addtobag-container";

function MatchbackAddToBag(props) {
  const {sku, url} = props;
  return <AddToBagContainer
    url={url}
    sku={sku}
    stockQty='10'
    productData={{sku_type: 'configurable'}}
    isBuyable={true}
    // Pass extra information to the component for update the behaviour.
    extraInfo={{showAddToBag: true}}
    wishListButtonRef={{}}
    styleCode={null}
  />
}

export default MatchbackAddToBag;
