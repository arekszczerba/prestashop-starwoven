import {
  type Browser,
  type Page,
  utilsCore,
  utilsPlaywright,
} from '@prestashop-core/ui-testing';

let screenshotNumber: number = 1;

/**
 * @module MochaHelper
 * @description Helper to define mocha hooks
 */

/**
 * @function before
 * @description Create unique browser for all mocha run
 */
before(async function () {
  this.browser = await utilsPlaywright.createBrowser();
});

/**
 * @function after
 * @description Close browser after finish the run
 */
after(async function () {
  await utilsPlaywright.closeBrowser(this.browser);
});

const takeScreenShotAfterStep = async (browser: Browser, screenshotPath: string) => {
  const pages: Page[] = browser.contexts()[0].pages();

  for (let incPage = 0; incPage < pages.length; incPage++) {
    await pages[incPage].bringToFront();
    await pages[incPage].screenshot({
      path: screenshotPath.replace('%d', incPage < 10 ? `0${incPage.toString()}` : incPage.toString()),
      fullPage: true,
    });
  }
};

/**
 * @function afterEach
 * @description Take a screenshot if a step is failed
 */
afterEach(async function () {
  // Take screenshot if demanded after failed step
  if (global.SCREENSHOT.AFTER_FAIL && this.currentTest?.state === 'failed') {
    await takeScreenShotAfterStep(this.browser, `${global.SCREENSHOT.FOLDER}/fail_test_${screenshotNumber}_%d.png`);
    screenshotNumber += 1;
  }
  if (global.SCREENSHOT.EACH_STEP) {
    const testPath = this.currentTest?.file;
    // eslint-disable-next-line no-unsafe-optional-chaining
    const folderPath = testPath?.slice(testPath?.indexOf('tests/UI') + 8).slice(0, -3);
    let stepId: string = `screenshot-${screenshotNumber}`;

    if (this.currentTest?.title) {
      stepId = `${screenshotNumber}-${this.currentTest?.title}`;
    }

    const screenshotPath = `${global.SCREENSHOT.FOLDER}${folderPath}/${utilsCore.slugify(stepId)}_%d.png`;
    await takeScreenShotAfterStep(this.browser, screenshotPath).catch((err) => {
      console.log(`screenshot for ${this.currentTest?.title} failed`, err);
    });
    screenshotNumber += 1;
  }
});
