#!/etc/sh
php5 /var/www/ldap.php >> /var/www/ldap.log
php5 /var/www/synchronization.php >> /var/www/synchronization.log
php5 /var/www/backup.php >> /var/www/backup.log
php5 /var/www/clean.php >> /var/www/clean.log
