<?php

/**
 * Model_Entity_Content
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $type_id
 * @property string $title
 * @property Model_Entity_Type $Type
 * @property Doctrine_Collection $Data
 * @property Doctrine_Collection $File
 * @property Doctrine_Collection $Access
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     lab2023 - Dev. Team <info@lab2023.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Model_Entity_Content extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('docloud_content');
        $this->hasColumn('type_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '255',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_bin');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Model_Entity_Type as Type', array(
             'local' => 'type_id',
             'foreign' => 'id'));

        $this->hasMany('Model_Entity_Data as Data', array(
             'local' => 'data_id',
             'foreign' => 'id'));

        $this->hasMany('Model_Entity_File as File', array(
             'local' => 'file_id',
             'foreign' => 'id'));

        $this->hasMany('Model_Entity_Access as Access', array(
             'local' => 'access_id',
             'foreign' => 'id'));

        $softdelete0 = new Doctrine_Template_SoftDelete();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $blameable0 = new Doctrine_Template_Blameable();
        $this->actAs($softdelete0);
        $this->actAs($timestampable0);
        $this->actAs($blameable0);
    }
}