import React from 'react';
import WishlistProduct from '../wishlist-product';

class WishlistProductList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      items: [],
    };
  }

  componentDidMount() {
    // @todo: Fetch the wishlist product list from API or Storage.
    // Set the data in state.
    this.setState({
      items: [
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
        {
          nid: '100',
          alt: 'Benchwright Console Table',
          title: 'Benchwright Console Table',
          original_price: 200,
          final_price: 150,
          url: 'https://www.potterybarn.ae/sites/g/files/bndsjb1296/files/styles/product_zoom_medium_606x504/public/media/website/var/assets/GroupProductImages/benchwright-console-table/201824_0225_benchwright-console-table-rustic-mahogany-z.129478.jpg?itok=PCfrTwVf',
        },
      ],
    });
  }

  generateProductGrid = (items) => {
    const productList = [];
    Object.entries(items).forEach(([key, product]) => {
      productList.push(
        <WishlistProduct
          key={key}
          item={product}
        />,
      );
    });

    return productList;
  };

  render() {
    const { items } = this.state;

    return (
      <div className="wishlist-list">
        { this.generateProductGrid(items) }
      </div>
    );
  }
}

export default WishlistProductList;
