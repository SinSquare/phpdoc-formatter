<?php

/**
 * Sample text here
 */

namespace Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/currency", name="currency_")
 */
class CurrencyController extends AbstractApiController
{
    /**
     * {@inheritdoc}
     * 
     * @Route("/list", name="list")
     * @Method({"GET"})
     * 
     * @SWG\Get(
     *     path="/currency/list",
     *     summary="Lists Currencys",
     *     description="Currency listing",
     *     security={{"JWTTokenAuth":{}}},
     *     tags={"Currency"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="data",
     *                 type="array",
     *                 items=@SWG\Items(ref="#/definitions/currencyArrayDefinition")
     *             ),
     *             @SWG\Property(
     *                 property="meta",
     *                 type="object",
     *                 ref="#/definitions/metaArrayDefinition"
     *             ),
     *             @SWG\Property(
     *                 property="links",
     *                 type="object",
     *                 ref="#/definitions/linksArrayDefinition"
     *             ),
     *         )
     *     )
     * )
     */
    public function listAction(Request $request): JsonResponse
    {
        return parent::listAction($request);
    }
}
