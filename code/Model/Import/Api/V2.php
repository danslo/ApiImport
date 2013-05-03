<?php
/*
 * Copyright 2013 Alexander Buch
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

class Danslo_ApiImport_Model_Import_Api_V2 extends Danslo_ApiImport_Model_Import_Api
{
	
	/**
	 * (non-PHPdoc)
	 * @see Danslo_ApiImport_Model_Import_Api::importEntities()
	 */
	public function importEntities($entities, $entityType = null, $behavior = null)
	{
		$this->_prepareEntites($entities);
		return parent::importEntities($entities, $entityType, $behavior);
	}
	
	/**
	 * Prepare incoming entities encoded as complexType apiImportImportEntitiesArray
	 * for passthru to API V1 as associative array
	 * 
	 * @param array $entities
	 * @return void
	 */
	protected function _prepareEntites(Array &$entities)
    {
    	foreach ($entities as &$entity) {
   			foreach ($entity as $j => &$object) {
   				if (!$object->key) continue;
   				$entity[$object->key] = $object->value;
   				unset($entity[$j]);
   				unset($object);
   			}
   			unset($entity);
    	}
    }
	
}