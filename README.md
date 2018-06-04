# Bencom Database converter

This Laravel console application lets you convert Bencom's CSV files to a SQL dataset.

## Usage

### Providers & signups

Make sure you use the `Aanmeldingen 2014` dataset from Bencom. 

Run the following command:

```
php artisan parse:signups ";" "PATH_TO_FILE"
```

Replace PATH_TO_FILE to the actual path of your CSV file.

### Press Expressions

Make sure you exported the `Mediauitingen` dataset from Bencom to a CSV. 

Run the following command:

```
php artisan parse:press ";" "PATH_TO_FILE"
```

Replace PATH_TO_FILE to the actual path of your CSV file. This command uses a `;` delimiter. If you have used another delimiter, make sure you change that in the command.