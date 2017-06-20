/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.language = 'ru';
	config.uiColor = '#EEEEEE';
	config.toolbar = 'CMS';

	// CMS SIMPLE EDITOR
	config.toolbar_CMS =
	[
		{ name: 'document', items: [ 'Maximize', '-', 'Source' ] },
		{ name: 'clipboard', items: [ 'Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord' ] },
		{ name: 'search', items: [ 'Find' ] },
		{ name: 'insert', items: [ 'Image', 'Youtube', 'Flash', 'Table', 'CreateDiv', 'HorizontalRule' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'jusify', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor', 'Iframe' ] },
		{ name: 'paragraph', items : [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ] },
		'/',
		{ name: 'styles', items: [ 'Format', 'Font', 'FontSize', ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'BidiLtr', 'BidiRtl', 'ShowBlocks', 'SpecialChar' ] },
		{ name: 'view', items: [ 'Zoom' ] }
	];

	// ON SITE INLINE EDITOR
	config.toolbar_FRONTEND =
	[
		{ name: 'clipboard', items: [ 'SintezSave', 'Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord' ] },
		{ name: 'search', items: [ 'Find' ] },
		{ name: 'insert', items: [ 'Image', 'Youtube', 'Flash', 'Table', 'CreateDiv', 'HorizontalRule' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ] },{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor', 'Iframe' ] },
		'/',
		{ name: 'styles', items: [ 'Format', 'Font', 'FontSize', ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'jusify', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'tools', items: [ 'BidiLtr', 'BidiRtl', 'SpecialChar' ] }
	];

	// ON SITE INLINE FIELD EDITOR
	config.toolbar_SAVEONLY =
	[
		{ name: 'clipboard', items: [ 'SintezSave'] }
	];
};

CKEDITOR.disableAutoInline = true;
CKFinder.setupCKEditor( null, '/cms/public/templates/js/ckfinder/' );