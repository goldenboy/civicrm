cd ..\xml
php GenCode.php

cd ..\sql
mysql -u crm -p -e "\. Contacts.sql"
mysql -u crm -p -e "\. FixedData.sql"

php GenerateContactData.php

