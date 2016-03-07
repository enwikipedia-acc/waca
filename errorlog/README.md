# Error Log directory

This is the folder in which error logs get written by default. This is OK for a development environment, but this **MUST
BE CHANGED FOR PRODUCTION ENVIRONMENTS!**

Files in this directory can be read by calling something along the lines of:
```php
echo "<?php var_dump(unserialize(file_get_contents('1234.log')));" | php
```