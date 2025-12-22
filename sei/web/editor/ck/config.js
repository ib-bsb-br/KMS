/**
 * Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.language = 'pt-br';
	config.skin='moonocolor';
	config.autoGrow_minHeight= 10;
	config.autoGrow_onStartup= true;
	config.dialog_noConfirmCancel = true;
	//config.height=100;
	config.scayt_sLang='pt_BR';
	config.defaultLanguage='pt-br';
	config.sharedSpaces= {'top':'divComandos'};
	config.scayt_autoStartup=true;
	config.scayt_uiTabs='0,0,0';
	config.linkShowAdvancedTab=false;
	config.linkShowTargetTab=false;
};


CKEDITOR.on('dialogDefinition',function(ev)
{if(ev.data.name=='image'){
	var dd=ev.data.definition;dd.removeContents('Link');dd.removeContents('advanced');dd.minHeight=200;dd.minWidth=250;
	var tab=dd.getContents('info');tab.get('ratioLock').style='margin-top:20px;width:40px;height:40px;';tab.get('txtUrl').hidden=true;
	tab.get('txtAlt').hidden=true;tab.get('htmlPreview').hidden=true;tab.remove('txtHSpace');tab.remove('txtVSpace');tab.remove('cmbAlign');}});

