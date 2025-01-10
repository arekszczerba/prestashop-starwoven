import {expect} from 'chai';
import testContext from '@utils/testContext';

// Import commonTests
import setMultiStoreStatus from '@commonTests/BO/advancedParameters/multistore';

// Import pages
import addEmployeePage from '@pages/BO/advancedParameters/team/add';
import rolesPage from '@pages/BO/advancedParameters/team/roles';
import addRolePage from '@pages/BO/advancedParameters/team/roles/add';
import logsPage from '@pages/BO/advancedParameters/logs';
import webservicePage from '@pages/BO/advancedParameters/webservice';
import addWebservicePage from '@pages/BO/advancedParameters/webservice/add';
import multiStorePage from '@pages/BO/advancedParameters/multistore';
import addShopGroupPage from '@pages/BO/advancedParameters/multistore/add';
import addShopPage from '@pages/BO/advancedParameters/multistore/shop/add';
import shopPage from '@pages/BO/advancedParameters/multistore/shop';

import {
  boDashboardPage,
  boLoginPage,
  boInformationPage,
  boPerformancePage,
  boAdministrationPage,
  boEmailPage,
  boImportPage,
  boEmployeesPage,
  boSqlManagerPage,
  boSqlManagerCreatePage,
  boDbBackupPage,
  boFeatureFlagPage,
  boApiClientsPage,
  boApiClientsCreatePage,

  type BrowserContext,
  type Page,
  utilsPlaywright,
  //BOBasePage,
} from '@prestashop-core/ui-testing';

const baseContext: string = 'audit_BO_advancedParameters';

