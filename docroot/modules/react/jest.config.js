module.exports = {
  verbose: false,
  collectCoverage: false,
  testRegex: '(/tests/.*|\\.(test|spec))\\.(ts|tsx|js)$',
  transformIgnorePatterns: [
    'node_modules/(?!(jest-)?react-native|lottie-react-native)'
  ],
  moduleDirectories: ['node_modules', 'src'],
  coveragePathIgnorePatterns: ['/node_modules/', '/jest'],
  testEnvironment: 'jsdom',
  globals: {
    window: {},
  },
};
