module.exports = {
  testEnvironment: 'node',
  collectCoverageFrom: [
    'src/controllers/**/*.js',
    'src/middlewares/**/*.js',
    'src/validators/**/*.js'
  ],
  coverageThreshold: {
    global: {
      branches: 40,
      functions: 40,
      lines: 40,
      statements: 40
    }
  },
  testMatch: ['**/tests/**/*.test.js'],
  verbose: true
};

