/**
 * Created by bcu on 05/05/2016.
 */
CKEDITOR.plugins.add( 'widgetform', {
  requires: 'widget',
  init: function( editor ) {
    editor.widgets.add( 'widgetform', {
      draggable: false,
      editables: {
        campo1: { selector: '.area-editavel1', allowedContent: 'br'},
        campo2: { selector: '.area-editavel2', allowedContent: 'br'},
        campo3: { selector: '.area-editavel3', allowedContent: 'br'},
        campo4: { selector: '.area-editavel4', allowedContent: 'br'},
        campo5: { selector: '.area-editavel5', allowedContent: 'br'},
        campo6: { selector: '.area-editavel6', allowedContent: 'br'},
        campo7: { selector: '.area-editavel7', allowedContent: 'br'},
        campo8: { selector: '.area-editavel8', allowedContent: 'br'},
        campo9: { selector: '.area-editavel9', allowedContent: 'br'},
        campo10: { selector: '.area-editavel10', allowedContent: 'br'},
        campo11: { selector: '.area-editavel11', allowedContent: 'br'},
        campo12: { selector: '.area-editavel12', allowedContent: 'br'},
        campo13: { selector: '.area-editavel13', allowedContent: 'br'},
        campo14: { selector: '.area-editavel14', allowedContent: 'br'},
        campo15: { selector: '.area-editavel15', allowedContent: 'br'},
        campo16: { selector: '.area-editavel16', allowedContent: 'br'},
        campo17: { selector: '.area-editavel17', allowedContent: 'br'},
        campo18: { selector: '.area-editavel18', allowedContent: 'br'},
        campo19: { selector: '.area-editavel19', allowedContent: 'br'},
        campo20: { selector: '.area-editavel20', allowedContent: 'br'}

},
upcast: function( element ) {
  return element.name == 'table' && element.hasClass( 'tabela-somente-leitura' );
}
} );
}

} );