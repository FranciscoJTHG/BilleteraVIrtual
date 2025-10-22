module.exports = {
  testEnvironment: 'node',
  testMatch: ['**/tests/e2e/**/*.test.js'],
  testTimeout: 30000,
  verbose: true,
  setupFilesAfterEnv: ['<rootDir>/tests/e2e/setup.js']
};
