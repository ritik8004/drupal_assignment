import React from 'react';
import { connectMenu } from 'react-instantsearch-dom';

const Menu = (props) => {
  const { items, element } = props;

  return (
    <>
      <div>{element.dataset.label}</div>
      <ul>
        {items.map((item) => (
          <li key={item.label}>
            <a
              href={Drupal.url('shop-men/--size_rest-one_size_0')}
            >
              {item.label}
            </a>
          </li>
        ))}
      </ul>
    </>
  );
};

export default connectMenu(Menu);