describe('BO - Advanced Parameters', async () => {
  let browserContext: BrowserContext;
  let page: Page;

  //Pre-condition: Enable multistore
  setMultiStoreStatus(true, `${baseContext}_preTest`);

  before(async function () {
    utilsPlaywright.setErrorsCaptured(true);

    browserContext = await utilsPlaywright.createBrowserContext(this.browser);
    page = await utilsPlaywright.newTab(browserContext);
  });

  after(async () => {
    await utilsPlaywright.closeBrowserContext(browserContext);
  });

  it('should login in BO', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'loginBO', baseContext);

    await boLoginPage.goTo(page, global.BO.URL);
    await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

    const pageTitle = await boDashboardPage.getPageTitle(page);
    expect(pageTitle).to.contains(boDashboardPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Informations\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToInformationsPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.informationLink,
    );

    const pageTitle = await boInformationPage.getPageTitle(page);
    expect(pageTitle).to.contains(boInformationPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Performance\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToPerformancePage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.performanceLink,
    );

    const pageTitle = await boPerformancePage.getPageTitle(page);
    expect(pageTitle).to.contains(boPerformancePage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Administration\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToAdministrationPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.administrationLink,
    );

    const pageTitle = await boAdministrationPage.getPageTitle(page);
    expect(pageTitle).to.contains(boAdministrationPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > E-mail\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToEmailPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.emailLink,
    );

    const pageTitle = await boEmailPage.getPageTitle(page);
    expect(pageTitle).to.contains(boEmailPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Import\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToImportPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.importLink,
    );
    await boImportPage.closeSfToolBar(page);

    const pageTitle = await boImportPage.getPageTitle(page);
    expect(pageTitle).to.contains(boImportPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Team\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToAdvancedParamsPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.teamLink,
    );
    await boEmployeesPage.closeSfToolBar(page);

    const pageTitle = await boEmployeesPage.getPageTitle(page);
    expect(pageTitle).to.contains(boEmployeesPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Team > Employee > Add new employee\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToNewEmployeePage', baseContext);

    await boEmployeesPage.goToAddNewEmployeePage(page);

    const pageTitle = await addEmployeePage.getPageTitle(page);
    expect(pageTitle).to.contains(addEmployeePage.pageTitleCreate);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Roles\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToRolesPage', baseContext);

    await boEmployeesPage.goToRolesPage(page);

    const pageTitle = await rolesPage.getPageTitle(page);
    expect(pageTitle).to.contains(rolesPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Roles > Add new role page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToNewRolePage', baseContext);

    await rolesPage.goToAddNewRolePage(page);

    const pageTitle = await addRolePage.getPageTitle(page);
    expect(pageTitle).to.contains(addRolePage.pageTitleCreate);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Roles > Edit role page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToEditRole', baseContext);

    await boEmployeesPage.goToRolesPage(page);
    await rolesPage.goToEditRolePage(page, 1);

    const pageTitle = await addRolePage.getPageTitle(page);
    expect(pageTitle).to.contains(addRolePage.pageTitleEdit('SuperAdmin'));

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Permissions\' tab', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToPermissionsTab', baseContext);

    const isTabOpened = await boEmployeesPage.goToPermissionsTab(page);
    expect(isTabOpened, 'Permissions tab is not opened!').to.eq(true);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Database\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToDatabasePageToCreateNewSQLQuery', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.databaseLink,
    );

    await boSqlManagerPage.closeSfToolBar(page);

    const pageTitle = await boSqlManagerPage.getPageTitle(page);
    expect(pageTitle).to.contains(boSqlManagerPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'New SQL query\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToNewSQLQueryPage', baseContext);

    await boSqlManagerPage.goToNewSQLQueryPage(page);

    const pageTitle = await boSqlManagerCreatePage.getPageTitle(page);
    expect(pageTitle).to.contains(boSqlManagerCreatePage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'DB Backup\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToDbBackupPage', baseContext);

    await boSqlManagerPage.goToDbBackupPage(page);

    const pageTitle = await boDbBackupPage.getPageTitle(page);
    expect(pageTitle).to.contains(boDbBackupPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Logs\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToLogsPageToEraseLogs', baseContext);

    await boDashboardPage.goToSubMenu(page, boDashboardPage.advancedParametersLink, boDashboardPage.logsLink);
    await logsPage.closeSfToolBar(page);

    const pageTitle = await logsPage.getPageTitle(page);
    expect(pageTitle).to.contains(logsPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Webservice\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToWebservicePage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.webserviceLink,
    );
    await webservicePage.closeSfToolBar(page);

    const pageTitle = await webservicePage.getPageTitle(page);
    expect(pageTitle).to.contains(webservicePage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Webservice > Add new webservice key\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToAddNewWebserviceKeyPage', baseContext);

    await webservicePage.goToAddNewWebserviceKeyPage(page);

    const pageTitle = await addWebservicePage.getPageTitle(page);
    expect(pageTitle).to.contains(addWebservicePage.pageTitleCreate);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > Multistore\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToMultiStorePage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.multistoreLink,
    );

    const pageTitle = await multiStorePage.getPageTitle(page);
    expect(pageTitle).to.contains(multiStorePage.pageTitle);
  });

  it('should go to \'Advanced Parameters > Multistore > Add new shop group\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToAddNewShopGroupPage', baseContext);

    await multiStorePage.goToNewShopGroupPage(page);

    const pageTitle = await addShopGroupPage.getPageTitle(page);
    expect(pageTitle).to.contains(addShopGroupPage.pageTitleCreate);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  // CANT FIND GROUP SHOP NAME
  // it('should go to \'Advanced Parameters > Multistore > Edit shop group\' page', async function () {
  //   await testContext.addContextItem(this, 'testIdentifier', 'goToEditShopGroupPage', baseContext);
  //   await page.pause();

  //   await boDashboardPage.goToSubMenu(
  //     page,
  //     boDashboardPage.advancedParametersLink,
  //     boDashboardPage.multistoreLink,
  //   );
  //   // console.log('edit shop group');
  //   await multiStorePage.filterTable(page, 'a!name', 'Default');
  //   // console.log('test');
  //   // await multiStorePage.gotoEditShopGroupPage(page, 1);

  //   // const pageTitle = await addShopGroupPage.getPageTitle(page);
  //   // expect(pageTitle).to.contains(addShopGroupPage.pageTitleEdit);

  //   const pageTitle = await multiStorePage.getPageTitle(page);
  //   expect(pageTitle).to.contains(multiStorePage.pageTitle);

  //   const jsErrors = utilsPlaywright.getJsErrors();
  //   expect(jsErrors.length).to.equals(0);
  // });

  it('should go to \'Advanced Parameters > Multistore > Add new shop\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToAddNewShopPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.multistoreLink,
    );

    await multiStorePage.goToNewShopPage(page);

    const pageTitle = await addShopPage.getPageTitle(page);
    expect(pageTitle).to.contains(addShopPage.pageTitleCreate);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  // CANT FIND SHOP NAME
  // it('should go to \'Advanced Parameters > Multistore > Edit shop\' page', async function () {
  //   await testContext.addContextItem(this, 'testIdentifier', 'goToEditShopPage', baseContext);

  //   // const shopName = process.env.SHOP_NAME;
  //   // console.log('stringify my env var =' + shopName?.toString());
  //   // const testShopName = shopName?.toString();
  //   // console.log('used my string variable =' + testShopName);
  //   await page.pause();

  //   await shopPage.filterTable(page, 'a!name', 'ps900-adv');
  //   console.log('nom de ma shop en dur');

  //   // await shopPage.filterTable(page, 'a!name', `${testShopName}`);
  //   // console.log('nom de ma shop avec la varaible');


  //   await shopPage.gotoEditShopPage(page, 1);
  //   console.log('yes');
  //   const pageTitle = await addShopPage.getPageTitle(page);
  //   expect(pageTitle).to.contains(addShopPage.pageTitleEdit);

  //   const jsErrors = utilsPlaywright.getJsErrors();
  //   expect(jsErrors.length).to.equals(0);
  // });

  it('should go to \'Advanced Parameters > New & Experimental Features\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToFeatureFlagPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.featureFlagLink,
    );
    await boFeatureFlagPage.closeSfToolBar(page);

    const pageTitle = await boFeatureFlagPage.getPageTitle(page);
    expect(pageTitle).to.contains(boFeatureFlagPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > API Client\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToAdminAPIPage', baseContext);

    await boDashboardPage.goToSubMenu(
      page,
      boDashboardPage.advancedParametersLink,
      boDashboardPage.adminAPILink,
    );

    const pageTitle = await boApiClientsPage.getPageTitle(page);
    expect(pageTitle).to.eq(boApiClientsPage.pageTitle);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  it('should go to \'Advanced Parameters > API Client > Add New API Client\' page', async function () {
    await testContext.addContextItem(this, 'testIdentifier', 'goToNewAPIClientPage', baseContext);

    await boApiClientsPage.goToNewAPIClientPage(page);

    const pageTitle = await boApiClientsCreatePage.getPageTitle(page);
    expect(pageTitle).to.eq(boApiClientsCreatePage.pageTitleCreate);

    const jsErrors = utilsPlaywright.getJsErrors();
    expect(jsErrors.length).to.equals(0);
  });

  // NOT STARTED
  // it('should go to \'Advanced Parameters > Security\' page', async function () {
  //   await testContext.addContextItem(this, 'testIdentifier', 'goToSecurityPage', baseContext);

  //   const jsErrors = utilsPlaywright.getJsErrors();
  //   expect(jsErrors.length).to.equals(0);
  // });
});