<?php
echo $this->Tinymce->input($field, 
	array('label' => false, 'div' => false),
	array(
		'language' => 'en',
		'theme_advanced_buttons1' => 'bold,italic,underline,separator,link,unlink,separator,undo,redo,cleanup,code',
		'theme_advanced_statusbar_location' => 'none',
		// If this is changed, also change Event::allowed_tags
		'valid_elements' => 'p,br,a[href|target=_blank],strong/b,i/em,u',
	
		/* These three prevent links to other pages on this same domain
		 * from being converted to relative URLs. */
		'relative_urls' => false,
		'remove_script_host' => false,
		'convert_urls' => false
	)
);