<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Stock\Helper;

use ArrayObject;
use Codeception\Module;
use Generated\Shared\DataBuilder\StockBuilder;
use Generated\Shared\DataBuilder\StockProductBuilder;
use Generated\Shared\Transfer\StockProductTransfer;
use Generated\Shared\Transfer\StockTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Stock\Persistence\SpyStockProductQuery;
use Orm\Zed\Stock\Persistence\SpyStockQuery;
use Orm\Zed\Stock\Persistence\SpyStockStore;
use Spryker\Zed\Stock\Business\StockFacadeInterface;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class StockDataHelper extends Module
{
    use LocatorHelperTrait;
    use DataCleanupHelperTrait;

    /**
     * @param array $seedData
     *
     * @return void
     */
    public function haveProductInStock(array $seedData = []): void
    {
        $stockFacade = $this->getStockFacade();

        $stockSeedData = [];
        if (isset($seedData[StockProductTransfer::FK_STOCK])) {
            $stockSeedData[StockTransfer::ID_STOCK] = $seedData[StockProductTransfer::FK_STOCK];
        }

        $stockTransfer = $this->haveStock($stockSeedData);
        $stockProductTransfer = (new StockProductBuilder($seedData))->build();
        $stockProductTransfer->setStockType($stockTransfer->getName());

        $idStockProduct = $stockFacade->createStockProduct($stockProductTransfer);

        $this->debug(sprintf(
            'Inserted StockProduct: %d of Concrete Product SKU: %s',
            $idStockProduct,
            $stockProductTransfer->getSku()
        ));

        $this->getDataCleanupHelper()->_addCleanup(function () use ($idStockProduct, $stockTransfer) {
            $this->cleanUpStockProduct($idStockProduct);
            $this->cleanUpStock($stockTransfer->getIdStock());
        });
    }

    /**
     * @param array $seedData
     *
     * @return \Generated\Shared\Transfer\StockTransfer
     */
    public function haveStock(array $seedData = []): StockTransfer
    {
        $stockTransfer = (new StockBuilder($seedData))->build();
        $stockResponseTransfer = $this->getStockFacade()->createStock($stockTransfer);

        $stockTransfer = $stockResponseTransfer->getStock();

        $this->debug(sprintf(
            'Inserted Stock: %d',
            $stockTransfer->getIdStock()
        ));

        $this->getDataCleanupHelper()->_addCleanup(function () use ($stockTransfer) {
            $this->cleanUpStock($stockTransfer->getIdStock());
        });

        return $stockTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\StockTransfer $stockTransfer
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    public function haveStockStoreRelation(StockTransfer $stockTransfer, StoreTransfer $storeTransfer): StoreRelationTransfer
    {
        $stockTransfer->requireIdStock();
        $storeTransfer->requireIdStore();

        $stockStoreEntity = (new SpyStockStore())
            ->setFkStore($storeTransfer->getIdStore())
            ->setFkStock($stockTransfer->getIdStock());
        $stockStoreEntity->save();

        $this->debug(sprintf(
            'Inserted Stock Store Relation: %d',
            $stockStoreEntity->getIdStockStore()
        ));

        $this->getDataCleanupHelper()->_addCleanup(function () use ($stockStoreEntity) {
            $stockStoreEntity->delete();
        });

        return (new StoreRelationTransfer())
            ->setIdEntity($stockTransfer->getIdStock())
            ->setIdStores([$storeTransfer->getIdStore()])
            ->setStores(new ArrayObject([$storeTransfer]));
    }

    /**
     * @return \Spryker\Zed\Stock\Business\StockFacadeInterface
     */
    protected function getStockFacade(): StockFacadeInterface
    {
        return $this->getLocator()->stock()->facade();
    }

    /**
     * @param int $idStockProduct
     *
     * @return void
     */
    protected function cleanUpStockProduct(int $idStockProduct): void
    {
        SpyStockProductQuery::create()
            ->filterByIdStockProduct($idStockProduct)
            ->delete();
    }

    /**
     * @param int $idStock
     *
     * @return void
     */
    protected function cleanUpStock(int $idStock): void
    {
        SpyStockQuery::create()
            ->filterByIdStock($idStock)
            ->delete();
    }
}
