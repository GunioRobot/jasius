<?php

/**
 * Kebab Framework
 *
 * LICENSE
 *
 * This source file is subject to the  Dual Licensing Model that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kebab-project.com/cms/licensing
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@lab2023.com so we can send you a copy immediately.
 *
 * @category   Kebab
 * @package    Kebab
 * @subpackage Library
 * @author     Onur Özgür ÖZKAN <onur.ozgur.ozkan@lab2023.com>
 * @copyright  Copyright (c) 2010-2011 lab2023 - internet technologies TURKEY Inc. (http://www.lab2023.com)
 * @license    http://www.kebab-project.com/cms/licensing
 * @version    1.5.0
 */
 
/**
 * 
 *
 * @category   Kebab
 * @package    
 * @subpackage 
 * @author     Onur Özgür ÖZKAN <onur.ozgur.ozkan@lab2023.com>
 * @copyright  Copyright (c) 2010-2011 lab2023 - internet technologies TURKEY Inc. (http://www.lab2023.com)
 * @license    http://www.kebab-project.com/cms/licensing
 * @version    1.5.0
 */
class Jasius_Model_Data
{
    /**
     * @static
     * @param $typeId
     * @param $contentId
     * @param array $propertyFormData
     * @return array|bool
     */
    public static $validate = false;
    
    public static function add($contentId, array $propertyFormData)
    {
        // Sort $propertyFormData with $item_id
        ksort($propertyFormData);
        // Validation
        $retData = self::validation($contentId, $propertyFormData);

        if (!self::$validate) {
            $retVal = $retData;
        } else {
            // Save collection
            $dataCollection = new Doctrine_Collection('Model_Entity_Data');
            $dataCollection->fromArray($retData);
            $dataCollection->save();
            unset($dataCollection);
            
            $retVal = true;
        }

        return $retVal;
    }

    public static function update($contentId, $propertyFormData)
    {
        // Sort $propertyFormData with $item_id
        ksort($propertyFormData);

        $retData = self::validation($contentId, $propertyFormData);

        if (!self::$validate)
        {
            $retVal = $retData;
        } else {
            Doctrine_Manager::connection()->beginTransaction();
            try {
                foreach ($retData as $data) {
                    $col = self::getDataColumn($data);
                    $query = Doctrine_Query::create()
                        ->update('Model_Entity_Data')
                        ->set($col, $data[$col])
                        ->where('property_id = ?', $data['property_id'])
                        ->andWhere('content_id = ?', $data['content_id'])
                        ->execute();
                }
                Doctrine_Manager::connection()->commit();
                unset($retData);
                $retVal = true;
            } catch (Doctrine_Exception $e) {
                Doctrine_Manager::connection()->rollback();
                print_r($e);
            } catch (Zend_Exception $e) {
                Doctrine_Manager::connection()->rollback();
                print_r($e);
            }
        }
        return $retVal;
    }

    public static function getDataColumn($data) {
        if (array_key_exists('timeValue', $data)) {
            return 'timeValue';
        }
        if (array_key_exists('textValue', $data)) {
            return 'textValue';
        }
        if (array_key_exists('numberValue', $data)) {
            return 'numberValue';
        }
    }

