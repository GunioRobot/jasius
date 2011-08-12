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
 * @category   Kebab (kebab-reloaded)
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
 * @category   Kebab (kebab-reloaded)
 * @package    
 * @subpackage 
 * @author     Onur Özgür ÖZKAN <onur.ozgur.ozkan@lab2023.com>
 * @copyright  Copyright (c) 2010-2011 lab2023 - internet technologies TURKEY Inc. (http://www.lab2023.com)
 * @license    http://www.kebab-project.com/cms/licensing
 * @version    1.5.0
 */
class Jasius_ContentController extends Kebab_Rest_Controller
{
    public function deleteAction()
    {
        $param = $this->_helper->param();
        $response = $this->_helper->response();
        $retVal = Jasius_Model_Content::del($param['contentId']);
        $response->setSuccess(true)->getResponse();
    }
    
    public function indexAction()
    {
        $param = $this->_helper->param();
        $response = $this->_helper->response();
        $content    = Jasius_Model_Content::getAllContentByTypeId($param['typeId']);
        if (is_bool($content)) {
            $response->setSuccess(false);
        } else {
            $response->setSuccess(true)->addData($content);
        }
        $response->getResponse();
    }
    public function postAction()
    {
        $param = $this->_helper->param();

        $response = $this->_helper->response();

        Doctrine_Manager::connection()->beginTransaction();
        try {
            // Check type_id is in $propertyFormData
            if (array_key_exists('controller', $param)) {
                unset($param['controller']);
            }
             if (array_key_exists('action', $param)) {
                unset($param['action']);
            }
             if (array_key_exists('module', $param)) {
                unset($param['module']);
            }

            $keys = array_keys($param);
            $expProperty = explode('_',$keys[0]);
            $propertyId =$expProperty[count($expProperty)-1];

            $type = Doctrine_Core::getTable('Model_Entity_Property')->find($propertyId);
            if (is_bool($type)){
                $response->getResponse();
            }
            $content = Jasius_Model_Content::add($type->type_id);
            $retData = Jasius_Model_Data::add($type->type_id, $content->id, $param);

            $success = is_bool($retData) && $retData === true
                     ? Doctrine_Manager::connection()->commit()
                     : false;

            if ($success) {
                $response->setSuccess($success)->add('contentId',$content->id)->addNotification('INFO', 'Document is saved');
            } else {
                $response->setErrors($retData);
            }

        } catch (Zend_Exception $e) {
            Doctrine_Manager::connection()->rollback();
            throw $e;
        } catch (Doctrine_Exception $e) {
            Doctrine_Manager::connection()->rollback();
            throw $e;
        }

        $response->getResponse();

    }

    public function getAction()
    {
        $param = $this->_helper->param();
        $response = $this->_helper->response();
        $content = Doctrine_Core::getTable('Model_Entity_Content')->find($param['contentId']);
        $rawData = is_object($content)
                ? Jasius_Model_Data::getDataForLoadDocumentForm($content->id)
                : array();

        $retData = array();
        foreach($rawData as $item) {
            $retData['property_item_'. $item['property_id']] = $item[Jasius_Model_Data::mapping($item['dataType'])];
        }

        if(count($retData)>0){
            $response->setSuccess(true)->addData($retData);
        } else {
            $response->setSuccess(false)->add('msg','Property bulunamadı.');
        }
        $response->getResponse();

    }

}
