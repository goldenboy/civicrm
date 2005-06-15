# Generated by MaxQ [com.bitmechanic.maxq.generator.JythonCodeGenerator]
from PyHttpTestCase import PyHttpTestCase
from com.bitmechanic.maxq import Config
import commonConst, commonAPI
global validatorPkg
if __name__ == 'main':
    validatorPkg = Config.getValidatorPkgName()
# Determine the validator for this testcase.
exec 'from '+validatorPkg+' import Validator'


# definition of test class
class testCustomDataAllByTab(PyHttpTestCase):
    #def setUp(self):
    #    global db
    #    db = commonAPI.dbStart()
    
    #def tearDown(self):
    #    commonAPI.dbStop(db)
    
    def runTest(self):
        self.msg('Test started')

        drupal_path = commonConst.DRUPAL_PATH

        commonAPI.login(self)

        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/cd''') % drupal_path)
        url = "%s/civicrm/contact/view/cd" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 5 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        #self.msg("Testing URL: %s" % self.replaceURL('''http://localhost/favicon.ico'''))
        #url = "http://localhost/favicon.ico"
        #params = None
        #Validator.validateRequest(self, self.getMethod(), "get", url, params)
        #self.get(url, params)
        #self.msg("Response code: %s" % self.getResponseCode())
        #self.assertEquals("Assert number 6 failed", 404, self.getResponseCode())
        #Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''cid''', '''68'''),
            ('''action''', '''update'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/cd?cid=68&action=update''') % drupal_path)
        url = "%s/civicrm/contact/view/cd" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 7 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        #self.msg("Testing URL: %s" % self.replaceURL('''http://localhost/favicon.ico'''))
        #url = "http://localhost/favicon.ico"
        #params = None
        #Validator.validateRequest(self, self.getMethod(), "get", url, params)
        #self.get(url, params)
        #self.msg("Response code: %s" % self.getResponseCode())
        #self.assertEquals("Assert number 8 failed", 404, self.getResponseCode())
        #Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''_qf_default''', '''CustomData:next'''),
            ('''1_1_registered_voter''', '''yes'''),
            ('''1_2_party_registration''', '''Congress'''),
            ('''1_3_date_last_voted[d]''', '''11'''),
            ('''1_3_date_last_voted[M]''', '''5'''),
            ('''1_3_date_last_voted[Y]''', '''1992'''),
            ('''1_4_voting_precinct''', ''''''),
            ('''2_6_school_college''', '''R R Vidyalaya'''),
            ('''2_5_degree''', '''Masters Of Science'''),
            ('''2_7_marks''', '''99'''),
            ('''2_8_date_of_degree[d]''', '''18'''),
            ('''2_8_date_of_degree[M]''', '''4'''),
            ('''2_8_date_of_degree[Y]''', '''2002'''),
            ('''_qf_CustomData_next''', '''Save'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/cd?_qf_default=CustomData:next&1_1_registered_voter=yes&1_2_party_registration=Congress&1_3_date_last_voted[d]=11&1_3_date_last_voted[M]=5&1_3_date_last_voted[Y]=1992&1_4_voting_precinct=&2_6_school_college=R R Vidyalaya&2_5_degree=Masters Of Science&2_7_marks=99&2_8_date_of_degree[d]=18&2_8_date_of_degree[M]=4&2_8_date_of_degree[Y]=2002&_qf_CustomData_next=Save''') % drupal_path)
        url = "%s/civicrm/contact/view/cd" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "post", url, params)
        self.post(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 9 failed", 302, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''action''', '''browse'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/cd?action=browse''') % drupal_path)
        url = "%s/civicrm/contact/view/cd" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 10 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        #self.msg("Testing URL: %s" % self.replaceURL('''http://localhost/favicon.ico'''))
        #url = "http://localhost/favicon.ico"
        #params = None
        #Validator.validateRequest(self, self.getMethod(), "get", url, params)
        #self.get(url, params)
        #self.msg("Response code: %s" % self.getResponseCode())
        #self.assertEquals("Assert number 11 failed", 404, self.getResponseCode())
        #Validator.validateResponse(self, self.getMethod(), url, params)
        
        self.msg('Test successfully complete.')
    # ^^^ Insert new recordings here.  (Do not remove this line.)


# Code to load and run the test
if __name__ == 'main':
    test = testCustomDataAllByTab("testCustomDataAllByTab")
    test.Run()
