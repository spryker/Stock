<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Stock\Communication;

use SprykerFeature\Zed\Stock\Business\StockFacade;
use SprykerFeature\Zed\Stock\Communication\Form\StockForm;
use SprykerFeature\Zed\Stock\Communication\Form\StockProductForm;
use SprykerFeature\Zed\Stock\Communication\Grid\StockGrid;
use SprykerFeature\Zed\Stock\Communication\Grid\StockProductGrid;
use SprykerFeature\Zed\Stock\Persistence\StockQueryContainer;
use SprykerEngine\Zed\Kernel\Communication\AbstractCommunicationDependencyContainer;
use Symfony\Component\HttpFoundation\Request;

class StockDependencyContainer extends AbstractCommunicationDependencyContainer
{

    /**
     * @return StockFacade
     */
    public function getStockFacade()
    {
        return $this->getLocator()->stock()->facade();
    }

    /**
     * @param Request $request
     *
     * @return StockForm
     */
    public function getStockForm(Request $request)
    {
        return new StockForm(
            $request
        );
    }

    /**
     * @param Request $request
     *
     * @return StockProductForm
     */
    public function getStockProductForm(Request $request)
    {
        return new StockProductForm(
            $request,
            $this->getQueryContainer()
        );
    }

    /**
     * @param Request $request
     *
     * @return StockGrid
     */
    public function getStockGrid(Request $request)
    {
        return new StockGrid(
            $this->getQueryContainer()->queryAllStockTypes(),
            $request
        );
    }

    /**
     * @param Request $request
     *
     * @return StockProductGrid
     */
    public function getStockProductGrid(Request $request)
    {
        return new StockProductGrid(
            $this->getQueryContainer()->queryAllStockProductsJoinedStockJoinedProduct(),
            $request
        );
    }

    /**
     * @return StockQueryContainer
     */
    public function getQueryContainer()
    {
        return $this->getLocator()->stock()->queryContainer();
    }

}
