/**
 * DocumentManager Application TypesDataStore
 * 
 * @category    Kebab (kebab-reloaded)
 * @package     Applications
 * @namespace   KebabOS.applications.documentManager.application.models.TypesDataStore
 * @author      Tayfun Öziş ERİKAN <tayfun.ozis.erikan@lab2023.com>
 * @copyright   Copyright (c) 2010-2011 lab2023 - internet technologies TURKEY Inc. (http://www.lab2023.com)
 * @license     http://www.kebab-project.com/licensing
 */
KebabOS.applications.documentManager.application.models.TypesDataStore = Ext.extend(Kebab.library.ext.RESTfulBasicDataStore, {
    
    // Application Bootstrap
    bootstrap: null,
    
    // System REST API
    restAPI: 'jasius/type',
    
    readerFields: [
        {name: 'id', type: 'integer'},
        {name: 'title', type: 'string'}
    ]
});