module.exports = {
  verbose: false,
  collectCoverage: false,
  testRegex: "(/__tests__/.*|(\\.|/)(test|spec))\\.(jsx?|ts)$",
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
