<?php

/**
 * Model_Entity_Application
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $identity
 * @property string $className
 * @property string $name
 * @property string $department
 * @property string $version
 * @property enum $type
 * @property string $title
 * @property clob $description
 * @property boolean $active
 * @property Doctrine_Collection $StoryApplication
 * @property Doctrine_Collection $Application
 * @property Doctrine_Collection $Feedback
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     lab2023 - Dev. Team <info@lab2023.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Model_Entity_Application extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('system_application');
        $this->hasColumn('identity', 'string', 255, array(
             'type' => 'string',
             'unique' => true,
             'length' => '255',
             ));
        $this->hasColumn('className', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('department', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('version', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('type', 'enum', 11, array(
             'type' => 'enum',
             'length' => 11,
             'values' => 
             array(
              0 => 'application',
              1 => 'system',
             ),
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('description', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('active', 'boolean', null, array(
             'type' => 'boolean',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_bin');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Model_Entity_StoryApplication as StoryApplication', array(
             'local' => 'id',
             'foreign' => 'application_id'));

        $this->hasMany('Model_Entity_Story as Application', array(
             'refClass' => 'Model_Entity_StoryApplication',
             'local' => 'application_id',
             'foreign' => 'story_id'));

        $this->hasMany('Model_Entity_Feedback as Feedback', array(
             'local' => 'id',
             'foreign' => 'application_id'));

        $i18n0 = new Doctrine_Template_I18n(array(
             'fields' => 
             array(
              0 => 'title',
              1 => 'description',
             ),
             'className' => 'ApplicationTranslation',
             'length' => 2,
             ));
        $searchable1 = new Doctrine_Template_Searchable(array(
             'fields' => 
             array(
              0 => 'title',
              1 => 'description',
             ),
             'className' => 'SystemApplicationSearch',
             ));
        $i18n0->addChild($searchable1);
        $this->actAs($i18n0);
    }
}