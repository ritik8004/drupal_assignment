import { connectSortBy } from 'react-instantsearch-dom';

const SortByList = ({ items, refine }) => (
  <ul>
    {items.map(item => (
      <li key={item.value}>
        <a
          href="#"
          style={{ fontWeight: item.isRefined ? 'bold' : '' }}
          onClick={event => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          {item.label}
        </a>
      </li>
    ))}
  </ul>
);

export default connectSortBy(SortByList);