    public static function validation ($contentId, $propertyFormData)
    {
        self::$validate = false;
        $content = Doctrine_Core::getTable('Model_Entity_Content')->find($contentId);
        $propertyDataStructure = Jasius_Model_Property::getAllPropertyByTypeId($content->type_id)->execute();

        $i = 0;
        $errorMessage = array();
        $dataCollectionArray = array();
        foreach ($propertyFormData as $propertyKey => $propertyValue) {

            //KBBTODO use listlist($id, $dataType, $isUni) = $propertyDataStructure[$i];

            // Check dataType
            $dataTypeCheck = Doctrine_Validator::isValidType($propertyValue, $propertyDataStructure[$i]['dataType']);
            if (!$dataTypeCheck) {
                $errorMessage[$propertyKey] = 'Data type is not appropriate in this area';
            }

            // Check Require
            if ($propertyDataStructure[$i]['isRequire'] && is_null($propertyValue)) {
                $errorMessage[$propertyKey] = 'Field cannot be left blank';
            }

            // Check enum
            if ($propertyDataStructure[$i]['dataType'] === 'enum') {
                $enum = unserialize($propertyDataStructure[$i]['enum']);
                if (!in_array($propertyValue, $enum)) {
                    $errorMessage[$propertyKey] = 'Please select from the list';
                }
            }

            // Add default value if value is null and defaultValue is set
            if (is_null($propertyValue) && !is_null($propertyDataStructure[$i]['defaultValue'])) {
                $propertyFormData[$propertyKey] = $propertyDataStructure[$i]['defaultValue'];
            }

            // Check isUnique
            if ($propertyDataStructure[$i]['isUnique']) {
                $field = self::mapping($propertyDataStructure[$i]['dataType']);
                $isUniqueCheck = Doctrine_Query::create()->from('Model_Entity_Data data')
                                         ->where($field . '= ?', $propertyValue)
                                         ->andWhere('data.content_id != ?', $contentId)->count() > 0
                                ? true
                                : false;

                if ($isUniqueCheck) {
                    $errorMessage[$propertyKey] = 'Same record entered before';
                }
            }

            $dataCollectionArray[$i]['property_id'] = $propertyDataStructure[$i]['id'];
            $dataCollectionArray[$i]['content_id'] = $contentId;
            $dataCollectionArray[$i][self::mapping($propertyDataStructure[$i]['dataType'])] = $propertyValue;

            $i++;
        } // eof foreach

        if (count($errorMessage) > 0) {
            self::$validate = false;
            return $errorMessage;
        } else {
            self::$validate = true;
            return $dataCollectionArray;
        }
    }

    public static function getDataForLoadDocumentForm($contentId)
    {
        $query = Doctrine_Query::create()
                    ->select('
                        data.property_id,
                        data.numberValue,
                        data.textValue,
                        data.timeValue,
                        property.dataType as dataType
                    ')
                    ->from('Model_Entity_Data data')
                    ->leftJoin('data.Property property')
                    ->where('data.content_id = ?', $contentId)
                    ->orderBy('property.weight ASC')
                    ->setHydrationMode(Doctrine::HYDRATE_ARRAY);

        $rawData = $query->execute();

        return $rawData;
    }

    public static function del($contentId)
    {
        Doctrine_Manager::connection()->beginTransaction();
        try {
                Doctrine_Query::create()
                    ->delete('Model_Entity_Data data')
                    ->where('data.content_id = ?', $contentId)
                    ->execute();
            
            $retVal = Doctrine_Manager::connection()->commit();
        } catch (Doctrine_Exception $e) {
            Doctrine_Manager::connection()->rollback();
            throw $e;
        } catch (Zend_Exception $e) {
            Doctrine_Manager::connection()->rollback();
            throw $e;
        }

        return $retVal;
    }
    public static function mapping($dataType)
    {
        switch ($dataType) {
            case 'integer':
                $retDataType = 'numberValue';
                break;
            case 'string':
                $retDataType = 'textValue';
                break;
            case 'boolean':
                $retDataType = 'numberValue';
                break;
            case 'enum':
                $retDataType = 'textValue';
                break;
            case 'float':
                $retDataType = 'numberValue';
                break;
            case 'decimal':
                $retDataType = 'numberValue';
                break;
            case 'array':
                $retDataType = 'textValue';
                break;
            case 'object':
                $retDataType = 'textValue';
                break;
            case 'blob':
                $retDataType = 'textValue';
                break;
            case 'clob':
                $retDataType = 'textValue';
                break;
            case 'timestamp':
                $retDataType = 'timeValue';
                break;
            case 'time':
                $retDataType = 'timeValue';
                break;
            case 'date':
                $retDataType = 'timeValue';
                break;
            case 'gzip':
                $retDataType = 'textValue';
                break;
            default:
                throw new Kebab_Exception('Unvalid data type');
                break;
        }

        return $retDataType;
    }
}
