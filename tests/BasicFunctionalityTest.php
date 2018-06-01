<?php

/*
 * This file is part of the PHPDoc Formatter application.
 * https://github.com/SinSquare/phpdoc-formatter
 *
 * (c) Ábel Katona
 *
 * This source file is subject to the MIT license that is bundled with this source code in the file LICENSE.
 */

namespace PhpDocFormatter\Tests;

use PhpDocFormatter\Application;
use PhpDocFormatter\Config;
use PHPUnit\Framework\TestCase;

class BasicFunctionalityTest extends TestCase
{
    public function testDocCommentExtraction()
    {
        $file = '
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
 */

/* Some comment
/** bad1 */
/** bad 2
* sdfsdf
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
        /* Some other comment
        return;
    }
}
';
        $config = Config::create();
        $config->setFinder(array());
        $app = new Application($config);

        $docComments = $this->invokeMethod($app, 'findAllDocDomment', array($file));

        $this->assertCount(3, $docComments);

        $fst = '/**
 * Sample text here
 */
';
        $scnd = '/**
 * @SWG\Info(
 *     title="API",
 *     version="1.0"
 * )
 */
';

        $thrd = '    /**
     * Sends a successfull(data key) JSON API response.
     * 
     * 
     * @param array    $data        the data to send
     * @param int      $code        HTTP response code
     * @param array    $headers     array of headers
     * 
     * @return JsonResponse
     */
';
        $expected = array($fst, $scnd, $thrd);

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $docComments[$key]['match']);
            $this->assertEquals(strlen($value), $docComments[$key]['length']);

            $pos = strpos($file, $value);
            $this->assertEquals($pos, $docComments[$key]['offset']);
        }
    }

    public function testDocBodyIdent()
    {
        $values = array(
            "/**\n" => '',
            "    /**\n" => '    ',
            "\t/**\n" => "\t",
            "###/**\n" => '###',
        );

        $config = Config::create();
        $config->setFinder(array());
        $app = new Application($config);

        foreach ($values as $key => $value) {
            $ident = $this->invokeMethod($app, 'getDocBodyIdent', array($key));
            $this->assertEquals($value, $ident);
        }
    }

    public function testGetDocBody()
    {
        $in = '
    /**
     *       Sends a successfull(data key) JSON API response.
         *      
    *
*
       *
    *
    *
     *     
  *          @param array    $data        the data to send
        * 
  *    @return JsonResponse
       */
';
        $out = 'Sends a successfull(data key) JSON API response.


@param array    $data        the data to send

@return JsonResponse';

        $config = Config::create();
        $config->setFinder(array());
        $app = new Application($config);

        $o = $this->invokeMethod($app, 'getDocBody', array($in));
        $this->assertEquals($out, $o);
    }

    public function testNormalizeDocBody()
    {
        $in = '{@inheritdoc}
@Route("/list", name="list")
@SWG\Swagger(
host=API_HOST
)';
        $out = "{@inheritdoc}
@Route(\"/list\", name=\"list\")
@SWG\Swagger(
\thost=API_HOST
)";
        $config = Config::create();
        $config->setFinder(array());
        $config->setIdent("\t");

        $app = new Application($config);

        $o = $this->invokeMethod($app, 'normalizeDocBody', array($in));
        $this->assertEquals($out, $o);
    }

    public function testReconstructDocBody()
    {
        $in = "{@inheritdoc}
@Route(\"/list\", name=\"list\")
@SWG\Swagger(
\thost=API_HOST
)";
        $out = "\t\t/**
\t\t * {@inheritdoc}
\t\t * @Route(\"/list\", name=\"list\")
\t\t * @SWG\Swagger(
\t\t * \thost=API_HOST
\t\t * )
\t\t */
";

        $config = Config::create();
        $config->setFinder(array());
        $config->setIdent("\t");

        $app = new Application($config);
        $newLine = $this->invokeMethod($app, 'getFileDominantLineEnding', array($in));
        $config->setNewLine($newLine);

        $o = $this->invokeMethod($app, 'reconstructDocBody', array($in, "\t\t"));
        $this->assertEquals($out, $o);
    }

    public function testReconstructFile()
    {
        $file = '
<?php

/**
     * Sample text here
   */

namespace Controller;

/**
 *      @SWG\Info(
    *     title="API",
    *     version="1.0"
 *   )
 */

/* Some comment
/** bad1 */
/** bad 2
* sdfsdf
abstract class AbstractApiController
{
    /**
     * Sends a successfull(data key) JSON API response.
 *          @param array    $headers     array of headers
 *               
 *     @return JsonResponse
 */
    protected function success(array $data, $code = 200, array $headers = array()): JsonResponse
    {
        /* Some other comment
        return;
    }
}
';
        $newFile = '
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
 */

/* Some comment
/** bad1 */
/** bad 2
* sdfsdf
abstract class AbstractApiController
{
    /**
     * Sends a successfull(data key) JSON API response.
     * @param array    $headers     array of headers
     *
     * @return JsonResponse
     */
    protected function success(array $data, $code = 200, array $headers = array()): JsonResponse
    {
        /* Some other comment
        return;
    }
}
';
        $config = Config::create();
        $config->setFinder(array());
        $app = new Application($config);

        $newLine = $this->invokeMethod($app, 'getFileDominantLineEnding', array($file));
        $config->setNewLine($newLine);

        $docComments = $this->invokeMethod($app, 'findAllDocDomment', array($file));

        foreach ($docComments as $key => $match) {
            $value = $match['match'];
            $ident = $this->invokeMethod($app, 'getDocBodyIdent', array($value));
            if (null === $ident) {
                throw new \Exception('not good');
            }

            $norm = $this->invokeMethod($app, 'getDocBody', array($value));
            $norm = $this->invokeMethod($app, 'normalizeDocBody', array($norm));
            $norm = $this->invokeMethod($app, 'reconstructDocBody', array($norm, $ident));
            $docComments[$key]['formatted'] = $norm;
        }

        $nF = $this->invokeMethod($app, 'reconstructFile', array($file, $docComments));

        $this->assertEquals($newFile, $nF);
    }

    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
