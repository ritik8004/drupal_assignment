/**
 * Ifelse component.
 *
 * Accepts no more than two children with condition. If condition evalates
 * to true return the first child to render and else part renders second child.
 *
 * When given only one child, it renders only If the condition evaluats to true.
 */
const Ifelse = ({ condition, children }) => {
  const [IfEle = null, ElseEle = null] = children.length > 1 ? children : [children];
  return condition ? IfEle : ElseEle;
};

export default Ifelse;
