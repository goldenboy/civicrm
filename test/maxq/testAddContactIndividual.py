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
class addIndividual_complete(PyHttpTestCase):
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
            ('''c_type''', '''Individual'''),
            ('''reset''', '''1'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/addI?c_type=Individual&reset=1''') % drupal_path)
        url = "%s/civicrm/contact/addI" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 6 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        params = [
            ('''_qf_default''', '''Edit:next'''),
            ('''prefix''', '''Dr'''),
            ('''first_name''', '''Lalit'''),
            ('''middle_name''', '''S'''),
            ('''last_name''', '''Zope'''),
            ('''suffix''', '''none'''),
            ('''job_title''', '''SE'''),
            ('''greeting_type''', '''Formal'''),
            ('''nick_name''', ''''''),
            ('''home_URL''', ''''''),
            ('''privacy[do_not_phone]''', ''''''),
            ('''privacy[do_not_email]''', ''''''),
            ('''__privacy[do_not_mail]''', '''1'''),
            ('''privacy[do_not_mail]''', '''1'''),
            ('''privacy[do_not_trade]''', ''''''),
            ('''preferred_communication_method''', '''Email'''),
            ('''location[1][location_type_id]''', '''1'''),
            ('''location[1][is_primary]''', '''1'''),
            ('''location[1][phone][1][phone_type]''', '''Phone'''),
            ('''location[1][phone][1][phone]''', '''1234567'''),
            ('''location[1][phone][2][phone_type]''', '''Mobile'''),
            ('''location[1][phone][2][phone]''', '''2345567'''),
            ('''location[1][phone][3][phone_type]''', ''''''),
            ('''location[1][phone][3][phone]''', ''''''),
            ('''location[1][email][1][email]''', '''manish111@lycos.com'''),
            ('''location[1][email][2][email]''', '''manish111@indiatimes.com'''),
            ('''location[1][email][3][email]''', ''''''),
            ('''location[1][im][1][provider_id]''', '''2'''),
            ('''location[1][im][1][name]''', '''HOLA'''),
            ('''location[1][im][2][provider_id]''', '''1'''),
            ('''location[1][im][2][name]''', '''Hello'''),
            ('''location[1][im][3][provider_id]''', ''''''),
            ('''location[1][im][3][name]''', ''''''),
            ('''location[1][address][street_address]''', '''21,jeevan so. pvt. ltd. east street, kothrud, paud road'''),
            ('''location[1][address][supplemental_address_1]''', ''''''),
            ('''location[1][address][supplemental_address_2]''', ''''''),
            ('''location[1][address][city]''', '''Pune'''),
            ('''location[1][address][state_province_id]''', '''1113'''),
            ('''location[1][address][postal_code]''', '''7689385'''),
            ('''location[1][address][country_id]''', '''1101'''),
            ('''location[1][address][geo_code_1]''', ''''''),
            ('''location[1][address][geo_code_2]''', ''''''),
            ('''location[2][location_type_id]''', '''2'''),
            ('''location[2][phone][1][phone_type]''', '''Pager'''),
            ('''location[2][phone][1][phone]''', '''34-346777755'''),
            ('''location[2][phone][2][phone_type]''', '''Phone'''),
            ('''location[2][phone][2][phone]''', '''163837843'''),
            ('''location[2][phone][3][phone_type]''', ''''''),
            ('''location[2][phone][3][phone]''', ''''''),
            ('''location[2][email][1][email]''', '''xyz@yahoo.com'''),
            ('''location[2][email][2][email]''', ''''''),
            ('''location[2][email][3][email]''', ''''''),
            ('''location[2][im][1][provider_id]''', '''5'''),
            ('''location[2][im][1][name]''', '''Namste'''),
            ('''location[2][im][2][provider_id]''', ''''''),
            ('''location[2][im][2][name]''', ''''''),
            ('''location[2][im][3][provider_id]''', ''''''),
            ('''location[2][im][3][name]''', ''''''),
            ('''location[2][address][street_address]''', ''''''),
            ('''location[2][address][supplemental_address_1]''', ''''''),
            ('''location[2][address][supplemental_address_2]''', ''''''),
            ('''location[2][address][city]''', ''''''),
            ('''location[2][address][state_province_id]''', ''''''),
            ('''location[2][address][postal_code]''', ''''''),
            ('''location[2][address][postal_code_suffix]''', ''''''),
            ('''location[2][address][country_id]''', ''''''),
            ('''location[2][address][geo_code_1]''', ''''''),
            ('''location[2][address][geo_code_2]''', ''''''),
            ('''gender''', '''Male'''),
            ('''birth_date[d]''', '''21'''),
            ('''birth_date[M]''', '''10'''),
            ('''birth_date[Y]''', '''1981'''),
            ('''note''', '''hi this is manish  from india team working on civiCRM nice to meet you'''),
            ('''_qf_Edit_next_view''', '''Save'''),]
        url = "%s/civicrm/contact/addI" % drupal_path
        self.msg("Testing URL: %s" % url)
        
        queryID = "select id from civicrm_contact where sort_name=\'%s, %s\'" % (params[4][1], params[2][1])
        cid     = db.loadVal(queryID)
        print "%s" % queryID
        print "%s" % cid
        if cid :
            Validator.validateRequest(self, self.getMethod(), "post", url, params)
            self.post(url, params)
            self.msg("Response code: %s" % self.getResponseCode())
            self.assertEquals("Assert number 7 failed", 200, self.getResponseCode())
            Validator.validateResponse(self, self.getMethod(), url, params)
            print "******************************************************************"
            print "Individual Contact \'%s, %s\' already exists" % (params[4][1], params[2][1])
            print "******************************************************************"
        else :
            Validator.validateRequest(self, self.getMethod(), "post", url, params)
            self.post(url, params)
            self.msg("Response code: %s" % self.getResponseCode())
            self.assertEquals("Assert number 8 failed", 302, self.getResponseCode())
            Validator.validateResponse(self, self.getMethod(), url, params)
            print "******************************************************************"
            print "Individual Contact \'%s, %s\' Added Successfully" % (params[4][1], params[2][1])
            print "******************************************************************"
            cid = db.loadVal(queryID)
        
        print "%s" % cid
        CID = '''%s''' % cid
        params = [
           ('''reset''', '''1'''),
           ('''cid''', CID),]
        url = "%s/civicrm/contact/view" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 9 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        commonAPI.logout(self)
        self.msg("Test successfully complete")
    # ^^^ Insert new recordings here.  (Do not remove this line.)


# Code to load and run the test
if __name__ == 'main':
    test = addIndividual_complete("addIndividual_complete")
    test.Run()
