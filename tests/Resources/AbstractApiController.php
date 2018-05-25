<?php

/**
 * Sample text here
 */

namespace Controller;

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
abstract class AbstractApiController
{
    /**
     * Sends a successfull(data key) JSON API response.
     * 
     * 
     * @param array    $data        the data to send
     * @param int      $code        HTTP response code
     * @param array    $headers     array of headers
     * 
     * @return JsonResponse
     */
    protected function success(array $data, $code = 200, array $headers = array()): JsonResponse
    {
        $resp = array('data' => $data);

        return $this->jsonResponse($resp, $code, $headers);
    }
}
