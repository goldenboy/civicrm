# Generated by MaxQ [com.bitmechanic.maxq.generator.JythonCodeGenerator]
from PyHttpTestCase import PyHttpTestCase
from com.bitmechanic.maxq import Config
from com.bitmechanic.maxq import DBUtil
import commonConst, commonAPI
global validatorPkg
if __name__ == 'main':
    validatorPkg = Config.getValidatorPkgName()
# Determine the validator for this testcase.
exec 'from '+validatorPkg+' import Validator'


# definition of test class
class testAdminEnableDisableCustomData(PyHttpTestCase):
    def setUp(self):
        global db
        db = commonAPI.dbStart()
    
    def tearDown(self):
        commonAPI.dbStop(db)
    
    def runTest(self):
        self.msg('Test started')
        
        drupal_path = commonConst.DRUPAL_PATH
        
        commonAPI.login(self)
        
        params = [
            ('''reset''', '''1'''),]
        url = "%s/civicrm/admin" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 6 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        name    = 'Test Group'
        queryID = 'select id from civicrm_custom_group where title like \'%%%s%%\'' % name
        
        gid     = db.loadVal(queryID)
        
        if gid :
            GID = '''%s''' % gid
            
            params = [
                ('''action''', '''disable'''),
                ('''reset''', '''1'''),
                ('''id''', GID),]
            url = "%s/civicrm/admin/custom/group" % drupal_path
            self.msg("Testing URL: %s" % url)
            Validator.validateRequest(self, self.getMethod(), "get", url, params)
            self.get(url, params)
            self.msg("Response code: %s" % self.getResponseCode())
            self.assertEquals("Assert number 7 failed", 200, self.getResponseCode())
            Validator.validateResponse(self, self.getMethod(), url, params)
            
            params = [
                ('''action''', '''enable'''),
                ('''reset''', '''1'''),
                ('''id''', GID),]
            url = "%s/civicrm/admin/custom/group" % drupal_path
            self.msg("Testing URL: %s" % url)
            Validator.validateRequest(self, self.getMethod(), "get", url, params)
            self.get(url, params)
            self.msg("Response code: %s" % self.getResponseCode())
            self.assertEquals("Assert number 8 failed", 200, self.getResponseCode())
            Validator.validateResponse(self, self.getMethod(), url, params)
        else :
            print "****************************************************************"
            print "Custom Group \'%s\'not found." % name
            print "****************************************************************"
        commonAPI.logout(self)
        self.msg('Test successfully complete.')
    # ^^^ Insert new recordings here.  (Do not remove this line.)
        

# Code to load and run the test
if __name__ == 'main':
    test = testAdminEnableDisableCustomData("testAdminEnableDisableCustomData")
    test.Run()
