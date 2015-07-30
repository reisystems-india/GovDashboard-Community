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


abstract class AbstractFileMetaModelLoader extends AbstractMetaModelLoader {

    abstract protected function getMetaModelFolderName();

    protected function simplifyPath($path) {
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? StringHelper::trim($_SERVER['DOCUMENT_ROOT']) : NULL;
        if (isset($documentRoot)) {
            // 'fixing' slash
            $documentRoot = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $documentRoot);
        }

        return (isset($documentRoot) && (strpos($path, $documentRoot) === 0)) ? substr($path, strlen($documentRoot)) : $path;
    }

    public function load(AbstractMetaModel $metamodel, array $filters = NULL) {
        $metamodelTypeFolder = $this->getMetaModelFolderName();

        $filecount = 0;
        $metamodelConfigurations = module_invoke_all('dp_metamodel');
        foreach ($metamodelConfigurations as $metamodelConfiguration) {
            $path = $metamodelConfiguration['path'] . DIRECTORY_SEPARATOR . $metamodelTypeFolder . DIRECTORY_SEPARATOR . 'metadata';

            // initial name space is not defined. It will be based on subfolder name, if any
            $namespace = NULL;

            $simplifiedPath = $this->simplifyPath($path);
            LogHelper::log_info(t("Loading configuration from '@path' ...", array('@path' => $simplifiedPath)));
            if (!file_exists($path)) {
                throw new IllegalStateException(t('Folder could not be found: %path', array('%path' => $simplifiedPath)));
            }

            $filecount += $this->loadFromDirectory($metamodel, $filters, $path, $namespace);
        }
        LogHelper::log_info(t('Processed @filecount files', array('@filecount' => $filecount)));
    }

    protected function loadFromDirectory(AbstractMetaModel $metamodel, array $filters = NULL, $path, $namespace, $level = 0) {
        $filecount = 0;

        $handle = opendir($path);
        if ($handle !== FALSE) {
            $indent = str_pad('', $level * 4);
            while (($filename = readdir($handle)) !== FALSE) {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename)) {
                    if ($filename[0] != '.') {
                        $folder = DIRECTORY_SEPARATOR . $filename;

                        $nestedNameSpace = isset($namespace) ? NameSpaceHelper::addNameSpace($namespace, $filename) : $filename;

                        LogHelper::log_debug(t("{$indent}Scanning '@folderName' ...", array('@folderName' => $folder)));
                        $filecount += $this->loadFromDirectory($metamodel, $filters, $path . $folder, $nestedNameSpace, $level + 1);
                    }
                }
                elseif ($this->fileNameEndsWithJson($filename)) {
                    LogHelper::log_debug(t("{$indent}Processing '@filename' ...", array('@filename' => $filename)));

                    $this->loadFromFile($metamodel, $filters, $namespace, $path . DIRECTORY_SEPARATOR, $filename);
                    $filecount++;
                }
            }

            closedir($handle);
        }

        return $filecount;
    }

    protected function fileNameEndsWithJson($filename) {
        return strrpos($filename, '.json') === strlen($filename) - strlen('.json');
    }

    protected function loadFromFile(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, $path, $filename) {
        $source = new __AbstractFileMetaModelLoader_Source();

        $source->filename = $path . $filename;

        $modifiedDateTime = filemtime($source->filename);
        if ($modifiedDateTime === FALSE) {
            $modifiedDateTime = NULL;
        }
        $source->datetime = $modifiedDateTime;

        $fileContent = file_get_contents($source->filename);
        if ($fileContent === FALSE) {
            throw new IllegalStateException(t('Could not read content of %filename file', array('%filename' => $filename)));
        }
        $source->content = $this->processFileContent($source->filename, $fileContent);

        $this->merge($metamodel, $filters, $namespace, $source);
    }

    abstract protected function processFileContent($filename, $fileContent);

    abstract protected function merge(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, __AbstractFileMetaModelLoader_Source $source);
}

class __AbstractFileMetaModelLoader_Source extends AbstractObject {

    public $filename = NULL;
    public $datetime = NULL;
    public $content = NULL;
}