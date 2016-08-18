# mattjanssen/api-response-bundle

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)

`mattjanssen/api-response-bundle` is a slightly-opinionated Symfony bundle for transforming controller action returns
and exceptions into a standardized JSON response. The serializer and CORS headers can be configured globally, per path,
and per action via annotation.

## Install

Via Composer

``` bash
$ composer require mattjanssen/api-response-bundle
```

## Usage

In your API controllers, just return whatever you want serialized in the response. The ApiResponseBundle takes care of
turning that into an actual JSON response.

``` php
return [
    'id' => 5,
    'school' => $school,
    'users' => $users,
];
```

This would result in the following JSON return:

``` javascript
{
    data: {
        id: 5,
        school: ...,
        users: [ ... ]
    },
    error: null
}
```

## Status Codes
 
By default, responses are sent with the 200 OK status. In order to use a different status, use the `@ApiResponse` 
annotation on the controller action. This should only be used to change the success status codes. See the Error Response
section for handling error output.

``` php
/**
 * @ApiResponse(httpCode=201)
 */
public function createAction() {}
```

The resulting response would have the 201 CREATED status.

## Error Response

To respond with an error, throw any exception implementing the `ApiResponseExceptionInterface`. On the exception you can
optionally set the HTTP status code, the exception code, the exception message and the error data to be serialized into the 
response.

``` php
throw (new ApiResponseException())
    ->setHttpStatusCode(404)
    ->setCode(100404)
    ->setMessage('Could not find school.')
    ->setErrorData(['schoolId' => 42]);
```

This would result in the following JSON return with a 404 HTTP status:

``` javascript
{
    data: null,
    error: {
        code: 100404,
        message: 'Could not find school.',
        errorData: {
            schoolId: 42
        }
    }
}
```

## Exception Handling

Besides turning `ApiResponseExceptionInterface` exceptions into error responses, the bundle will also handle any
uncaught exceptions in the following manner:

### `HttpExceptionInterface`

The exception status code is used for both the response HTTP code and the error code. The error message is the
corresponding `Response::$statusTexts` array value. Error data is null.

### `AuthenticationException`

Both the response HTTP code and the error code are 401. The error message is "Unauthorized". Error data is null.

### `AccessDeniedException`

Both the response HTTP code and the error code are 403. The error message is "Forbidden". Error data is null.

### All Other Exceptions

Both the response HTTP code and the error code are 500.

If the Symfony kernel *is not in debug mode*, the error message is "Internal Server Error". Error data is null.

If the Symfony kernel *is in debug mode*, the error message is compiled from the exception class, message, file and 
line number. And the error data is the exception trace.

## Testing

``` bash
$ composer install --dev
$ vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mattjanssen/api-response-bundle.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mattjanssen/api-response-bundle
