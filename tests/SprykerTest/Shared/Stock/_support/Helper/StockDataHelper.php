<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Stock\Helper;

use ArrayObject;
use Codeception\Module;
use Generated\Shared\DataBuilder\StockProductBuilder;
use Generated\Shared\DataBuilder\TypeBuilder;
use Generated\Shared\Transfer\StockTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TypeTransfer;
use Orm\Zed\Stock\Persistence\SpyStockStore;
use Spryker\Zed\Stock\Business\StockFacadeInterface;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class StockDataHelper extends Module
{
    use LocatorHelperTrait;

    /**
     * @param array $seedData
     *
     * @return void
     */
    public function haveProductInStock(array $seedData = []): void
    {
        $stockFacade = $this->getStockFacade();

        $stockTransfer = $this->haveStock();
        $stockProductTransfer = (new StockProductBuilder($seedData))->build();
        $stockProductTransfer->setStockType($stockTransfer->getName());

        $stockFacade->createStockType($stockTransfer);
        $stockFacade->createStockProduct(
            (new StockProductBuilder($seedData))
                ->build()
                ->setStockType($stockTransfer->getName())
        );
    }

    /**
     * @param array $seedData
     *
     * @return \Generated\Shared\Transfer\TypeTransfer
     */
    public function haveStock(array $seedData = []): TypeTransfer
    {
        $stockTransfer = (new TypeBuilder($seedData))->build();
        $this->getStockFacade()->createStockType($stockTransfer);

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

        (new SpyStockStore())
            ->setFkStore($storeTransfer->getIdStore())
            ->setFkStock($stockTransfer->getIdStock())
            ->save();

        return (new StoreRelationTransfer())
            ->setIdEntity($stockTransfer->getIdStock())
            ->setIdStores([$storeTransfer->getIdStore()])
            ->setStores(new ArrayObject([$storeTransfer]));
    }

    /**
     * @return \Spryker\Zed\Stock\Business\StockFacadeInterface
     */
    private function getStockFacade(): StockFacadeInterface
    {
        return $this->getLocator()->stock()->facade();
    }
}
