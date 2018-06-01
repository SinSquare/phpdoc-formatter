[![Build Status](https://travis-ci.org/SinSquare/phpdoc-formatter.svg?branch=master)](https://travis-ci.org/SinSquare/phpdoc-formatter)

A simple tool for formatting PHPDoc

The tool currently fixes:
* Idention through multiline comments
* Removing more than 2 empty lines
* Fixing PHPDoc format

Usage:
```
phpdoc-formatter [--option value] [/path/to/project ...]
```
Options:
```
  --exclude (-e)    Exclude path(s)
                    ex: --exclude vendor,library
  --version (-v)    Displays the version

  --ident (-i)      Sets the ident character(s)
                    ex: --ident "    "
  --newline (-n)    Sets the newline character(s)
                    ex: --ident "\r\n"
  --help (-h)       Displays this message
```

Example:

Before:
```
/**
 * @SWG\Info(
* title="API",
 *    version="1.0"
 * )
 * 
 *    @SWG\Swagger(
 *   host=API_HOST,
 * basePath=API_BASE_PATH
 *   )
 * 
 *   @SWG\SecurityScheme(
 *  securityDefinition="JWTTokenAuth",
 *    type="apiKey",
 *  in="header",
 *     name="Authorization",
 *   description=API_DEFAULT_TOKEN,
 *   )
 */
```
After:
```
/**
 * @SWG\Info(
 *     title="API",
 *     version="1.0"
 * )
 * 
 * @SWG\Swagger(
 *     host=API_HOST,
 *     basePath=API_BASE_PATH
 * )
 * 
 * @SWG\SecurityScheme(
 *     securityDefinition="JWTTokenAuth",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description=API_DEFAULT_TOKEN,
 * )
 */
```