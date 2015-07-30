<?php
/*
 * Copyright 2014 REI Systems, Inc.
 * 
 * This file is part of GovDashboard.
 * 
 * GovDashboard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GovDashboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GovDashboard.  If not, see <http://www.gnu.org/licenses/>.
 */


class TransactionSupporter extends AbstractDataSubmitter {

    private $datasourceName = NULL;

    public function __construct($datasourceName) {
        parent::__construct();
        $this->datasourceName = $datasourceName;
    }

    public function init() {
        $result = parent::init();

        if ($result) {
            TransactionManager::getInstance()->startTransaction($this->datasourceName);
        }

        return $result;
    }

    public function finish() {
        $transaction = TransactionManager::getInstance()->getTransaction($this->datasourceName);
        $transaction->commit();

        parent::finish();
    }

    public function abort() {
        $transaction = TransactionManager::getInstance()->getTransaction($this->datasourceName);
        $transaction->rollback();

        parent::abort();
    }
}
