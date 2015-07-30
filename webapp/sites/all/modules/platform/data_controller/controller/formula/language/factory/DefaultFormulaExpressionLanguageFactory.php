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

class DefaultFormulaExpressionLanguageFactory extends FormulaExpressionLanguageFactory {

    private $handlerConfigurations = NULL;
    private $handlers = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_formula_expression_language');
    }

    protected function getDefaultLanguage() {
        $defaultLanguage = NULL;

        if (isset($this->handlerConfigurations)) {
            foreach ($this->handlerConfigurations as $language => $handlerConfiguration) {
                if (isset($handlerConfiguration['default']) && $handlerConfiguration['default']) {
                    if (isset($defaultLanguage)) {
                        throw new IllegalArgumentException(t(
                            'Found several default formula languages: [%lanuage1, %language2]',
                            array('%lanuage1' => $defaultLanguage, '%language2' => $language)));
                    }

                    $defaultLanguage = $language;
                }
            }
        }
        if (!isset($defaultLanguage)) {
            throw new IllegalArgumentException(t('Default formula language is not set'));
        }

        return $defaultLanguage;
    }

    protected function getHandlerClassName($language) {
        $classname = isset($this->handlerConfigurations[$language]['classname']) ? $this->handlerConfigurations[$language]['classname'] : NULL;
        if (!isset($classname)) {
            throw new IllegalArgumentException(t('Unsupported formula language: %language', array('%language' => $language)));
        }

        return $classname;
    }

    public function getHandler($language) {
        $selectedLanguage = isset($language) ? $language : $this->getDefaultLanguage();

        if (isset($this->handlers[$selectedLanguage])) {
            return $this->handlers[$selectedLanguage];
        }

        $classname = $this->getHandlerClassName($selectedLanguage);

        $handler = new $classname();

        $this->handlers[$selectedLanguage] = $handler;

        return $handler;
    }
}
