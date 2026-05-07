-- Grant necessary permissions to the app user for multi-tenancy
GRANT ALL PRIVILEGES ON *.* TO 'app'@'%' WITH GRANT OPTION;
GRANT CREATE ON *.* TO 'app'@'%';
FLUSH PRIVILEGES; 